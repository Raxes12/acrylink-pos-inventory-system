<?php

namespace App\Http\Middleware;

use App\Models\InputAttackLog;
use App\Models\SecurityRule;
use App\Services\IntrusionDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;

class DetectInputAttack
{
    private const MAX_PAYLOAD_LENGTH = 4000;

    private array $excludedPaths = [
        'storage/*',
        'livewire/upload-file',
    ];

    private array $patterns = [
        'xss' => [
            '/<\s*script\b/i',
            '/javascript\s*:/i',
            '/<[^>]+\bon(?:error|load|click|focus)\s*=/i',
        ],
        'sql_injection' => [
            '/\bunion\s+select\b/i',
            '/\bor\s+(?:1\s*=\s*1|[\'\"][^\'\"]+[\'\"]\s*=\s*[\'\"][^\'\"]+[\'\"])/i',
            '/\bdrop\s+table\b/i',
            '/\binsert\s+into\b/i',
            '/\b(?:select|union|where|or|and)\b.{0,80}--(?:\s|$)/i',
            '/\/\*.*?\*\//s',
        ],
        'command_injection' => [
            '/\b(?:cat|curl|wget|powershell|cmd\.exe)\b\s+/i',
            '/(?:;|\|\||&&)\s*(?:rm|del|cat|curl|wget|powershell|cmd)\b/i',
        ],
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $detections = $this->detect($request->except(['_token', '_method']));

        if ($detections === []) {
            return $next($request);
        }

        $ids = app(IntrusionDetectionService::class);
        $shouldBlock = false;
        foreach ($detections as $detection) {
            $ruleName = match ($detection['attack_type']) {
                'sql_injection' => 'sql_injection_attempt',
                'xss' => 'xss_attempt',
                default => null,
            };
            $rule = $ruleName ? $ids->ruleFor($ruleName) : null;
            if ($ruleName && $rule === false) {
                continue;
            }

            $severity = $ruleName
                ? $ids->ruleSeverity($ruleName, $detection['severity'])
                : $detection['severity'];
            $action = $ruleName ? $ids->ruleAction($ruleName, 'alert') : 'alert';

            InputAttackLog::create([
                'user_id' => $request->user()?->id,
                'attack_type' => $detection['attack_type'],
                'request_url' => $this->limit((string) $request->fullUrl(), 2048),
                'request_method' => $request->method(),
                'input_payload' => $this->limit(json_encode($detection['payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}'),
                'ip_address' => $request->ip(),
                'user_agent' => $this->limit((string) $request->userAgent(), 1000),
                'severity' => $severity,
                'detected_at' => now(),
            ]);

            if ($ruleName) {
                $threshold = max(1, (int) $ids->ruleThreshold($ruleName, 1));
                $window = max(1, $ids->ruleWindow($ruleName, 5));
                $key = 'ids-input|'.$ruleName.'|'.($request->ip() ?: 'unknown');
                RateLimiter::hit($key, $window * 60);
                if (RateLimiter::attempts($key) < $threshold) {
                    continue;
                }
            }

            $ids->createIntrusionEvent(
                $ruleName ?? 'abnormal_activity',
                $severity,
                'Suspicious input pattern detected in '.$request->method().' '.$request->path().'.',
                $request,
                $request->user(),
                $rule instanceof SecurityRule ? $rule : null,
                'input_validation'
            );
            $shouldBlock = $shouldBlock || $action === 'block';
        }

        if ($shouldBlock) {
            abort(400, 'The request was rejected because it contains unsafe input.');
        }

        return $next($request);
    }

    private function detect(array $input): array
    {
        $detections = [];
        foreach ($this->flatten($input) as $key => $value) {
            foreach ($this->patterns as $attackType => $patterns) {
                if (isset($detections[$attackType])) {
                    continue;
                }
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $detections[$attackType] = [
                            'attack_type' => $attackType,
                            'severity' => $attackType === 'sql_injection' ? 'critical' : 'high',
                            'payload' => [
                                'field' => $key,
                                'matched_pattern' => $pattern,
                                'value' => $this->limit($value, 500),
                            ],
                        ];
                        break;
                    }
                }
            }
        }

        return array_values($detections);
    }

    private function flatten(array $input, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($input as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if ($value instanceof UploadedFile) {
                continue;
            }

            if (is_array($value)) {
                $flattened += $this->flatten($value, $path);
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $flattened[$path] = (string) $value;
            }
        }

        return $flattened;
    }

    private function shouldSkip(Request $request): bool
    {
        foreach ($this->excludedPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    private function limit(string $value, int $length = self::MAX_PAYLOAD_LENGTH): string
    {
        return mb_substr($value, 0, $length);
    }
}
