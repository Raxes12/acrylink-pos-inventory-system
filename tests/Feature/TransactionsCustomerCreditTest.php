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
use App\Models\TransactionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionsCustomerCreditTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-19 11:00:00');
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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_transactions_page_displays_compact_customer_credit_and_independent_status_details(): void
    {
        $this->normalSale('TX-WALK-IN');
        $partial = $this->creditSale('TX-CREDIT-PARTIAL', 'Fleet Account', today()->addDays(10));
        $this->pay($partial, 250);
        $this->creditSale('TX-CREDIT-UNPAID', 'Unpaid Account', today()->addDays(20));
        $this->creditSale('TX-CREDIT-OVERDUE', 'Overdue Account', today()->subDay());

        $this->get(route('pos.transactions'))
            ->assertOk()
            ->assertSee('Customer')
            ->assertSee('Payment')
            ->assertSee('Amounts')
            ->assertSee('Credit')
            ->assertSee('Transaction Status')
            ->assertSee('Walk-in')
            ->assertSee('Balance:')
            ->assertSee('750.00')
            ->assertSee('Sep 29, 2026')
            ->assertSee('Paid')
            ->assertSee('Unpaid')
            ->assertSee('Partially paid')
            ->assertSee('Overdue')
            ->assertSee('Completed')
            ->assertSee(route('pos.credit-payments.create', $partial), false)
            ->assertViewHas('transactions', fn ($transactions) => $transactions->every(fn (Transaction $transaction) =>
                $transaction->relationLoaded('customer')
                && $transaction->relationLoaded('cashier')
                && $transaction->relationLoaded('creditSale')
                && $transaction->relationLoaded('payments')
                && $transaction->relationLoaded('refunds')
                && $transaction->relationLoaded('paymentMethod')
            ));
    }

    public function test_transaction_search_matches_customer_name_and_code(): void
    {
        $match = $this->creditSale('TX-CUSTOMER-MATCH', 'North Fleet', today()->addMonth());
        $other = $this->creditSale('TX-CUSTOMER-OTHER', 'South Garage', today()->addMonth());
        $customerCode = $match->customer->customer_code;

        $this->get(route('pos.transactions', ['search' => 'North Fleet']))
            ->assertOk()
            ->assertSee($match->receipt_no)
            ->assertDontSee($other->receipt_no);

        $this->get(route('pos.transactions', ['search' => $customerCode]))
            ->assertOk()
            ->assertSee($match->receipt_no)
            ->assertDontSee($other->receipt_no);
    }

    private function normalSale(string $receipt): Transaction
    {
        $transaction = $this->transaction($receipt, 100, PaymentMethod::where('code', 'cash')->firstOrFail());
        $this->pay($transaction, 100);

        return $transaction;
    }

    private function creditSale(string $receipt, string $customerName, Carbon $dueDate): Transaction
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => $customerName,
            'credit_enabled' => true,
            'credit_limit' => 10000,
        ]);
        $transaction = $this->transaction(
            $receipt,
            1000,
            PaymentMethod::where('code', 'credit_account')->firstOrFail(),
            $customer
        );
        CreditSale::create([
            'transaction_id' => $transaction->id,
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'due_date' => $dueDate,
            'original_credit_amount' => 1000,
        ]);

        return $transaction->fresh('customer');
    }

    private function transaction(string $receipt, float $total, PaymentMethod $method, ?Customer $customer = null): Transaction
    {
        return Transaction::create([
            'receipt_no' => $receipt,
            'checkout_token' => (string) Str::uuid(),
            'date' => today(),
            'time' => now()->toTimeString(),
            'cashier_id' => $this->cashier->id,
            'customer_id' => $customer?->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method_id' => $method->id,
            'payment' => $method->code === 'cash' ? $total : 0,
            'change_due' => 0,
            'payment_status' => $method->code === 'credit_account' ? 'unpaid' : 'paid',
            'status' => 'completed',
        ]);
    }

    private function pay(Transaction $transaction, float $amount): TransactionPayment
    {
        return TransactionPayment::create([
            'transaction_id' => $transaction->id,
            'payment_method_id' => PaymentMethod::where('code', 'cash')->value('id'),
            'amount' => $amount,
            'received_by' => $this->cashier->id,
            'paid_at' => now(),
        ]);
    }
}

