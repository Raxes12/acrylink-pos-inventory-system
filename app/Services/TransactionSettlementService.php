<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionSettlementService
{
    public function totalPaid(Transaction $transaction): float
    {
        $amount = $transaction->relationLoaded('payments')
            ? $transaction->payments->sum('amount')
            : $transaction->payments()->sum('amount');

        return $this->fromCents($this->toCents($amount));
    }

    public function netTotal(Transaction $transaction): float
    {
        if ($transaction->status === 'voided') {
            return 0.0;
        }

        return $this->fromCents(max(
            0,
            $this->toCents($transaction->total) - $this->toCents($transaction->totalRefunded())
        ));
    }

    public function outstandingBalance(Transaction $transaction): float
    {
        return $this->fromCents(max(
            0,
            $this->toCents($this->netTotal($transaction)) - $this->toCents($this->totalPaid($transaction))
        ));
    }

    /**
     * Allocate a return between debt reduction and money actually returned.
     * The caller must hold a lock on the transaction row while using this result.
     *
     * @return array{receivable_adjustment: float, reimbursement: float}
     */
    public function refundAllocation(Transaction $transaction, mixed $refundAmount): array
    {
        $refundCents = max(0, $this->toCents($refundAmount));
        $creditSale = $transaction->relationLoaded('creditSale')
            ? $transaction->creditSale
            : $transaction->creditSale()->first();

        if (! $creditSale) {
            return [
                'receivable_adjustment' => 0.0,
                'reimbursement' => $this->fromCents($refundCents),
            ];
        }

        $paidCents = $this->toCents($this->totalPaid($transaction));
        $alreadyRefundedCents = $this->toCents($transaction->totalRefunded());
        $netAfterCents = max(0, $this->toCents($transaction->total) - $alreadyRefundedCents - $refundCents);
        $cumulativeReimbursementCents = max(0, $paidCents - $netAfterCents);
        $alreadyReimbursed = $transaction->relationLoaded('refunds')
            ? $transaction->refunds->sum('reimbursement_amount')
            : $transaction->refunds()->sum('reimbursement_amount');
        $reimbursementCents = min(
            $refundCents,
            max(0, $cumulativeReimbursementCents - $this->toCents($alreadyReimbursed))
        );

        return [
            'receivable_adjustment' => $this->fromCents($refundCents - $reimbursementCents),
            'reimbursement' => $this->fromCents($reimbursementCents),
        ];
    }

    public function recordAppliedPayment(Transaction $transaction, array $attributes): TransactionPayment
    {
        return DB::transaction(function () use ($transaction, $attributes) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $amount = $this->fromCents($this->toCents($attributes['amount'] ?? 0));
            $outstanding = $this->outstandingBalance($locked);

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'The applied payment amount must be greater than zero.']);
            }
            if ($this->toCents($amount) > $this->toCents($outstanding)) {
                throw ValidationException::withMessages(['amount' => 'The applied payment cannot exceed the outstanding balance.']);
            }

            $payment = $locked->payments()->create(array_merge($attributes, ['amount' => $amount]));
            $locked->unsetRelation('payments');
            $this->synchronizePaymentStatus($locked);
            $transaction->payment_status = $locked->payment_status;
            $transaction->unsetRelation('payments');

            return $payment;
        }, 3);
    }

    public function synchronizePaymentStatus(Transaction $transaction): void
    {
        $transaction->unsetRelation('payments');
        $transaction->unsetRelation('refunds');
        $transaction->unsetRelation('creditSale');
        $status = $this->paymentStatus($transaction);

        if ($transaction->payment_status !== $status) {
            $transaction->forceFill(['payment_status' => $status])->saveQuietly();
        }
    }

    public function paymentStatus(Transaction $transaction, ?CarbonInterface $businessDate = null): string
    {
        $creditSale = $transaction->relationLoaded('creditSale')
            ? $transaction->creditSale
            : $transaction->creditSale()->first();

        return $this->paymentStatusForValues(
            $transaction->status,
            $this->outstandingBalance($transaction),
            $this->totalPaid($transaction),
            $creditSale?->due_date,
            $businessDate,
        );
    }

    public function paymentStatusForValues(
        string $transactionStatus,
        mixed $outstandingBalance,
        mixed $totalPaid,
        ?CarbonInterface $dueDate,
        ?CarbonInterface $businessDate = null,
    ): string {
        if ($transactionStatus === 'voided' || $this->toCents($outstandingBalance) <= 0) {
            return 'paid';
        }

        if ($dueDate !== null && $dueDate->toDateString() < ($businessDate ?? today())->toDateString()) {
            return 'overdue';
        }

        return $this->toCents($totalPaid) > 0
            ? 'partially_paid'
            : 'unpaid';
    }

    private function toCents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function fromCents(int $amount): float
    {
        return round($amount / 100, 2);
    }
}
