<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\CaseInsensitiveSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $hasContextColumns = Schema::hasColumn('audit_logs', 'affected_table')
            && Schema::hasColumn('audit_logs', 'affected_record_id');
        $logs = $this->filteredLogs($request, $hasContextColumns);

        $actions = AuditLog::distinct()->pluck('action')->sort()->values();
        $modules = $hasContextColumns
            ? AuditLog::query()
                ->whereNotNull('affected_table')
                ->distinct()
                ->pluck('affected_table')
                ->filter()
                ->unique()
                ->sort()
                ->values()
            : $actions;
        $users = User::orderBy('name')->get(['id', 'name', 'username']);
        $auditSummary = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('timestamp', today())->count(),
            'security' => AuditLog::whereIn('action', ['SECURITY', 'IDS', 'LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])->count(),
            'users' => AuditLog::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];

        return view('audit.index', compact('logs', 'actions', 'modules', 'users', 'auditSummary'));
    }

    public function results(Request $request)
    {
        $hasContextColumns = Schema::hasColumn('audit_logs', 'affected_table')
            && Schema::hasColumn('audit_logs', 'affected_record_id');
        $logs = $this->filteredLogs($request, $hasContextColumns);

        return response()->json([
            'html' => view('audit._results', compact('logs'))->render(),
        ]);
    }

    private function filteredLogs(Request $request, bool $hasContextColumns)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:80'],
            'module' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $action = $filters['action'] ?? null;
        $userId = $filters['user_id'] ?? null;
        $module = $filters['module'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        return AuditLog::query()
            ->when($search, fn ($q) => $q->where(function ($query) use ($search, $hasContextColumns) {
                CaseInsensitiveSearch::where($query, 'username', $search);
                CaseInsensitiveSearch::orWhere($query, 'detail', $search);
                CaseInsensitiveSearch::orWhere($query, 'action', $search);
                CaseInsensitiveSearch::orWhere($query, 'ip_address', $search);
                CaseInsensitiveSearch::orWhere($query, 'mac_address', $search);
                CaseInsensitiveSearch::orWhere($query, 'user_agent', $search);

                if ($hasContextColumns) {
                    CaseInsensitiveSearch::orWhere($query, 'affected_record_id', $search);
                    CaseInsensitiveSearch::orWhere($query, 'affected_table', $search);
                }
            }))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($module && $hasContextColumns, fn ($q) => $q->where(function ($query) use ($module) {
                $query->where('affected_table', $module)
                    ->orWhere('action', $module);
            }))
            ->when($module && ! $hasContextColumns, fn ($q) => $q->where('action', $module))
            ->when($dateFrom, fn ($q) => $q->whereDate('timestamp', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('timestamp', '<=', $dateTo))
            ->orderByDesc('timestamp')
            ->paginate(50)
            ->withQueryString();
    }
}
