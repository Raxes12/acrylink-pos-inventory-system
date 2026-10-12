<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AccountsReceivableService
{
    public const DUE_SOON_DAYS = 7;

    public function openReceivablesQuery(array $filters = []): Builder
    {
        return $this->buildReceivablesQuery($filters, true);
    }

    public function receivablesQuery(array $filters = []): Builder
    {
        return $this->buildReceivablesQuery($filters, false);
    }

    private function buildReceivablesQuery(array $filters, bool $openOnly): Builder
    {
        $payments = DB::table('transaction_payments')
            ->selectRaw('transaction_id, SUM(amount) as paid_amount')
            ->groupBy('transaction_id');
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');

        $query = Transaction::query()
            ->join('credit_sales', 'credit_sales.transaction_id', '=', 'transactions.id')
            ->join('customers', 'customers.id', '=', 'credit_sales.customer_id')
            ->leftJoinSub($payments, 'ar_payments', 'ar_payments.transaction_id', '=', 'transactions.id')
            ->leftJoinSub($refunds, 'ar_refunds', 'ar_refunds.transaction_id', '=', 'transactions.id')
            ->select('transactions.*')
            ->addSelect('credit_sales.due_date as ar_due_date')
            ->selectRaw($this->netExpression().' as ar_net_amount')
            ->selectRaw('COALESCE(ar_payments.paid_amount, 0) as ar_paid_amount')
            ->selectRaw($this->balanceExpression().' as ar_balance_amount')
            ->where('transactions.status', '!=', 'voided')
            ->with([
                'customer:id,customer_code,customer_name',
                'cashier:id,name',
                'creditSale:id,transaction_id,customer_id,credit_term_days,due_date,original_credit_amount',
            ]);

        if ($openOnly) {
            $query->whereRaw($this->balanceExpression().' > 0');
        }

        $query->when($filters['search'] ?? null, function (Builder $query, string $value) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value).'%';
            $query->where(fn (Builder $nested) => $nested
                ->where('customers.customer_name', 'like', $like)
                ->orWhere('customers.customer_code', 'like', $like)
                ->orWhere('transactions.receipt_no', 'like', $like));
        });

        $query->when($filters['customer'] ?? null, function (Builder $query, string $value) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value).'%';
            $query->where(fn (Builder $nested) => $nested
                ->where('customers.customer_name', 'like', $like)
                ->orWhere('customers.customer_code', 'like', $like));
        });
        $query->when($filters['receipt'] ?? null, fn (Builder $query, string $value) => $query->where('transactions.receipt_no', 'like', '%'.$value.'%'));
        $query->when($filters['due_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('credit_sales.due_date', '>=', $value));
        $query->when($filters['due_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('credit_sales.due_date', '<=', $value));
        $query->when($filters['transaction_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('transactions.date', '>=', $value));
        $query->when($filters['transaction_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('transactions.date', '<=', $value));
        $query->when($filters['cashier_id'] ?? null, fn (Builder $query, int|string $cashierId) => $query->where('transactions.cashier_id', (int) $cashierId));

        $today = today()->toDateString();
        $dueSoon = today()->addDays(self::DUE_SOON_DAYS)->toDateString();
        match ($filters['payment_status'] ?? null) {
            'open' => $query->whereRaw($this->balanceExpression().' > 0'),
            'due_soon' => $query->whereRaw($this->balanceExpression().' > 0')
                ->whereDate('credit_sales.due_date', '>=', $today)
                ->whereDate('credit_sales.due_date', '<=', $dueSoon),
            'overdue' => $query->whereRaw($this->balanceExpression().' > 0')
                ->whereDate('credit_sales.due_date', '<', $today),
            'partially_paid' => $query->whereRaw($this->balanceExpression().' > 0')
                ->whereRaw('COALESCE(ar_payments.paid_amount, 0) > 0'),
            'paid' => $query->whereRaw($this->balanceExpression().' <= 0'),
            'unpaid' => $query->whereDate('credit_sales.due_date', '>=', $today)
                ->whereRaw('COALESCE(ar_payments.paid_amount, 0) <= 0'),
            default => null,
        };

        return $query;
    }

    /** @return array{total_outstanding: float, due_soon: float, overdue: float, customers_with_open_credit: int} */
    public function summary(array $filters = []): array
    {
        $base = $this->receivablesQuery($filters)->toBase();
        $rows = DB::query()->fromSub($base, 'open_receivables')->where('ar_balance_amount', '>', 0);
        $today = today();
        $result = $rows->selectRaw('COALESCE(SUM(ar_balance_amount), 0) as total_outstanding')
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date >= ? AND ar_due_date <= ? THEN ar_balance_amount ELSE 0 END), 0) as due_soon', [
                $today->toDateString(), $today->copy()->addDays(self::DUE_SOON_DAYS)->toDateString(),
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN ar_due_date < ? THEN ar_balance_amount ELSE 0 END), 0) as overdue', [$today->toDateString()])
            ->selectRaw('COUNT(DISTINCT customer_id) as customers_with_open_credit')
            ->first();

        return [
            'total_outstanding' => round((float) $result->total_outstanding, 2),
            'due_soon' => round((float) $result->due_soon, 2),
            'overdue' => round((float) $result->overdue, 2),
            'customers_with_open_credit' => (int) $result->customers_with_open_credit,
        ];
    }

    public function statusForRow(Transaction $transaction): string
    {
        return app(TransactionSettlementService::class)->paymentStatusForValues(
            $transaction->status,
            $transaction->ar_balance_amount,
            $transaction->ar_paid_amount,
            $transaction->creditSale->due_date,
        );
    }

    private function netExpression(): string
    {
        return 'CASE WHEN transactions.total - COALESCE(ar_refunds.refunded_amount, 0) > 0 '
            .'THEN transactions.total - COALESCE(ar_refunds.refunded_amount, 0) ELSE 0 END';
    }

    private function balanceExpression(): string
    {
        return 'CASE WHEN ('.$this->netExpression().') - COALESCE(ar_payments.paid_amount, 0) > 0 '
            .'THEN ('.$this->netExpression().') - COALESCE(ar_payments.paid_amount, 0) ELSE 0 END';
    }
}
