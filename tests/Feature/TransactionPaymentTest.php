<?php

namespace Tests\Feature;

use App\Models\CreditSale;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\TransactionSettlementService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class TransactionPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected PaymentMethod $cash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::firstOrFail();
        $this->cash = PaymentMethod::where('code', 'cash')->firstOrFail();
    }

    public function test_multiple_payments_calculate_total_paid_and_outstanding(): void
    {
        $transaction = $this->transaction(['total' => 20000, 'payment' => 0, 'change_due' => 0, 'payment_status' => 'unpaid']);
        $service = app(TransactionSettlementService::class);
        $gcash = PaymentMethod::where('code', 'gcash')->firstOrFail();

        $service->recordAppliedPayment($transaction, $this->paymentData(5000, $this->cash, '2026-09-14'));
        $this->assertSame('partially_paid', $transaction->fresh()->payment_status);
        $service->recordAppliedPayment($transaction, $this->paymentData(5000, $gcash, '2026-09-20', 'GCASH-001'));
        $service->recordAppliedPayment($transaction, $this->paymentData(10000, $this->cash, '2026-10-01'));

        $transaction->load('payments.paymentMethod', 'payments.receivedBy');
        $this->assertSame(20000.0, $transaction->totalPaid());
        $this->assertSame(20000.0, $transaction->netTotal());
        $this->assertSame(0.0, $transaction->outstandingBalance());
        $this->assertSame('paid', $transaction->fresh()->payment_status);
        $this->assertSame(['cash', 'gcash', 'cash'], $transaction->payments->map->paymentMethod->pluck('code')->all());
        $this->assertTrue($transaction->payments->every(fn ($payment) => $payment->receivedBy->is($this->user)));
    }

    public function test_outstanding_uses_refund_adjusted_net_total_and_never_goes_negative(): void
    {
        $transaction = $this->transaction(['total' => 1000, 'payment' => 0, 'change_due' => 0, 'payment_status' => 'unpaid']);
        app(TransactionSettlementService::class)->recordAppliedPayment($transaction, $this->paymentData(600));
        Refund::create([
            'refund_no' => 'REF-LEDGER-1', 'transaction_id' => $transaction->id,
            'processed_by' => $this->user->id, 'refund_amount' => 300,
            'refund_method' => 'cash', 'refund_type' => 'partial',
        ]);
        $transaction->refresh();
        $this->assertSame(700.0, $transaction->netTotal());
        $this->assertSame(600.0, $transaction->totalPaid());
        $this->assertSame(100.0, $transaction->outstandingBalance());

        Refund::create([
            'refund_no' => 'REF-LEDGER-2', 'transaction_id' => $transaction->id,
            'processed_by' => $this->user->id, 'refund_amount' => 700,
            'refund_method' => 'cash', 'refund_type' => 'full',
        ]);
        $transaction->refresh();
        $this->assertSame(0.0, $transaction->netTotal());
        $this->assertSame(0.0, $transaction->outstandingBalance());
    }

    public function test_service_rejects_zero_negative_and_excess_payments_atomically(): void
    {
        $transaction = $this->transaction(['total' => 100, 'payment' => 0, 'change_due' => 0, 'payment_status' => 'unpaid']);
        foreach ([0, -1, 100.01] as $amount) {
            try {
                app(TransactionSettlementService::class)->recordAppliedPayment($transaction, $this->paymentData($amount));
                $this->fail('Invalid payment should fail.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('amount', $exception->errors());
            }
        }
        $this->assertSame(0, $transaction->payments()->count());
        $this->assertSame('unpaid', $transaction->fresh()->payment_status);
    }

    public function test_overdue_state_is_retained_until_credit_sale_is_fully_paid(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Overdue Customer']);
        $transaction = $this->transaction(['customer_id' => $customer->id, 'total' => 100, 'payment' => 0, 'change_due' => 0, 'payment_status' => 'overdue']);
        CreditSale::create([
            'transaction_id' => $transaction->id, 'customer_id' => $customer->id,
            'credit_term_days' => 30, 'due_date' => today()->subDay(), 'original_credit_amount' => 100,
        ]);
        $service = app(TransactionSettlementService::class);
        $service->recordAppliedPayment($transaction, $this->paymentData(50));
        $this->assertSame('overdue', $transaction->fresh()->payment_status);
        $service->recordAppliedPayment($transaction, $this->paymentData(50));
        $this->assertSame('paid', $transaction->fresh()->payment_status);
    }

    public function test_backfill_applies_sale_amount_not_cash_tender_and_is_idempotent(): void
    {
        $migration = $this->migration();
        $migration->down();
        $cash = $this->transaction(['total' => 950, 'payment' => 1000, 'change_due' => 50, 'payment_reference' => null]);
        $cardMethod = PaymentMethod::findOrFail(PaymentMethod::idForCode('credit'));
        $card = $this->transaction(['payment_method_id' => $cardMethod->id, 'total' => 700, 'payment' => 700, 'change_due' => 0, 'payment_reference' => 'CARD-1']);
        $migration->up();

        $this->assertSame('950.00', $cash->payments()->sole()->amount);
        $this->assertSame('700.00', $card->payments()->sole()->amount);
        $this->assertSame('CARD-1', $card->payments()->sole()->payment_reference);
        $this->assertSame('Legacy transaction payment backfill', $cash->payments()->sole()->notes);
        $migration->up();
        $this->assertSame(2, TransactionPayment::count());
        $this->assertSame(950.0, $cash->fresh()->totalPaid());
        $this->assertSame(0.0, $cash->fresh()->outstandingBalance());
    }

    public function test_backfill_skips_voided_pending_zero_and_preexisting_ledgers(): void
    {
        $migration = $this->migration();
        $migration->down();
        $voided = $this->transaction(['status' => 'voided']);
        $pending = $this->transaction();
        DB::table('transactions')->where('id', $pending->id)->update(['legacy_payment_status' => 'pending']);
        $zero = $this->transaction(['payment' => 0, 'change_due' => 0]);
        $existing = $this->transaction();
        $migration->up();
        // Simulate an interrupted/custom import before the migration retries its backfill.
        TransactionPayment::where('transaction_id', $existing->id)->delete();
        TransactionPayment::create($this->paymentData(25) + ['transaction_id' => $existing->id]);
        $migration->up();
        $this->assertFalse($voided->payments()->exists());
        $this->assertFalse($pending->payments()->exists());
        $this->assertFalse($zero->payments()->exists());
        $this->assertSame(1, $existing->payments()->count());
        $this->assertSame('25.00', $existing->payments()->sole()->amount);
    }

    public function test_backfill_rollback_is_safe_and_refuses_new_payments(): void
    {
        $migration = $this->migration();
        $migration->down();
        $this->transaction(['total' => 950, 'payment' => 1000, 'change_due' => 50]);
        $migration->up();
        $migration->down();
        $this->assertFalse(Schema::hasTable('transaction_payments'));
        $migration->up();

        $transaction = Transaction::firstOrFail();
        TransactionPayment::where('transaction_id', $transaction->id)->delete();
        app(TransactionSettlementService::class)->recordAppliedPayment($transaction, $this->paymentData(950));
        try {
            $migration->down();
            $this->fail('New ledger data must be preserved.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Cannot roll back transaction payments', $exception->getMessage());
        }
        $this->assertTrue(Schema::hasTable('transaction_payments'));
        $this->assertSame(1, TransactionPayment::count());
    }

    public function test_indexes_foreign_keys_casts_and_inverse_relationships(): void
    {
        foreach (['transaction_payments_transaction_paid_at_index', 'transaction_payments_paid_at_method_index', 'transaction_payments_receiver_paid_at_index'] as $index) {
            $this->assertTrue(Schema::hasIndex('transaction_payments', $index));
        }
        $foreignColumns = collect(Schema::getForeignKeys('transaction_payments'))->pluck('columns');
        foreach ([['transaction_id'], ['payment_method_id'], ['received_by']] as $columns) {
            $this->assertContains($columns, $foreignColumns);
        }
        $transaction = $this->transaction(['total' => 100, 'payment' => 0, 'change_due' => 0, 'payment_status' => 'unpaid']);
        $payment = app(TransactionSettlementService::class)->recordAppliedPayment($transaction, $this->paymentData(100, paidAt: '2026-09-15 12:30:00'))->refresh();
        $this->assertSame('100.00', $payment->amount);
        $this->assertSame('2026-09-15 12:30:00', $payment->paid_at->format('Y-m-d H:i:s'));
        $this->assertTrue($payment->transaction->is($transaction));
        $this->assertTrue($payment->paymentMethod->is($this->cash));
        $this->assertTrue($payment->receivedBy->is($this->user));
        $this->assertTrue($transaction->payments->first()->is($payment));
        $this->assertTrue($this->cash->transactionPayments->first()->is($payment));
        $this->assertTrue($this->user->receivedTransactionPayments->first()->is($payment));
    }

    public function test_database_rejects_missing_ledger_parents(): void
    {
        foreach ([
            ['transaction_id' => 999999], ['payment_method_id' => 999999], ['received_by' => 999999],
        ] as $override) {
            try {
                TransactionPayment::create(array_replace(
                    $this->paymentData(10),
                    ['transaction_id' => $this->transaction()->id],
                    $override
                ));
                $this->fail('Missing parent should fail.');
            } catch (QueryException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_15_000035_create_transaction_payments_table.php');
    }

    private function transaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_replace([
            'receipt_no' => 'PAY-'.Str::random(16), 'checkout_token' => (string) Str::uuid(),
            'date' => '2026-09-15', 'time' => '10:00:00', 'cashier_id' => $this->user->id,
            'subtotal' => 100, 'discount' => 0, 'total' => 100,
            'payment_method_id' => $this->cash->id, 'payment' => 100, 'change_due' => 0,
            'payment_status' => 'paid', 'status' => 'completed',
        ], $overrides));
    }

    private function paymentData(float $amount, ?PaymentMethod $method = null, string $paidAt = '2026-09-15', ?string $reference = null): array
    {
        return [
            'payment_method_id' => ($method ?? $this->cash)->id, 'amount' => $amount,
            'payment_reference' => $reference, 'received_by' => $this->user->id,
            'paid_at' => $paidAt, 'notes' => 'Test payment',
        ];
    }
}

