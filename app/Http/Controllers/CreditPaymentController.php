<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Services\TransactionSettlementService;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreditPaymentController extends Controller
{
    private const SETTLEMENT_METHODS = ['cash', 'gcash', 'check'];

    public function create(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);
        $transaction->load([
            'customer', 'creditSale', 'payments.paymentMethod', 'payments.receivedBy', 'refunds',
        ]);
        $this->assertCollectibleCreditSale($transaction);

        $paymentMethods = PaymentMethod::query()
            ->whereIn('code', self::SETTLEMENT_METHODS)
            ->where('is_active', true)
            ->orderByRaw("CASE code WHEN 'cash' THEN 1 WHEN 'gcash' THEN 2 WHEN 'check' THEN 3 ELSE 4 END")
            ->get(['id', 'code', 'name']);

        return view('pos.credit-payments.create', compact('transaction', 'paymentMethods'));
    }

    public function store(Request $request, Transaction $transaction, TransactionSettlementService $settlement)
    {
        Gate::authorize('view', $transaction);
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0|max:'.InputLimits::DATABASE_MONEY_MAX,
            'payment_method' => 'required|string|in:'.implode(',', self::SETTLEMENT_METHODS),
            'payment_reference' => 'nullable|required_if:payment_method,gcash|string|max:100',
            'check_number' => 'nullable|required_if:payment_method,check|string|max:100',
            'check_bank' => 'nullable|required_if:payment_method,check|string|max:100',
            'check_date' => 'nullable|required_if:payment_method,check|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $paymentId = DB::transaction(function () use ($transaction, $validated, $settlement) {
            $locked = Transaction::query()
                ->with(['creditSale', 'payments', 'refunds'])
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();
            Gate::authorize('view', $locked);
            $this->assertCollectibleCreditSale($locked);

            $method = PaymentMethod::query()
                ->where('code', $validated['payment_method'])
                ->where('is_active', true)
                ->first();
            if (! $method || ! in_array($method->code, self::SETTLEMENT_METHODS, true)) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Select an enabled settlement payment method.',
                ]);
            }
            if ($method->code === 'gcash' && blank($validated['payment_reference'] ?? null)) {
                throw ValidationException::withMessages([
                    'payment_reference' => 'Enter the GCash reference number.',
                ]);
            }

            $outstandingBefore = $settlement->outstandingBalance($locked);
            $payment = $settlement->recordAppliedPayment($locked, [
                'payment_method_id' => $method->id,
                'amount' => $validated['amount'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'check_number' => $method->code === 'check' ? $validated['check_number'] : null,
                'check_bank' => $method->code === 'check' ? $validated['check_bank'] : null,
                'check_date' => $method->code === 'check' ? $validated['check_date'] : null,
                'received_by' => auth()->id(),
                'paid_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);
            $locked->refresh();
            $outstandingAfter = $settlement->outstandingBalance($locked);

            AuditLog::record('CREDIT_PAYMENT', "Recorded credit payment for {$locked->receipt_no}: PHP ".number_format((float) $payment->amount, 2).' via '.$method->name, null, [
                'affected_table' => 'transaction_payments',
                'affected_record_id' => $payment->id,
                'old_values' => [
                    'payment_status' => $transaction->payment_status,
                    'outstanding_balance' => $outstandingBefore,
                ],
                'new_values' => [
                    'transaction_id' => $locked->id,
                    'customer_id' => $locked->customer_id,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $method->code,
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'check_number' => $method->code === 'check' ? $validated['check_number'] : null,
                    'check_bank' => $method->code === 'check' ? $validated['check_bank'] : null,
                    'check_date' => $method->code === 'check' ? $validated['check_date'] : null,
                    'received_by' => $payment->received_by,
                    'paid_at' => $payment->paid_at?->toDateTimeString(),
                    'payment_status' => $locked->payment_status,
                    'outstanding_balance' => $outstandingAfter,
                ],
            ]);

            return $payment->id;
        }, 3);

        return redirect()->route('pos.credit-payments.show', $paymentId)
            ->with('success', 'Credit payment recorded successfully.');
    }

    public function show(TransactionPayment $transactionPayment)
    {
        $transactionPayment->load([
            'paymentMethod', 'receivedBy',
            'transaction.customer', 'transaction.creditSale', 'transaction.payments', 'transaction.refunds',
        ]);
        abort_unless($transactionPayment->transaction?->creditSale, 404);
        Gate::authorize('view', $transactionPayment->transaction);

        return view('pos.credit-payments.show', compact('transactionPayment'));
    }

    private function assertCollectibleCreditSale(Transaction $transaction): void
    {
        abort_unless($transaction->creditSale, 404);
        if ($transaction->status === 'voided') {
            throw ValidationException::withMessages(['transaction' => 'A voided Credit Sale cannot receive payments.']);
        }
        if ($transaction->outstandingBalance() <= 0) {
            throw ValidationException::withMessages(['transaction' => 'This Credit Sale is already fully settled.']);
        }
    }
}
