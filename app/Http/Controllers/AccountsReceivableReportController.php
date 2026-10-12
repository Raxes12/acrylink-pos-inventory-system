<?php

namespace App\Http\Controllers;

use App\Models\TransactionPayment;
use App\Services\AccountsReceivableReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AccountsReceivableReportController extends Controller
{
    public function index(Request $request, AccountsReceivableReportService $report, ReportsController $security)
    {
        $filters = $this->filters($request);
        $aging = $report->agingSummary($filters);
        $transactions = $report->query($filters)
            ->orderBy('credit_sales.due_date')
            ->orderBy('transactions.date')
            ->paginate(20)
            ->withQueryString();
        $collectionPeriod = $filters['collection_period'];
        $collectionQuery = TransactionPayment::query()
            ->whereHas('transaction.creditSale');
        $today = today()->toDateString();
        $collectionQuery = match ($collectionPeriod) {
            'today' => $collectionQuery->whereDate('paid_at', $today),
            'week' => $collectionQuery->whereDate('paid_at', '>=', today()->startOfWeek()->toDateString())
                ->whereDate('paid_at', '<=', $today),
            'month' => $collectionQuery->whereDate('paid_at', '>=', today()->startOfMonth()->toDateString())
                ->whereDate('paid_at', '<=', $today),
            default => $collectionQuery,
        };
        $totalCollections = round((float) $collectionQuery->sum('amount'), 2);
        $collectionPeriodLabel = match ($collectionPeriod) {
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            default => 'All Time',
        };

        $security->authorizeReceivableReportView($request, $filters, $transactions->total());

        return view('reports.accounts-receivable', compact(
            'transactions', 'filters', 'aging', 'report',
            'collectionPeriod', 'collectionPeriodLabel', 'totalCollections'
        ));
    }

    public function export(Request $request, AccountsReceivableReportService $report, ReportsController $security)
    {
        $filters = $this->filters($request);
        $transactions = $report->query($filters)->orderBy('credit_sales.due_date')->get();
        if ($response = $security->authorizeReceivableReportExport($request, 'csv', $filters, $transactions)) {
            return $response;
        }

        return response()->streamDownload(function () use ($transactions, $report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [app(\App\Services\SystemSettingsService::class)->get('business.name', 'Acry-Link')]);
            fputcsv($handle, ['ACCOUNTS RECEIVABLE REPORT']);
            fputcsv($handle, ['GENERATED', now()->format('F j, Y g:i A')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Customer', 'Customer Code', 'Receipt', 'Transaction Date', 'Due Date', 'Net Sale', 'Paid', 'Outstanding', 'Status']);
            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $this->csvCell($transaction->customer?->customer_name),
                    $this->csvCell($transaction->customer?->customer_code),
                    $this->csvCell($transaction->receipt_no),
                    $transaction->date->format('Y-m-d'),
                    $transaction->creditSale->due_date->format('Y-m-d'),
                    number_format((float) $transaction->ar_net_amount, 2, '.', ''),
                    number_format((float) $transaction->ar_paid_amount, 2, '.', ''),
                    number_format((float) $transaction->ar_balance_amount, 2, '.', ''),
                    $report->status($transaction),
                ]);
            }
            fclose($handle);
        }, 'accounts-receivable-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request, AccountsReceivableReportService $report, ReportsController $security)
    {
        $filters = $this->filters($request);
        $transactions = $report->query($filters)->orderBy('credit_sales.due_date')->get();
        $aging = $report->agingSummary($filters);
        if ($response = $security->authorizeReceivableReportExport($request, 'pdf', $filters, $transactions)) {
            return $response;
        }

        return Pdf::loadView('reports.exports.accounts-receivable-pdf', [
            'companyName' => app(\App\Services\SystemSettingsService::class)->get('business.name', 'Acry-Link'),
            'transactions' => $transactions,
            'aging' => $aging,
            'report' => $report,
        ])->setPaper('a4', 'landscape')->download('accounts-receivable-'.now()->format('Ymd-His').'.pdf');
    }

    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'customer' => 'nullable|string|max:150',
            'receipt' => 'nullable|string|max:50',
            'payment_status' => 'nullable|in:unpaid,partially_paid,overdue',
            'aging' => 'nullable|in:not_due,1_30,31_60,61_90,90_plus',
            'due_from' => 'nullable|date',
            'due_to' => 'nullable|date|after_or_equal:due_from',
            'transaction_from' => 'nullable|date',
            'transaction_to' => 'nullable|date|after_or_equal:transaction_from',
            'collection_period' => 'nullable|in:today,week,month,all',
        ]);

        $filters['collection_period'] ??= 'all';

        return $filters;
    }

    private function csvCell(?string $value): string
    {
        $value ??= '';
        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
