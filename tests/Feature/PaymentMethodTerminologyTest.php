<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PaymentMethodTerminologyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
    }

    public function test_reference_codes_and_labels_are_unambiguous(): void
    {
        $this->assertSame(['cash', 'gcash', 'credit_account', 'check'], PaymentMethod::defaultCodes());
        $this->assertContains('credit_account', PaymentMethod::defaultCodes());
        $this->assertSame('Credit Card', PaymentMethod::labelFor('credit'));
        $this->assertSame('Credit Sale', PaymentMethod::labelFor('credit_account'));
        $this->assertDatabaseMissing('payment_methods', ['code' => 'credit', 'is_active' => true]);
        $this->assertDatabaseHas('payment_methods', ['code' => 'credit_account', 'name' => 'Credit Sale']);
    }

    public function test_seeder_corrects_labels_without_reactivating_disabled_methods(): void
    {
        PaymentMethod::create(['code' => 'credit', 'name' => 'Credit Card', 'is_active' => true]);
        PaymentMethod::where('code', 'credit_account')->update(['name' => 'Wrong', 'is_active' => false]);
        $this->seed(LookupSeeder::class);

        $this->assertDatabaseHas('payment_methods', ['code' => 'credit', 'name' => 'Credit Card', 'is_active' => false]);
        $this->assertDatabaseHas('payment_methods', ['code' => 'credit_account', 'name' => 'Credit Sale', 'is_active' => false]);
    }

    public function test_credit_account_is_available_in_filters_and_pos_but_not_refund_tenders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('pos.transactions'))
            ->assertOk()
            ->assertDontSee('<option value="credit"', false)
            ->assertDontSee('Credit Card')
            ->assertSee('<option value="credit_account"', false)
            ->assertSee('Credit Sale');

        $this->get(route('pos.index'))
            ->assertOk()
            ->assertDontSee('data-method="credit"', false)
            ->assertDontSee('Credit Card')
            ->assertSee('data-method="credit_account"', false)
            ->assertSee('Credit Sale');

        $transaction = $this->transaction('credit_account');
        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Credit Sale')
            ->assertDontSee('Card Reference:');
        $this->get(route('pos.refund.form', $transaction))
            ->assertOk()
            ->assertDontSee('Credit Card')
            ->assertDontSee('value="credit_account"', false);
    }

    public function test_credit_account_checkout_requires_customer_terms_before_creating_financial_records(): void
    {
        $beforeTransactions = Transaction::count();
        $beforePayments = DB::table('transaction_payments')->count();

        $this->actingAs($this->admin)->post(route('pos.checkout'), [
            'items' => [['variant_id' => 1, 'quantity' => 1, 'unit_price' => 1]],
            'discount' => 0,
            'payment_method' => 'credit_account',
            'payment' => 0,
        ])->assertSessionHasErrors(['customer_id', 'credit_term_days']);

        $this->assertSame($beforeTransactions, Transaction::count());
        $this->assertSame($beforePayments, DB::table('transaction_payments')->count());
    }

    public function test_reports_and_receipts_use_central_labels(): void
    {
        $card = $this->transaction('credit');
        $sale = $this->transaction('credit_account');
        $this->actingAs($this->admin);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Credit Card')
            ->assertSee('Credit Sale');
        $csv = $this->get(route('reports.export'))->streamedContent();
        $this->assertStringContainsString('Credit Card', $csv);
        $this->assertStringContainsString('Credit Sale', $csv);
        $this->get(route('pos.receipt', $card))->assertOk()->assertSee('Credit Card')->assertSee('Card Reference:');
        $this->get(route('pos.receipt', $sale))->assertOk()->assertSee('Credit Sale')->assertDontSee('Card Reference:');
    }

    public function test_migration_preserves_credit_id_and_guarded_rollback_preserves_references(): void
    {
        $creditId = PaymentMethod::idForCode('credit');
        $migration = $this->migration();
        $migration->down();
        $this->assertSame($creditId, PaymentMethod::where('code', 'credit')->value('id'));
        $this->assertDatabaseHas('payment_methods', ['code' => 'credit', 'name' => 'Credit']);
        $this->assertDatabaseMissing('payment_methods', ['code' => 'credit_account']);
        $migration->up();
        $this->assertSame($creditId, PaymentMethod::where('code', 'credit')->value('id'));

        $this->transaction('credit_account');
        try {
            $migration->down();
            $this->fail('Referenced method must not be removed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('financial records reference it', $exception->getMessage());
        }
        $this->assertDatabaseHas('payment_methods', ['code' => 'credit_account', 'name' => 'Credit Sale']);
    }

    public function test_retired_methods_are_hidden_even_before_migration_and_history_is_preserved(): void
    {
        $transaction = $this->transaction('credit');
        $methodId = $transaction->payment_method_id;
        PaymentMethod::whereKey($methodId)->update(['is_active' => true]);
        PaymentMethod::create(['code' => 'other', 'name' => 'Other', 'is_active' => true]);
        $references = app(\App\Services\ReferenceDataService::class);
        $references->forgetPaymentMethods();
        $this->assertEqualsCanonicalizing(PaymentMethod::defaultCodes(), $references->paymentMethods()->pluck('code')->all());

        $migration = require database_path('migrations/2026_09_25_000038_restrict_available_payment_methods.php');
        $migration->up();
        $migration->up();
        $this->assertSame($methodId, $transaction->fresh()->payment_method_id);
        $this->assertSame('credit', $transaction->fresh()->payment_method);
        $this->assertDatabaseHas('payment_methods', ['id' => $methodId, 'is_active' => false]);
        $this->assertDatabaseHas('payment_methods', ['code' => 'other', 'is_active' => false]);
    }

    public function test_supplier_forms_only_offer_supported_methods(): void
    {
        $this->actingAs($this->admin)->get(route('suppliers.create'))
            ->assertOk()
            ->assertSee('value="credit_account"', false)
            ->assertDontSee('value="credit"', false)
            ->assertDontSee('Credit Card');
        foreach (['credit', 'online_banking', 'store_credit'] as $method) {
            $this->post(route('suppliers.store'), ['name' => 'Supplier', 'payment_method' => $method])
                ->assertSessionHasErrors('payment_method');
        }
    }

    private function transaction(string $method): Transaction
    {
        return Transaction::create([
            'receipt_no' => 'TERM-'.Str::random(14),
            'date' => today(),
            'time' => now()->format('H:i:s'),
            'cashier_id' => $this->admin->id,
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'payment_method' => $method,
            'payment_reference' => $method === 'credit' ? 'CARD-REFERENCE' : null,
            'payment_card_type' => $method === 'credit' ? 'credit' : null,
            'payment_card_last4' => $method === 'credit' ? '1234' : null,
            'payment' => $method === 'credit_account' ? 0 : 100,
            'change_due' => 0,
            'payment_status' => $method === 'credit_account' ? 'unpaid' : 'paid',
            'status' => 'completed',
        ]);
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_16_000036_add_credit_account_payment_method.php');
    }
}

