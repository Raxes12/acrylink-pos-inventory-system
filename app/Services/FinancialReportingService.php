<?php

namespace App\Services;

use App\Models\TransactionPayment;
use Illuminate\Support\Facades\DB;

class FinancialReportingService
{
    /** @return array{today_net_sales: float, today_collections: float, today_credit_sales: float, total_accounts_receivable: float, overdue_receivables: float} */
    public function todayMetrics(): array
    {
        $today = today()->toDateString();
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');
        $netExpression = 'CASE WHEN transactions.total - COALESCE(report_refunds.refunded_amount, 0) > 0 '
            .'THEN transactions.total - COALESCE(report_refunds.refunded_amount, 0) ELSE 0 END';
        $sales = DB::table('transactions')
            ->leftJoinSub($refunds, 'report_refunds', 'report_refunds.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.date', $today)
            ->where('transactions.status', '!=', 'voided');
        $creditSales = clone $sales;
        $receivables = app(AccountsReceivableService::class)->summary();

        return [
            'today_net_sales' => round((float) $sales->sum(DB::raw($netExpression)), 2),
            'today_collections' => round((float) TransactionPayment::query()->whereDate('paid_at', $today)->sum('amount'), 2),
            'today_credit_sales' => round((float) $creditSales
                ->join('credit_sales', 'credit_sales.transaction_id', '=', 'transactions.id')
                ->sum(DB::raw($netExpression)), 2),
            'total_accounts_receivable' => $receivables['total_outstanding'],
            'overdue_receivables' => $receivables['overdue'],
        ];
    }
}
