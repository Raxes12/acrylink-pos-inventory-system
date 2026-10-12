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
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\AccountsReceivableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountsReceivableTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-19 10:00:00');
        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
        $this->cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_summarizes_only_open_non_voided_credit_balances(): void
    {
        $dueSoon = $this->creditSale('AR-DUE-SOON', 1000, today()->addDays(3));
        $this->pay($dueSoon, 200);
        $overdue = $this->creditSale('AR-OVERDUE', 500, today()->subDay());
        Refund::create([
            'refund_no' => 'REF-AR-001', 'transaction_id' => $overdue->id,
            'refund_amount' => 100, 'refund_method_id' => PaymentMethod::where('code', 'cash')->value('id'),
            'reason' => 'Returned item', 'processed_by' => $this->cashier->id,
        ]);
        $paid = $this->creditSale('AR-PAID', 300, today()->addDays(2));
        $this->pay($paid, 300);
        $voided = $this->creditSale('AR-VOID', 700, today()->subDays(2));
        $voided->update(['status' => 'voided']);

        $this->actingAs($this->cashier)->get(route('accounts-receivable.index'))
            ->assertOk()
            ->assertSee('1,200.00')
            ->assertSee('800.00')
            ->assertSee('400.00')
            ->assertSee('2')
            ->assertSee('AR-DUE-SOON')
            ->assertSee('AR-OVERDUE')
            ->assertSee('AR-PAID')
            ->assertDontSee('AR-VOID');
    }

    public function test_filters_customer_receipt_status_and_date_ranges(): void
    {
        $alpha = $this->customer('Alpha Fleet');
        $partial = $this->creditSale('AR-ALPHA-01', 1000, today()->addDays(5), $alpha, today()->subDays(2));
        $this->pay($partial, 250);
        $this->creditSale('AR-BETA-01', 500, today()->subDay(), $this->customer('Beta Garage'), today()->subDays(10));

        $this->actingAs($this->cashier)->get(route('accounts-receivable.index', [
            'customer' => 'Alpha',
            'receipt' => 'ALPHA',
            'payment_status' => 'partially_paid',
            'due_from' => today()->toDateString(),
            'due_to' => today()->addWeek()->toDateString(),
            'transaction_from' => today()->subDays(3)->toDateString(),
            'transaction_to' => today()->toDateString(),
        ]))->assertOk()->assertSee('AR-ALPHA-01')->assertDontSee('AR-BETA-01');
    }

    public function test_compact_search_and_status_filters_cover_open_due_paid_and_partial_sales(): void
    {
        $alpha = $this->customer('Alpha Fleet');
        $partial = $this->creditSale('AR-ALPHA-PARTIAL', 1000, today()->addDays(3), $alpha);
        $this->pay($partial, 250);
        $paid = $this->creditSale('AR-ALPHA-PAID', 500, today()->addDays(20), $alpha);
        $this->pay($paid, 500);
        $this->creditSale('AR-BETA-OVERDUE', 300, today()->subDay(), $this->customer('Beta Garage'));

        $this->actingAs($this->cashier)
            ->get(route('accounts-receivable.index', ['search' => 'ALPHA-PARTIAL']))
            ->assertOk()->assertSee('AR-ALPHA-PARTIAL')->assertDontSee('AR-BETA-OVERDUE');
        $this->get(route('accounts-receivable.index', ['payment_status' => 'due_soon']))
            ->assertOk()->assertSee('AR-ALPHA-PARTIAL')->assertDontSee('AR-ALPHA-PAID');
        $this->get(route('accounts-receivable.index', ['payment_status' => 'partially_paid']))
            ->assertOk()->assertSee('AR-ALPHA-PARTIAL')->assertDontSee('AR-ALPHA-PAID');
        $this->get(route('accounts-receivable.index', ['payment_status' => 'paid']))
            ->assertOk()->assertSee('AR-ALPHA-PAID')->assertDontSee('AR-ALPHA-PARTIAL');
    }

    public function test_accounts_receivable_page_uses_compact_live_filters_without_date_inputs(): void
    {
        $this->actingAs($this->cashier)->get(route('accounts-receivable.index'))
            ->assertOk()
            ->assertSee('Search customer name, code, or receipt number...')
            ->assertSee('data-live-filter', false)
            ->assertSee('data-live-filter-choice="payment_status"', false)
            ->assertDontSee('name="due_from"', false)
            ->assertDontSee('name="due_to"', false)
            ->assertDontSee('name="transaction_from"', false)
            ->assertDontSee('name="transaction_to"', false)
            ->assertDontSee('Apply Filters');
    }

    public function test_customer_credit_detail_shows_limits_open_sales_and_payment_history(): void
    {
        $customer = $this->customer('Detail Customer', 5000, 45);
        $transaction = $this->creditSale('AR-DETAIL-01', 1200, today()->addDays(45), $customer);
        $payment = $this->pay($transaction, 200, 'GCASH-AR-1');

        $this->actingAs($this->cashier)
            ->get(route('accounts-receivable.customers.show', $customer))
            ->assertOk()
            ->assertSee('Detail Customer')
            ->assertSee('5,000.00')
            ->assertSee('1,000.00')
            ->assertSee('4,000.00')
            ->assertSee('45 days')
            ->assertSee('AR-DETAIL-01')
            ->assertSee('GCASH-AR-1')
            ->assertSee(route('pos.credit-payments.create', $transaction), false)
            ->assertSee(route('pos.credit-payments.show', $payment), false)
            ->assertSee(route('pos.receipt', $transaction), false);

        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail())
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Credit Transactions')
            ->assertSee('Payment History')
            ->assertSee('AR-DETAIL-01')
            ->assertSee('GCASH-AR-1')
            ->assertSee('1,000.00')
            ->assertSee('4,000.00')
            ->assertSee(route('pos.credit-payments.create', $transaction), false)
            ->assertSee(route('pos.credit-payments.show', $payment), false);
    }

    public function test_accounts_receivable_routes_follow_sales_rbac(): void
    {
        $customer = $this->customer('RBAC Customer');
        $this->actingAs($this->cashier)->get(route('accounts-receivable.index'))->assertOk();
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail())
            ->get(route('accounts-receivable.customers.show', $customer))->assertOk();
        $this->actingAs(User::where('role', User::ROLE_INVENTORY)->firstOrFail())
            ->get(route('accounts-receivable.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('accounts-receivable.index'))->assertRedirect(route('login'));
    }

    public function test_cashier_receivables_and_customer_history_only_include_their_sales(): void
    {
        $customer = $this->customer('Shared Credit Customer');
        $own = $this->creditSale('AR-OWN-SALE', 400, today()->addDays(5), $customer);
        $this->pay($own, 50, 'OWN-PAYMENT');

        $otherCashier = User::create([
            'username' => 'ar_other_cashier',
            'name' => 'Other AR Cashier',
            'email' => 'ar-other@example.test',
            'password' => $this->cashier->password,
            'role' => User::ROLE_CASHIER,
            'role_id' => $this->cashier->role_id,
            'status' => 'active',
        ]);
        $other = $this->creditSale('AR-OTHER-SALE', 900, today()->subDay(), $customer);
        $other->update(['cashier_id' => $otherCashier->id]);
        TransactionPayment::create([
            'transaction_id' => $other->id,
            'payment_method_id' => PaymentMethod::where('code', 'gcash')->value('id'),
            'amount' => 100,
            'payment_reference' => 'OTHER-PAYMENT',
            'received_by' => $otherCashier->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($this->cashier)
            ->get(route('accounts-receivable.index'))
            ->assertOk()
            ->assertSee('AR-OWN-SALE')
            ->assertDontSee('AR-OTHER-SALE');
        $this->get(route('accounts-receivable.customers.show', $customer))
            ->assertOk()
            ->assertSee('AR-OWN-SALE')
            ->assertSee('OWN-PAYMENT')
            ->assertDontSee('AR-OTHER-SALE')
            ->assertDontSee('OTHER-PAYMENT');

        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail())
            ->get(route('accounts-receivable.index'))
            ->assertOk()
            ->assertSee('AR-OWN-SALE')
            ->assertSee('AR-OTHER-SALE');
    }

    public function test_receivable_rows_are_eager_loaded_and_do_not_query_per_row(): void
    {
        foreach (range(1, 12) as $number) {
            $this->creditSale('AR-N1-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT), 100 + $number, today()->addDays($number));
        }

        DB::enableQueryLog();
        DB::flushQueryLog();
        $rows = app(AccountsReceivableService::class)->openReceivablesQuery()->get();
        $loadQueryCount = count(DB::getQueryLog());
        DB::flushQueryLog();

        foreach ($rows as $row) {
            $this->assertNotNull($row->customer?->customer_name);
            $this->assertNotNull($row->cashier?->name);
            $this->assertNotNull($row->creditSale?->due_date);
            app(AccountsReceivableService::class)->statusForRow($row);
        }

        $this->assertLessThanOrEqual(4, $loadQueryCount);
        $this->assertCount(0, DB::getQueryLog());
    }

    private function customer(string $name, float $limit = 10000, int $term = 30): Customer
    {
        return Customer::createWithGeneratedCode([
            'customer_name' => $name,
            'credit_enabled' => true,
            'credit_limit' => $limit,
            'default_credit_term_days' => $term,
        ]);
    }

    private function creditSale(string $receipt, float $total, Carbon $dueDate, ?Customer $customer = null, ?Carbon $saleDate = null): Transaction
    {
        $customer ??= $this->customer($receipt.' Customer');
        $transaction = Transaction::create([
            'receipt_no' => $receipt,
            'checkout_token' => (string) Str::uuid(),
            'date' => ($saleDate ?? today())->toDateString(),
            'time' => now()->toTimeString(),
            'cashier_id' => $this->cashier->id,
            'customer_id' => $customer->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method_id' => PaymentMethod::where('code', 'credit_account')->value('id'),
            'payment' => 0,
            'change_due' => 0,
            'payment_status' => 'unpaid',
            'status' => 'completed',
        ]);
        CreditSale::create([
            'transaction_id' => $transaction->id,
            'customer_id' => $customer->id,
            'credit_term_days' => max(1, today()->diffInDays($dueDate, false)),
            'due_date' => $dueDate->toDateString(),
            'original_credit_amount' => $total,
        ]);

        return $transaction->fresh();
    }

    private function pay(Transaction $transaction, float $amount, ?string $reference = null): TransactionPayment
    {
        return TransactionPayment::create([
            'transaction_id' => $transaction->id,
            'payment_method_id' => PaymentMethod::where('code', $reference ? 'gcash' : 'cash')->value('id'),
            'amount' => $amount,
            'payment_reference' => $reference,
            'received_by' => $this->cashier->id,
            'paid_at' => now(),
        ]);
    }
}
