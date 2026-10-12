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
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreditPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

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
        $this->cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->actingAs($this->cashier);
    }

    public function test_record_payment_page_displays_sale_totals_status_and_history(): void
    {
        $transaction = $this->creditTransaction(200);
        app(TransactionSettlementService::class)->recordAppliedPayment($transaction, $this->paymentData(50));

        $this->get(route('pos.credit-payments.create', $transaction))
            ->assertOk()
            ->assertSee($transaction->customer->customer_name)
            ->assertSee($transaction->receipt_no)
            ->assertSee('Due Date')
            ->assertSee('Net Total')
            ->assertSee('Total Paid')
            ->assertSee('Outstanding')
            ->assertSee('Partially paid')
            ->assertSee('Payment History')
            ->assertSee('50.00')
            ->assertSee('Credit Card')
            ->assertDontSee('Online Banking')
            ->assertDontSee('<option value="credit"', false)
            ->assertDontSee('value="credit_account"', false);
    }

    public function test_credit_payment_and_receipt_pages_show_overdue_after_date_rollover_without_mutation(): void
    {
        try {
            Carbon::setTestNow('2026-09-19 12:00:00');
            $transaction = $this->creditTransaction(100);
            $transaction->creditSale->update(['due_date' => '2026-09-20']);
            $this->assertSame('unpaid', $transaction->fresh()->payment_status);

            Carbon::setTestNow('2026-09-21 00:00:00');
            $this->get(route('pos.credit-payments.create', $transaction))->assertOk()->assertSee('Overdue');
            $this->get(route('pos.receipt', $transaction))->assertOk()->assertSee('Overdue');
            $this->get(route('pos.transactions'))->assertOk()->assertSee('Overdue');
            $this->assertSame('unpaid', $transaction->fresh()->payment_status);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_payment_uses_server_identity_and_timestamp_then_redirects_to_acknowledgment(): void
    {
        Carbon::setTestNow('2026-09-19 14:30:45');
        $transaction = $this->creditTransaction(200);
        $transactionCount = Transaction::count();
        $movementCount = DB::table('inventory_movements')->count();

        $response = $this->post(route('pos.credit-payments.store', $transaction), [
            'amount' => 75,
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-COLLECT-1',
            'notes' => 'First installment',
            'paid_at' => '2000-01-01 00:00:00',
            'received_by' => User::where('role', User::ROLE_ADMIN)->value('id'),
        ]);

        $payment = $transaction->payments()->latest('id')->firstOrFail();
        $response->assertRedirect(route('pos.credit-payments.show', $payment));
        $this->assertSame('75.00', $payment->amount);
        $this->assertSame('gcash', $payment->paymentMethod->code);
        $this->assertSame('GCASH-COLLECT-1', $payment->payment_reference);
        $this->assertSame($this->cashier->id, $payment->received_by);
        $this->assertSame('2026-09-19 14:30:45', $payment->paid_at->format('Y-m-d H:i:s'));
        $this->assertSame('partially_paid', $transaction->fresh()->payment_status);
        $this->assertSame($transactionCount, Transaction::count());
        $this->assertSame($movementCount, DB::table('inventory_movements')->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREDIT_PAYMENT',
            'affected_table' => 'transaction_payments',
            'affected_record_id' => $payment->id,
        ]);

        $this->get(route('pos.credit-payments.show', $payment))
            ->assertOk()
            ->assertSee('Payment Acknowledgment')
            ->assertSee('Payment ID')
            ->assertSee('#'.$payment->id)
            ->assertSee('Payment Reference')
            ->assertSee('Original Receipt Number')
            ->assertSee($transaction->receipt_no)
            ->assertSee($transaction->customer->customer_name)
            ->assertSee('Amount')
            ->assertSee('Payment Method')
            ->assertSee('Paid At')
            ->assertSee('Received By')
            ->assertSee('Remaining Balance')
            ->assertSee('GCash')
            ->assertSee('GCASH-COLLECT-1')
            ->assertSee('First installment')
            ->assertSee($this->cashier->name);
    }

    public function test_payment_validation_rejects_invalid_amount_method_reference_and_disabled_method(): void
    {
        $transaction = $this->creditTransaction(100);

        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 0, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('amount');
        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 100.01, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('amount');
        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 10, 'payment_method' => 'credit_account'])
            ->assertSessionHasErrors('payment_method');
        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 10, 'payment_method' => 'credit'])
            ->assertSessionHasErrors('payment_method');
        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 10, 'payment_method' => 'gcash'])
            ->assertSessionHasErrors('payment_reference');
        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 10, 'payment_method' => 'check'])
            ->assertSessionHasErrors(['check_number', 'check_bank', 'check_date']);

        PaymentMethod::where('code', 'online_banking')->update(['is_active' => false]);
        $this->post(route('pos.credit-payments.store', $transaction), [
            'amount' => 10, 'payment_method' => 'online_banking', 'payment_reference' => 'BANK-1',
        ])->assertSessionHasErrors('payment_method');

        $this->assertSame(0, $transaction->payments()->count());
    }

    public function test_check_settlement_stores_and_displays_check_details(): void
    {
        $transaction = $this->creditTransaction(200);

        $response = $this->post(route('pos.credit-payments.store', $transaction), [
            'amount' => 75,
            'payment_method' => 'check',
            'check_number' => 'CHK-AR-1001',
            'check_bank' => 'BDO',
            'check_date' => '2026-09-28',
            'notes' => 'Customer installment',
        ]);

        $payment = $transaction->payments()->latest('id')->firstOrFail();
        $response->assertRedirect(route('pos.credit-payments.show', $payment));
        $this->assertSame('check', $payment->paymentMethod->code);
        $this->assertSame('CHK-AR-1001', $payment->check_number);
        $this->assertSame('BDO', $payment->check_bank);
        $this->assertSame('2026-09-28', $payment->check_date->toDateString());
        $this->assertNull($payment->payment_reference);

        $this->get(route('pos.credit-payments.show', $payment))
            ->assertOk()
            ->assertSee('Check Number')
            ->assertSee('CHK-AR-1001')
            ->assertSee('BDO')
            ->assertSee('Sep 28, 2026');
    }

    public function test_locked_settlement_prevents_sequential_stale_overpayment(): void
    {
        $transaction = $this->creditTransaction(100);
        $this->post(route('pos.credit-payments.store', $transaction), [
            'amount' => 60, 'payment_method' => 'cash',
        ])->assertRedirect();

        $this->post(route('pos.credit-payments.store', $transaction), [
            'amount' => 50, 'payment_method' => 'cash',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(1, $transaction->payments()->count());
        $this->assertSame(60.0, $transaction->fresh()->totalPaid());
        $this->assertSame(40.0, $transaction->fresh()->outstandingBalance());
    }

    public function test_fully_paid_and_voided_credit_sales_reject_new_payments(): void
    {
        $paid = $this->creditTransaction(100);
        app(TransactionSettlementService::class)->recordAppliedPayment($paid, $this->paymentData(100));
        $this->post(route('pos.credit-payments.store', $paid), ['amount' => 1, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('transaction');

        $voided = $this->creditTransaction(100);
        $voided->update(['status' => 'voided']);
        $this->post(route('pos.credit-payments.store', $voided), ['amount' => 1, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('transaction');
    }

    public function test_non_credit_sale_is_not_a_collectible_account(): void
    {
        $transaction = $this->transaction(100, PaymentMethod::where('code', 'cash')->firstOrFail());
        $this->get(route('pos.credit-payments.create', $transaction))->assertNotFound();
        $this->post(route('pos.credit-payments.store', $transaction), ['amount' => 10, 'payment_method' => 'cash'])
            ->assertNotFound();
    }

    public function test_credit_payment_routes_use_existing_sales_rbac(): void
    {
        $transaction = $this->creditTransaction(100);
        $this->actingAs(User::where('role', User::ROLE_INVENTORY)->firstOrFail());
        $this->get(route('pos.credit-payments.create', $transaction))->assertForbidden();

        auth()->logout();
        $this->get(route('pos.credit-payments.create', $transaction))->assertRedirect(route('login'));
    }

    public function test_cashier_cannot_view_or_record_payments_for_another_cashiers_sale(): void
    {
        $owner = User::create([
            'username' => 'credit_owner',
            'name' => 'Credit Owner',
            'email' => 'credit-owner@example.test',
            'password' => $this->cashier->password,
            'role' => User::ROLE_CASHIER,
            'role_id' => $this->cashier->role_id,
            'status' => 'active',
        ]);
        $transaction = $this->creditTransaction(200);
        $transaction->update(['cashier_id' => $owner->id]);
        $payment = app(TransactionSettlementService::class)->recordAppliedPayment($transaction, $this->paymentData(50));

        $this->actingAs($this->cashier);
        $this->get(route('pos.credit-payments.create', $transaction))->assertForbidden();
        $this->post(route('pos.credit-payments.store', $transaction), [
            'amount' => 25,
            'payment_method' => 'cash',
        ])->assertForbidden();
        $this->get(route('pos.credit-payments.show', $payment))->assertForbidden();
        $this->assertSame(1, $transaction->payments()->count());

        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $this->get(route('pos.credit-payments.create', $transaction))->assertOk();
        $this->get(route('pos.credit-payments.show', $payment))->assertOk();
    }

    private function creditTransaction(float $total): Transaction
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Collection Customer '.Str::random(6),
            'credit_enabled' => true,
            'credit_limit' => 10000,
        ]);
        $transaction = $this->transaction($total, PaymentMethod::where('code', 'credit_account')->firstOrFail(), [
            'customer_id' => $customer->id,
        ]);
        CreditSale::create([
            'transaction_id' => $transaction->id,
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'due_date' => today()->addDays(30),
            'original_credit_amount' => $total,
        ]);

        return $transaction->fresh(['customer', 'creditSale']);
    }

    private function transaction(float $total, PaymentMethod $method, array $overrides = []): Transaction
    {
        return Transaction::create(array_replace([
            'receipt_no' => 'COLLECT-'.Str::random(12),
            'checkout_token' => (string) Str::uuid(),
            'date' => today(),
            'time' => now()->toTimeString(),
            'cashier_id' => $this->cashier->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method_id' => $method->id,
            'payment' => 0,
            'change_due' => 0,
            'payment_status' => 'unpaid',
            'status' => 'completed',
        ], $overrides));
    }

    private function paymentData(float $amount): array
    {
        return [
            'payment_method_id' => PaymentMethod::idForCode('credit'),
            'amount' => $amount,
            'payment_reference' => 'CARD-HISTORY-1',
            'received_by' => $this->cashier->id,
            'paid_at' => now(),
            'notes' => 'Existing payment',
        ];
    }
}
