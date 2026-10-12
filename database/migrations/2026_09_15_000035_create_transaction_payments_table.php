<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BACKFILL_NOTE = 'Legacy transaction payment backfill';

    public function up(): void
    {
        if (! Schema::hasTable('transaction_payments')) {
            Schema::create('transaction_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->restrictOnDelete();
                $table->foreignId('payment_method_id')->constrained('payment_methods')->restrictOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('payment_reference', 100)->nullable();
                $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
                $table->dateTime('paid_at');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['transaction_id', 'paid_at'], 'transaction_payments_transaction_paid_at_index');
                $table->index(['paid_at', 'payment_method_id'], 'transaction_payments_paid_at_method_index');
                $table->index(['received_by', 'paid_at'], 'transaction_payments_receiver_paid_at_index');
            });
        }

        $this->backfillLegacyPayments();
    }

    public function down(): void
    {
        if (! Schema::hasTable('transaction_payments')) {
            return;
        }

        if (DB::table('transaction_payments')->where(function ($query) {
            $query->whereNull('notes')->orWhere('notes', '!=', self::BACKFILL_NOTE);
        })->exists()) {
            throw new RuntimeException('Cannot roll back transaction payments after new ledger payments have been recorded. Preserve and reconcile those records before downgrading.');
        }

        Schema::drop('transaction_payments');
    }

    private function backfillLegacyPayments(): void
    {
        DB::table('transactions')
            ->select([
                'id', 'payment_method_id', 'cashier_id', 'date', 'time', 'total',
                'payment', 'change_due', 'payment_reference', 'status', 'legacy_payment_status',
                'created_at', 'updated_at',
            ])
            ->where('status', '!=', 'voided')
            ->where('legacy_payment_status', 'confirmed')
            ->whereNotNull('payment_method_id')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('transaction_payments')
                ->whereColumn('transaction_payments.transaction_id', 'transactions.id'))
            ->orderBy('id')
            ->chunkById(500, function ($transactions) {
                $rows = [];

                foreach ($transactions as $transaction) {
                    $appliedAmount = min(
                        max(0, (float) $transaction->total),
                        max(0, (float) $transaction->payment - (float) $transaction->change_due)
                    );

                    if ($appliedAmount <= 0) {
                        continue;
                    }

                    $rows[] = [
                        'transaction_id' => $transaction->id,
                        'payment_method_id' => $transaction->payment_method_id,
                        'amount' => number_format($appliedAmount, 2, '.', ''),
                        'payment_reference' => $transaction->payment_reference,
                        'received_by' => $transaction->cashier_id,
                        'paid_at' => Carbon::parse($transaction->date)->toDateString().' '
                            .Carbon::parse($transaction->time)->format('H:i:s'),
                        'notes' => self::BACKFILL_NOTE,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                    ];
                }

                if ($rows !== []) {
                    DB::table('transaction_payments')->insert($rows);
                }
            }, 'id');
    }
};
