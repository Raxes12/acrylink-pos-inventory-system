<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SystemSettingsService
{
    public const DEFAULTS = [
        'business.name' => 'VMC Auto Parts & Accessories',
        'business.address' => 'Ilagan City, Isabela',
        'business.contact_number' => '',
        'business.email' => '',
        'business.logo_path' => 'logo/vmc-logo.ico',
        'receipt.footer_message' => 'Please keep this receipt for returns and warranty concerns.',
        'receipt.show_cashier' => true,
        'receipt.show_customer' => true,
        'receipt.show_payment_method' => true,
    ];

    public function all(): array
    {
        if (! Schema::hasTable('system_settings')) {
            return self::DEFAULTS;
        }

        return Cache::remember('system-settings.all', 300, function () {
            $stored = SystemSetting::query()->pluck('value', 'key')->all();
            $settings = self::DEFAULTS;
            foreach ($stored as $key => $value) {
                if (is_bool(self::DEFAULTS[$key] ?? null)) {
                    $settings[$key] = filter_var($value, FILTER_VALIDATE_BOOL);
                } else {
                    $settings[$key] = $value;
                }
            }

            return $settings;
        });
    }

    public function get(string $key, mixed $fallback = null): mixed
    {
        return $this->all()[$key] ?? $fallback;
    }

    public function businessLogoUrl(): string
    {
        $path = (string) $this->get('business.logo_path', self::DEFAULTS['business.logo_path']);

        if (str_starts_with($path, 'business-logos/')) {
            return Storage::disk('public')->url($path);
        }

        return asset(ltrim($path ?: self::DEFAULTS['business.logo_path'], '/'));
    }

    public function hasCustomBusinessLogo(): bool
    {
        return str_starts_with((string) $this->get('business.logo_path'), 'business-logos/');
    }

    public function putMany(array $values, string $group): void
    {
        foreach ($values as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], [
                'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                'type' => is_bool($value) ? 'boolean' : 'string',
                'group' => $group,
            ]);
        }
        Cache::forget('system-settings.all');
    }
}
