<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\Customer;
use App\Models\AuditLog;
use App\Models\IntrusionEvent;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\SecurityRule;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class POSCustomerCreditTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
        $this->actingAs(User::where('role', User::ROLE_CASHIER)->firstOrFail());
        $this->variant = ProductVariant::with('inventoryStock')->firstOrFail();
    }

    public function test_pos_exposes_customer_search_and_credit_sale_fields_without_preloading_customers(): void
    {
        Customer::createWithGeneratedCode(['customer_name' => 'Not Embedded', 'status' => 'active']);

        $this->get(route('pos.index'))
            ->assertOk()
            ->assertSee(route('pos.customers'), false)
            ->assertDontSee('Credit Card')
            ->assertSee('Credit Sale')
            ->assertSee('Current Outstanding')
            ->assertSee('Available Credit')
            ->assertDontSee('Not Embedded');
    }

    public function test_server_side_search_limits_results_and_enforces_credit_eligibility(): void
    {
        foreach (range(1, 12) as $number) {
            Customer::createWithGeneratedCode([
                'customer_name' => "Search Customer {$number}",
                'phone' => "091700000{$number}",
                'credit_enabled' => $number !== 12,
                'credit_limit' => 5000,
                'status' => $number === 11 ? 'inactive' : 'active',
            ]);
        }

        $this->getJson(route('pos.customers', ['search' => 'Search Customer']))
            ->assertOk()
            ->assertJsonCount(10, 'customers');

        $creditResults = $this->getJson(route('pos.customers', ['search' => 'Search Customer', 'credit_only' => 1]))
            ->assertOk()
            ->json('customers');

        $this->assertCount(10, $creditResults);
        $this->assertTrue(collect($creditResults)->every(fn (array $customer) => $customer['credit_enabled'] === true));
        $this->assertTrue(collect($creditResults)->every(fn (array $customer) => array_key_exists('available_credit', $customer)));
        $this->getJson(route('pos.customers', ['search' => 'x']))->assertUnprocessable();
    }

    public function test_credit_checkout_revalidates_customer_terms_and_creates_unpaid_credit_sale(): void
    {
        Carbon::setTestNow('2026-09-16 10:30:00');
        $price = (float) $this->variant->selling_price;
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Eligible Account',
            'credit_enabled' => true,
            'credit_limit' => $price + 100,
            'default_credit_term_days' => 30,
            'status' => 'active',
        ]);

        $response = $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 45,
            'payment' => 0,
            'due_date' => '2099-01-01',
        ]));

        $transaction = Transaction::with('creditSale')->latest('id')->firstOrFail();
        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertSame($customer->id, $transaction->customer_id);
        $this->assertSame('credit_account', $transaction->payment_method);
        $this->assertSame('unpaid', $transaction->payment_status);
        $this->assertSame('0.00', $transaction->payment);
        $this->assertSame(0, $transaction->payments()->count());
        $this->assertSame(45, $transaction->creditSale->credit_term_days);
        $this->assertSame('2026-10-31', $transaction->creditSale->due_date->toDateString());
        $this->assertSame(number_format((float) $transaction->total, 2, '.', ''), $transaction->creditSale->original_credit_amount);
        $this->assertSame((float) $transaction->total, $transaction->outstandingBalance());
        $creditSaleAudit = AuditLog::where('action', 'CREDIT_SALE')->sole();
        $this->assertSame($transaction->id, $creditSaleAudit->new_values['transaction_id']);
        $this->assertSame($customer->id, $creditSaleAudit->new_values['customer_id']);
        $this->assertSame($transaction->creditSale->id, (int) $creditSaleAudit->affected_record_id);

        $creditResult = $this->getJson(route('pos.customers', [
            'search' => 'Eligible Account',
            'credit_only' => 1,
        ]))->assertOk()->json('customers.0');
        $this->assertSame((float) $transaction->total, (float) $creditResult['current_outstanding']);
        $this->assertSame(100.0, (float) $creditResult['available_credit']);

        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Eligible Account')
            ->assertSee('Payment Method:')
            ->assertSee('Credit Sale')
            ->assertSee('Sale Total:')
            ->assertSee('Initial Paid Amount:')
            ->assertSee('Current Total Paid:')
            ->assertSee('Outstanding Balance:')
            ->assertSee('Credit Term:')
            ->assertSee('Oct 31, 2026')
            ->assertSee('Payment Status:')
            ->assertSee('Unpaid')
            ->assertDontSee('Card Reference:');
    }

    public function test_backend_rejects_missing_inactive_disabled_and_over_limit_credit_customers(): void
    {
        $disabled = Customer::createWithGeneratedCode([
            'customer_name' => 'Disabled Credit', 'credit_enabled' => false, 'credit_limit' => 999999, 'status' => 'active',
        ]);
        $inactive = Customer::createWithGeneratedCode([
            'customer_name' => 'Inactive Credit', 'credit_enabled' => true, 'credit_limit' => 999999, 'status' => 'inactive',
        ]);
        $limited = Customer::createWithGeneratedCode([
            'customer_name' => 'Limited Credit', 'credit_enabled' => true, 'credit_limit' => 0, 'status' => 'active',
        ]);
        $negativeLimit = Customer::createWithGeneratedCode([
            'customer_name' => 'Negative Credit', 'credit_enabled' => true, 'credit_limit' => -1, 'status' => 'active',
        ]);

        $this->post(route('pos.checkout'), $this->payload('credit_account'))->assertSessionHasErrors('customer_id');
        $this->post(route('pos.checkout'), $this->payload('credit_account', ['customer_id' => $disabled->id, 'credit_term_days' => 30]))->assertSessionHasErrors('customer_id');
        $this->post(route('pos.checkout'), $this->payload('credit_account', ['customer_id' => $inactive->id, 'credit_term_days' => 30]))->assertSessionHasErrors('customer_id');
        $this->post(route('pos.checkout'), $this->payload('credit_account', ['customer_id' => $limited->id, 'credit_term_days' => 30]))->assertSessionHasErrors('customer_id');
        $this->post(route('pos.checkout'), $this->payload('credit_account', ['customer_id' => $negativeLimit->id, 'credit_term_days' => 30]))->assertSessionHasErrors('customer_id');
        $this->post(route('pos.checkout'), $this->payload('credit_account', ['customer_id' => $disabled->id, 'credit_term_days' => 0]))->assertSessionHasErrors('credit_term_days');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_credit_checkout_token_remains_idempotent(): void
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Token Customer', 'credit_enabled' => true, 'credit_limit' => 999999, 'status' => 'active',
        ]);
        $token = (string) Str::uuid();
        $payload = $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'payment' => 10,
            'initial_payment_method' => 'cash',
            'checkout_token' => $token,
        ]);
        $stock = (int) $this->variant->inventoryStock->quantity_on_hand;

        $this->post(route('pos.checkout'), $payload)->assertRedirect();
        $this->post(route('pos.checkout'), $payload)->assertRedirect();

        $this->assertSame(1, Transaction::where('checkout_token', $token)->count());
        $this->assertDatabaseCount('credit_sales', 1);
        $this->assertDatabaseCount('transaction_payments', 1);
        $this->assertSame('cash', Transaction::where('checkout_token', $token)->firstOrFail()->payments()->firstOrFail()->paymentMethod->code);
        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $this->variant->id,
            'quantity_on_hand' => $stock - 1,
        ]);
    }

    public function test_partial_initial_payment_uses_actual_method_and_finances_only_remaining_balance(): void
    {
        $price = (float) $this->variant->selling_price;
        $initialPayment = round($price / 2, 2);
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Partial Account',
            'credit_enabled' => true,
            'credit_limit' => $price - $initialPayment,
            'status' => 'active',
        ]);

        $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'payment' => $initialPayment,
            'initial_payment_method' => 'gcash',
            'initial_payment_reference' => 'GCASH-INITIAL-1',
        ]))->assertRedirect();

        $transaction = Transaction::with('payments.paymentMethod', 'creditSale')->latest('id')->firstOrFail();
        $this->assertSame('completed', $transaction->status);
        $this->assertSame('partially_paid', $transaction->payment_status);
        $this->assertSame($initialPayment, (float) $transaction->payment);
        $this->assertSame('credit_account', $transaction->payment_method);
        $this->assertSame('gcash', $transaction->payments->sole()->paymentMethod->code);
        $this->assertSame('GCASH-INITIAL-1', $transaction->payments->sole()->payment_reference);
        $this->assertSame($initialPayment, $transaction->totalPaid());
        $this->assertSame(round($price - $initialPayment, 2), $transaction->outstandingBalance());
        $this->assertSame(number_format($price, 2, '.', ''), $transaction->creditSale->original_credit_amount);

        app(TransactionSettlementService::class)->recordAppliedPayment($transaction, [
            'payment_method_id' => PaymentMethod::where('code', 'cash')->value('id'),
            'amount' => 10,
            'received_by' => auth()->id(),
            'paid_at' => now()->addDay(),
            'notes' => 'Later receivable payment',
        ]);
        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Initial Paid Amount:')
            ->assertSee(number_format($initialPayment, 2))
            ->assertSee('Current Total Paid:')
            ->assertSee(number_format($initialPayment + 10, 2));
    }

    public function test_fully_settled_credit_sale_records_actual_cash_payment_and_paid_status(): void
    {
        $price = (float) $this->variant->selling_price;
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Settled Account', 'credit_enabled' => true, 'credit_limit' => 1, 'status' => 'active',
        ]);

        $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 15,
            'payment' => $price,
            'initial_payment_method' => 'cash',
        ]))->assertRedirect();

        $transaction = Transaction::with('payments.paymentMethod')->latest('id')->firstOrFail();
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertSame(0.0, $transaction->outstandingBalance());
        $this->assertSame('cash', $transaction->payments->sole()->paymentMethod->code);
        $this->assertNull($transaction->payments->sole()->payment_reference);
        $this->assertNull($transaction->payment_card_last4);
    }

    public function test_initial_payment_validation_rejects_invalid_or_missing_collection_details(): void
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Validation Account', 'credit_enabled' => true, 'credit_limit' => 999999, 'status' => 'active',
        ]);
        $base = ['customer_id' => $customer->id, 'credit_term_days' => 30, 'payment' => 10];

        $this->post(route('pos.checkout'), $this->payload('credit_account', $base))
            ->assertSessionHasErrors('initial_payment_method');
        $this->post(route('pos.checkout'), $this->payload('credit_account', $base + ['initial_payment_method' => 'gcash']))
            ->assertSessionHasErrors('initial_payment_reference');
        $this->post(route('pos.checkout'), $this->payload('credit_account', $base + ['initial_payment_method' => 'check']))
            ->assertSessionHasErrors('initial_check_number');
        $this->post(route('pos.checkout'), $this->payload('credit_account', array_merge($base, [
            'payment' => (float) $this->variant->selling_price + 1, 'initial_payment_method' => 'cash',
        ])))->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('transaction_payments', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_check_can_be_the_actual_initial_collection_method(): void
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Check Account', 'credit_enabled' => true, 'credit_limit' => 999999, 'status' => 'active',
        ]);

        $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'payment' => 10,
            'initial_payment_method' => 'check',
            'initial_check_number' => 'CHK-1001',
            'initial_check_bank' => 'Test Bank',
            'initial_check_date' => '2026-09-19',
        ]))->assertRedirect();

        $transaction = Transaction::with('payments.paymentMethod')->latest('id')->firstOrFail();
        $this->assertSame('partially_paid', $transaction->payment_status);
        $this->assertSame('check', $transaction->payments->sole()->paymentMethod->code);
        $this->assertSame('CHK-1001', $transaction->check_number);
        $this->assertSame('Test Bank', $transaction->check_bank);
        $this->assertSame('2026-09-19', $transaction->check_date->toDateString());
        $payment = $transaction->payments->sole();
        $this->assertSame('CHK-1001', $payment->check_number);
        $this->assertSame('Test Bank', $payment->check_bank);
        $this->assertSame('2026-09-19', $payment->check_date->toDateString());
    }

    public function test_projected_outstanding_rejection_reports_server_calculated_amounts(): void
    {
        $price = (float) $this->variant->selling_price;
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Projected Account',
            'credit_enabled' => true,
            'credit_limit' => ($price * 2) - 0.01,
            'status' => 'active',
        ]);

        $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
        ]))->assertRedirect();

        $response = $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
        ]));

        $response->assertSessionHasErrors('customer_id');
        $message = session('errors')->first('customer_id');
        $this->assertStringContainsString('Credit Limit: ₱'.number_format(($price * 2) - 0.01, 2), $message);
        $this->assertStringContainsString('Current Outstanding: ₱'.number_format($price, 2), $message);
        $this->assertStringContainsString('Available Credit: ₱'.number_format($price - 0.01, 2), $message);
        $this->assertStringContainsString('Requested Credit: ₱'.number_format($price, 2), $message);
        $this->assertStringContainsString('Credit limit exceeded.', $message);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('credit_sales', 1);
        $attempt = AuditLog::where('action', 'CREDIT_LIMIT_EXCEEDED_ATTEMPT')->sole();
        $this->assertSame($customer->id, $attempt->new_values['customer_id']);
        $this->assertEquals($price, $attempt->new_values['requested_credit']);
        $this->assertEquals($price * 2, $attempt->new_values['projected_outstanding']);
    }

    public function test_credit_sale_at_exact_available_limit_is_approved(): void
    {
        $price = (float) $this->variant->selling_price;
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Exact Limit Account',
            'credit_enabled' => true,
            'credit_limit' => $price * 2,
            'status' => 'active',
        ]);

        foreach (range(1, 2) as $attempt) {
            $this->post(route('pos.checkout'), $this->payload('credit_account', [
                'customer_id' => $customer->id,
                'credit_term_days' => 30,
            ]))->assertSessionHasNoErrors();
        }

        $this->assertSame(2, Transaction::where('customer_id', $customer->id)->count());
        $this->assertDatabaseCount('credit_sales', 2);
        $this->assertSame(0, AuditLog::where('action', 'CREDIT_LIMIT_EXCEEDED_ATTEMPT')->count());
    }

    public function test_large_credit_sale_uses_existing_transaction_anomaly_rule(): void
    {
        SecurityRule::where('rule_name', 'large_transaction')->update([
            'threshold_value' => 1,
            'action' => 'alert',
            'is_active' => true,
        ]);
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Monitored Account',
            'credit_enabled' => true,
            'credit_limit' => (float) $this->variant->selling_price + 100,
            'status' => 'active',
        ]);

        $this->post(route('pos.checkout'), $this->payload('credit_account', [
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('intrusion_events', [
            'event_type' => 'large_transaction',
            'source_module' => 'transaction',
        ]);
        $this->assertSame(1, IntrusionEvent::where('event_type', 'large_transaction')->count());
        $this->assertSame(1, AuditLog::where('action', 'CREDIT_SALE')->count());
    }

    public function test_non_credit_checkout_ignores_stale_customer_and_credit_fields(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Cash Customer', 'status' => 'active']);

        $this->post(route('pos.checkout'), $this->payload('cash', [
            'customer_id' => $customer->id,
            'payment' => $this->variant->selling_price,
        ]))->assertRedirect();
        $transaction = Transaction::latest('id')->firstOrFail();
        $this->assertNull($transaction->customer_id);
        $this->assertFalse($transaction->creditSale()->exists());

        $this->post(route('pos.checkout'), $this->payload('cash', [
            'checkout_token' => (string) Str::uuid(),
            'payment' => $this->variant->selling_price,
        ]))->assertRedirect();
        $this->assertNull(Transaction::latest('id')->value('customer_id'));
    }

    public function test_customer_search_uses_existing_sales_rbac(): void
    {
        $this->actingAs(User::where('role', User::ROLE_INVENTORY)->firstOrFail());
        $this->getJson(route('pos.customers', ['search' => 'Customer']))->assertForbidden();

        auth()->logout();
        $this->get(route('pos.customers', ['search' => 'Customer']))->assertRedirect(route('login'));
    }

    private function payload(string $method, array $overrides = []): array
    {
        return array_merge([
            'items' => [[
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'unit_price' => $this->variant->selling_price,
            ]],
            'discount' => 0,
            'payment_method' => $method,
            'payment' => $method === 'credit_account' ? 0 : $this->variant->selling_price,
            'checkout_token' => (string) Str::uuid(),
        ], $overrides);
    }
}
