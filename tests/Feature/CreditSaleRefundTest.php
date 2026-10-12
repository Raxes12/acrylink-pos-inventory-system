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
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\TransactionSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreditSaleRefundTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
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
        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->variant = ProductVariant::with('product', 'inventoryStock')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_unpaid_credit_return_reduces_receivable_without_customer_reimbursement(): void
    {
        [$transaction, $item] = $this->creditSale(0);
        $stockBefore = (int) $this->variant->inventoryStock->quantity_on_hand;

        $this->post(route('pos.refund.process', $transaction), $this->payload($item, 2, null))
            ->assertRedirect(route('pos.receipt', $transaction));

        $refund = Refund::firstOrFail();
        $this->assertSame('2000.00', $refund->refund_amount);
        $this->assertSame('2000.00', $refund->receivable_adjustment_amount);
        $this->assertSame('0.00', $refund->reimbursement_amount);
        $this->assertNull($refund->getRawOriginal('refund_method_id'));
        $this->assertSame(8000.0, $transaction->fresh()->outstandingBalance());
        $this->assertSame('unpaid', $transaction->fresh()->payment_status);
        $this->assertSame('partial_refund', $transaction->fresh()->status);
        $this->assertSame($stockBefore + 2, (int) $this->variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_partially_paid_credit_return_reduces_outstanding_after_applied_payments(): void
    {
        [$transaction, $item] = $this->creditSale(4000);

        $this->post(route('pos.refund.process', $transaction), $this->payload($item, 2, null))
            ->assertRedirect(route('pos.receipt', $transaction));

        $refund = Refund::firstOrFail();
        $this->assertSame('2000.00', $refund->receivable_adjustment_amount);
        $this->assertSame('0.00', $refund->reimbursement_amount);
        $this->assertSame(4000.0, $transaction->fresh()->outstandingBalance());
        $this->assertSame('partially_paid', $transaction->fresh()->payment_status);
    }

    public function test_overpaid_after_refund_records_only_incremental_customer_reimbursement(): void
    {
        [$transaction, $item] = $this->creditSale(9000);

        $this->post(route('pos.refund.process', $transaction), $this->payload($item, 3, 'cash'))
            ->assertRedirect(route('pos.receipt', $transaction));

        $first = Refund::firstOrFail();
        $this->assertSame('1000.00', $first->receivable_adjustment_amount);
        $this->assertSame('2000.00', $first->reimbursement_amount);
        $this->assertSame('cash', $first->refund_method);
        $this->assertSame(7000.0, $transaction->fresh()->netTotal());
        $this->assertSame(0.0, $transaction->fresh()->outstandingBalance());
        $this->assertSame('paid', $transaction->fresh()->payment_status);

        $this->post(route('pos.refund.process', $transaction), $this->payload($item, 1, 'gcash'))
            ->assertRedirect(route('pos.receipt', $transaction));
        $second = Refund::latest('id')->firstOrFail();
        $this->assertSame('0.00', $second->receivable_adjustment_amount);
        $this->assertSame('1000.00', $second->reimbursement_amount);
        $this->assertSame(3000.0, (float) Refund::sum('reimbursement_amount'));

        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Receivable reduction:')
            ->assertSee('Customer reimbursement:')
            ->assertSee('GCash');
    }

    public function test_reimbursement_method_is_required_only_when_money_is_returned(): void
    {
        [$transaction, $item] = $this->creditSale(9000);

        $this->post(route('pos.refund.process', $transaction), $this->payload($item, 3, null))
            ->assertSessionHasErrors('refund_method');

        $this->assertDatabaseCount('refunds', 0);
        $this->assertSame(10000.0, $transaction->fresh()->netTotal());
    }

    public function test_refund_allocation_columns_are_available(): void
    {
        $this->assertTrue(Schema::hasColumns('refunds', [
            'receivable_adjustment_amount', 'reimbursement_amount',
        ]));
    }

    /** @return array{Transaction, TransactionItem} */
    private function creditSale(float $paid): array
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Refund Credit '.Str::random(5),
            'credit_enabled' => true,
            'credit_limit' => 20000,
        ]);
        $transaction = Transaction::create([
            'receipt_no' => 'RCP-CREDIT-REF-'.Str::random(8),
            'checkout_token' => (string) Str::uuid(),
            'date' => today(),
            'time' => now()->toTimeString(),
            'cashier_id' => $this->admin->id,
            'customer_id' => $customer->id,
            'subtotal' => 10000,
            'discount' => 0,
            'total' => 10000,
            'payment_method' => 'credit_account',
            'payment' => $paid,
            'change_due' => 0,
            'payment_status' => $paid > 0 ? 'partially_paid' : 'unpaid',
            'status' => 'completed',
        ]);
        CreditSale::create([
            'transaction_id' => $transaction->id,
            'customer_id' => $customer->id,
            'credit_term_days' => 30,
            'due_date' => today()->addDays(30),
            'original_credit_amount' => 10000,
        ]);
        $item = TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->variant->product_id,
            'variant_id' => $this->variant->id,
            'product_name' => $this->variant->product->name,
            'product_name_snapshot' => $this->variant->product->name,
            'quantity' => 10,
            'unit_price' => 1000,
            'subtotal' => 10000,
        ]);
        if ($paid > 0) {
            app(TransactionSettlementService::class)->recordAppliedPayment($transaction, [
                'payment_method_id' => PaymentMethod::where('code', 'cash')->value('id'),
                'amount' => $paid,
                'received_by' => $this->admin->id,
                'paid_at' => now(),
            ]);
        }

        return [$transaction->fresh(), $item];
    }

    private function payload(TransactionItem $item, int $quantity, ?string $method): array
    {
        return [
            'reason' => 'Credit sale return test',
            'refund_method' => $method,
            'items' => [[
                'transaction_item_id' => $item->id,
                'quantity' => $quantity,
                'restock' => 1,
            ]],
        ];
    }
}

