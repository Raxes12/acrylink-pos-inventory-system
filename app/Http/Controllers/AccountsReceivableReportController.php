<?php

namespace App\Http\Controllers;

use App\Models\TransactionPayment;
use App\Services\AccountsReceivableReportService;
use App\Services\ProfessionalSpreadsheetExportService;
use App\Services\SystemSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            fputcsv($handle, [app(SystemSettingsService::class)->get('business.name', 'Acry-Link')]);
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
            'companyName' => app(SystemSettingsService::class)->get('business.name', 'Acry-Link'),
            'transactions' => $transactions,
            'aging' => $aging,
            'report' => $report,
        ])->setPaper('a4', 'landscape')->download('accounts-receivable-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportExcel(
        Request $request,
        AccountsReceivableReportService $report,
        ReportsController $security,
        ProfessionalSpreadsheetExportService $spreadsheet
    ) {
        $filters = $this->filters($request);
        $transactions = $report->query($filters)->orderBy('credit_sales.due_date')->get();

        if ($response = $security->authorizeReceivableReportExport($request, 'xlsx', $filters, $transactions)) {
            return $response;
        }

        $records = $transactions->map(fn ($transaction) => [
            'customer' => $transaction->customer?->customer_name,
            'customer_code' => $transaction->customer?->customer_code,
            'receipt' => $transaction->receipt_no,
            'transaction_date' => $transaction->date,
            'due_date' => $transaction->creditSale->due_date,
            'net_sale' => (float) $transaction->ar_net_amount,
            'paid' => (float) $transaction->ar_paid_amount,
            'outstanding' => (float) $transaction->ar_balance_amount,
            'status' => Str::headline($report->status($transaction)),
        ]);

        return $spreadsheet->download(
            'accounts-receivable-'.now()->format('Ymd-His').'.xlsx',
            app(SystemSettingsService::class)->get('business.name', 'Acry-Link'),
            'Accounts Receivable Report',
            [
                ['label' => 'Generated', 'value' => now()->format('F j, Y g:i A')],
                ['label' => 'Filters', 'value' => $this->filterLabel($filters)],
                ['label' => 'Accounts', 'value' => $transactions->count()],
                ['label' => 'Total outstanding', 'value' => '₱'.number_format($transactions->sum('ar_balance_amount'), 2)],
            ],
            [
                ['key' => 'customer', 'label' => 'Customer', 'type' => 'text', 'width' => 26],
                ['key' => 'customer_code', 'label' => 'Customer Code', 'type' => 'text', 'width' => 18],
                ['key' => 'receipt', 'label' => 'Receipt', 'type' => 'text', 'width' => 17],
                ['key' => 'transaction_date', 'label' => 'Transaction Date', 'type' => 'date', 'width' => 18],
                ['key' => 'due_date', 'label' => 'Due Date', 'type' => 'date', 'width' => 16],
                ['key' => 'net_sale', 'label' => 'Net Sale', 'type' => 'currency', 'width' => 16],
                ['key' => 'paid', 'label' => 'Paid', 'type' => 'currency', 'width' => 16],
                ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'currency', 'width' => 17],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status', 'width' => 18],
            ],
            $records,
            'Receivables'
        );
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

    private function filterLabel(array $filters): string
    {
        $labels = collect([
            filled($filters['customer'] ?? null) ? 'Customer: '.$filters['customer'] : null,
            filled($filters['receipt'] ?? null) ? 'Receipt: '.$filters['receipt'] : null,
            filled($filters['payment_status'] ?? null) ? 'Status: '.Str::headline($filters['payment_status']) : null,
            filled($filters['aging'] ?? null) ? 'Aging: '.str_replace('_', '–', $filters['aging']) : null,
            filled($filters['due_from'] ?? null) ? 'Due from: '.$filters['due_from'] : null,
            filled($filters['due_to'] ?? null) ? 'Due to: '.$filters['due_to'] : null,
            filled($filters['transaction_from'] ?? null) ? 'Transaction from: '.$filters['transaction_from'] : null,
            filled($filters['transaction_to'] ?? null) ? 'Transaction to: '.$filters['transaction_to'] : null,
        ])->filter();

        return $labels->isEmpty() ? 'All accounts receivable records' : $labels->join(' | ');
    }

    private function csvCell(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
