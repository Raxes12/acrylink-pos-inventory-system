<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AccountsReceivableReportService
{
    public function query(array $filters): Builder
    {
        $query = app(AccountsReceivableService::class)->openReceivablesQuery($filters);
        $today = today();

        match ($filters['aging'] ?? null) {
            'not_due' => $query->whereDate('credit_sales.due_date', '>=', $today),
            '1_30' => $query->whereBetween('credit_sales.due_date', [$today->copy()->subDays(30), $today->copy()->subDay()]),
            '31_60' => $query->whereBetween('credit_sales.due_date', [$today->copy()->subDays(60), $today->copy()->subDays(31)]),
            '61_90' => $query->whereBetween('credit_sales.due_date', [$today->copy()->subDays(90), $today->copy()->subDays(61)]),
            '90_plus' => $query->whereDate('credit_sales.due_date', '<=', $today->copy()->subDays(91)),
            default => null,
        };

        return $query;
    }

    /** @return array<string, float> */
    public function agingSummary(array $filters = []): array
    {
        $filtersWithoutAging = array_merge($filters, ['aging' => null]);
        $base = $this->query($filtersWithoutAging)->toBase();
        $today = today();
        $result = DB::query()->fromSub($base, 'aged_receivables')
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date >= ? THEN ar_balance_amount ELSE 0 END), 0) as not_due', [$today->toDateString()])
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date BETWEEN ? AND ? THEN ar_balance_amount ELSE 0 END), 0) as days_1_30', [$today->copy()->subDays(30)->toDateString(), $today->copy()->subDay()->toDateString()])
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date BETWEEN ? AND ? THEN ar_balance_amount ELSE 0 END), 0) as days_31_60', [$today->copy()->subDays(60)->toDateString(), $today->copy()->subDays(31)->toDateString()])
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date BETWEEN ? AND ? THEN ar_balance_amount ELSE 0 END), 0) as days_61_90', [$today->copy()->subDays(90)->toDateString(), $today->copy()->subDays(61)->toDateString()])
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date <= ? THEN ar_balance_amount ELSE 0 END), 0) as days_90_plus', [$today->copy()->subDays(91)->toDateString()])
            ->first();

        return collect((array) $result)->map(fn ($value) => round((float) $value, 2))->all();
    }

    public function status(Transaction $transaction): string
    {
        return app(AccountsReceivableService::class)->statusForRow($transaction);
    }
}
