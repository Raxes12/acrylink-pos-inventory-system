<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\CreditSale;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\SecurityRule;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\FinancialReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinancialReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-19 12:00:00');
        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_financial_metrics_separate_sale_dates_from_collection_dates(): void
    {
        $normal = $this->transaction('RPT-CASH-TODAY', 100, today());
        $this->payment($normal, 100, today()->setTime(9, 0));

        $todayCredit = $this->creditTransaction('RPT-CREDIT-TODAY', 1000, today(), today()->addDays(30), 'Today Credit');
        $this->payment($todayCredit, 200, today()->setTime(10, 0));

        $oldCredit = $this->creditTransaction('RPT-CREDIT-OLD', 500, today()->subDays(10), today()->subDay(), 'Old Credit');
        $this->payment($oldCredit, 100, today()->setTime(11, 0));

        $metrics = app(FinancialReportingService::class)->todayMetrics();
        $this->assertSame(1100.0, $metrics['today_net_sales']);
        $this->assertSame(400.0, $metrics['today_collections']);
        $this->assertSame(1000.0, $metrics['today_credit_sales']);
        $this->assertSame(1200.0, $metrics['total_accounts_receivable']);
        $this->assertSame(400.0, $metrics['overdue_receivables']);

        $this->get(route('reports.index', ['range' => 'today']))
            ->assertOk()
            ->assertSee('Net Sales')
            ->assertSee('Collections')
            ->assertSee('From selected-period sales')
            ->assertSee('From earlier credit sales')
            ->assertSee('Why can this be higher than Net Sales?')
            ->assertSee('Credit Sales')
            ->assertSee('Gross Revenue')
            ->assertSee('Total Discounts')
            ->assertSee('Average Transaction')
            ->assertDontSee('Total Accounts Receivable')
            ->assertDontSee('Overdue Receivables')
            ->assertSee('1,100.00')
            ->assertSee('400.00')
            ->assertSee('300.00')
            ->assertSee('100.00')
            ->assertSee('1,000.00')
            ->assertDontSee('RPT-CREDIT-OLD');

        $customFilters = [
            'range' => 'custom',
            'date_from' => today()->subDays(10)->toDateString(),
            'date_to' => today()->subDays(10)->toDateString(),
        ];
        $customPdfUrl = route('reports.export.pdf', $customFilters);
        $customExcelUrl = route('reports.export.xlsx', $customFilters);

        $this->get(route('reports.index', $customFilters))->assertOk()
            ->assertViewHas('range', 'custom')
            ->assertViewHas('reportLabel', 'September 9, 2026')
            ->assertViewHas('exportPdfUrl', $customPdfUrl)
            ->assertViewHas('exportExcelUrl', $customExcelUrl)
            ->assertSee('500.00')
            ->assertSee('RPT-CREDIT-OLD')
            ->assertDontSee('RPT-CASH-TODAY')
            ->assertSee('Custom date range')
            ->assertSee('id="salesReportDateFilter"', false)
            ->assertDontSee('data-live-submit-only', false);

        SecurityRule::whereIn('rule_name', ['bulk_data_export', 'large_report_date_range'])
            ->update(['is_active' => false]);
        $this->get($customPdfUrl)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('sales-report-september-9-2026-20260919-120000.pdf');
        $excel = $this->get($customExcelUrl)
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload('sales-report-september-9-2026-20260919-120000.xlsx');
        $this->assertStringStartsWith('PK', $excel->getContent());
    }

    public function test_accounts_receivable_report_displays_totals_status_and_aging_buckets(): void
    {
        $notDue = $this->creditTransaction('RPT-AR-NOT-DUE', 1000, today(), today()->addDays(5), 'Future Customer');
        $this->payment($notDue, 250, today());
        $this->creditTransaction('RPT-AR-1-30', 200, today()->subDays(20), today()->subDays(15), 'Aging Customer');
        $this->creditTransaction('RPT-AR-31-60', 300, today()->subDays(60), today()->subDays(45), 'Aging Customer Two');
        $this->creditTransaction('RPT-AR-61-90', 400, today()->subDays(100), today()->subDays(75), 'Aging Customer Three');
        $this->creditTransaction('RPT-AR-90', 500, today()->subDays(150), today()->subDays(100), 'Aging Customer Four');

        $this->get(route('reports.accounts-receivable'))
            ->assertOk()
            ->assertSee('Receivables Report')
            ->assertSee('Not Yet Due')
            ->assertSee('750.00')
            ->assertSee('1-30 Days')
            ->assertSee('200.00')
            ->assertSee('31-60 Days')
            ->assertSee('300.00')
            ->assertSee('61-90 Days')
            ->assertSee('400.00')
            ->assertSee('90+ Days')
            ->assertSee('500.00')
            ->assertSee('Partially paid')
            ->assertSee('Overdue');

        $this->get(route('reports.accounts-receivable', ['aging' => '31_60']))
            ->assertOk()->assertSee('RPT-AR-31-60')->assertDontSee('RPT-AR-1-30');
    }

    public function test_sales_report_groups_net_sales_by_sales_associate(): void
    {
        $transaction = $this->transaction('RPT-ASSOCIATE', 1250, today());
        $transaction->update(['sales_associate' => 'Maria Santos']);

        $this->get(route('reports.index', ['range' => 'today']))
            ->assertOk()
            ->assertViewHas('bySalesAssociate', fn ($associates) => (float) $associates->get('Maria Santos') === 1250.0)
            ->assertSee('Sales by Associate')
            ->assertSee('Maria Santos')
            ->assertSee('Associate: Maria Santos');
    }

    public function test_receivables_report_filters_customer_collections_by_payment_period(): void
    {
        $current = $this->creditTransaction('RPT-COLLECTION-CURRENT', 200, today(), today()->addDays(30), 'Current Collection');
        $older = $this->creditTransaction('RPT-COLLECTION-OLDER', 200, today()->subMonths(2), today()->subMonth(), 'Older Collection');
        $this->payment($current, 75, today()->setTime(9, 0));
        $this->payment($older, 25, today()->subMonth()->setTime(9, 0));

        $this->get(route('reports.accounts-receivable', ['collection_period' => 'today']))
            ->assertOk()
            ->assertViewHas('collectionPeriod', 'today')
            ->assertViewHas('totalCollections', 75.0)
            ->assertSee('Total Customer Collections')
            ->assertSee('Reporting period')
            ->assertSee('This Week')
            ->assertSee('This Month')
            ->assertSee('All Time');

        $this->get(route('reports.accounts-receivable', ['collection_period' => 'all']))
            ->assertOk()
            ->assertViewHas('totalCollections', 100.0);
    }

    public function test_receivables_csv_and_pdf_exports_are_separate_from_sales_exports(): void
    {
        $this->creditTransaction('RPT-AR-EXPORT', 750, today(), today()->addDays(30), 'Export Customer');

        $csv = $this->get(route('reports.accounts-receivable.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('ACCOUNTS RECEIVABLE REPORT', $csv);
        $this->assertStringContainsString('Customer,"Customer Code",Receipt,"Transaction Date","Due Date","Net Sale",Paid,Outstanding,Status', $csv);
        $this->assertStringContainsString('RPT-AR-EXPORT', $csv);
        $this->assertStringContainsString('750.00', $csv);

        $this->get(route('reports.accounts-receivable.export.pdf'))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $excel = $this->get(route('reports.accounts-receivable.export.xlsx'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $excel->getContent());
        $salesCsv = $this->get(route('reports.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('SALES REPORT', $salesCsv);
        $this->assertStringNotContainsString('ACCOUNTS RECEIVABLE REPORT', $salesCsv);
    }

    public function test_receivables_report_remains_admin_only(): void
    {
        $this->actingAs(User::where('role', User::ROLE_CASHIER)->firstOrFail())
            ->get(route('reports.accounts-receivable'))->assertForbidden();
        $this->get(route('reports.accounts-receivable.export'))->assertForbidden();
        $this->get(route('reports.accounts-receivable.export.xlsx'))->assertForbidden();
        $this->get(route('reports.accounts-receivable.export.pdf'))->assertForbidden();
    }

    public function test_receivables_exports_reuse_existing_intrusion_monitoring(): void
    {
        SecurityRule::where('rule_name', 'bulk_data_export')->update([
            'threshold_value' => 1,
            'severity' => 'medium',
            'action' => 'alert',
        ]);
        SecurityRule::where('rule_name', 'large_report_date_range')->update(['is_active' => false]);

        $this->get(route('reports.accounts-receivable.export'))->assertOk();
        $this->get(route('reports.accounts-receivable.export'))->assertOk();

        $this->assertDatabaseHas('intrusion_events', [
            'event_type' => 'bulk_data_export',
            'source_module' => 'reports',
            'severity' => 'medium',
        ]);
    }

    private function creditTransaction(string $receipt, float $total, Carbon $saleDate, Carbon $dueDate, string $customerName): Transaction
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => $customerName,
            'credit_enabled' => true,
            'credit_limit' => 10000,
        ]);
        $transaction = $this->transaction($receipt, $total, $saleDate, $customer);
        CreditSale::create([
            'transaction_id' => $transaction->id,
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'due_date' => $dueDate,
            'original_credit_amount' => $total,
        ]);

        return $transaction;
    }

    private function transaction(string $receipt, float $total, Carbon $saleDate, ?Customer $customer = null): Transaction
    {
        return Transaction::create([
            'receipt_no' => $receipt,
            'checkout_token' => (string) Str::uuid(),
            'date' => $saleDate,
            'time' => '08:00:00',
            'cashier_id' => $this->admin->id,
            'customer_id' => $customer?->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method' => $customer ? 'credit_account' : 'cash',
            'payment' => $customer ? 0 : $total,
            'change_due' => 0,
            'payment_status' => $customer ? 'unpaid' : 'paid',
            'status' => 'completed',
        ]);
    }

    private function payment(Transaction $transaction, float $amount, Carbon $paidAt): TransactionPayment
    {
        return TransactionPayment::create([
            'transaction_id' => $transaction->id,
            'payment_method_id' => PaymentMethod::where('code', 'cash')->value('id'),
            'amount' => $amount,
            'received_by' => $this->admin->id,
            'paid_at' => $paidAt,
        ]);
    }
}
