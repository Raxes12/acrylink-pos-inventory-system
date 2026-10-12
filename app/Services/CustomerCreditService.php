<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class CustomerCreditService
{
    /**
     * @param  Collection<int, Customer>  $customers
     * @return array<int, array{credit_limit: float, current_outstanding: float, available_credit: float}>
     */
    public function summaries(Collection $customers): array
    {
        if ($customers->isEmpty()) {
            return [];
        }

        $outstandingByCustomer = Transaction::query()
            ->select(['id', 'customer_id', 'total', 'status'])
            ->whereIn('customer_id', $customers->pluck('id')->all())
            ->whereHas('creditSale')
            ->withSum('refunds', 'refund_amount')
            ->withSum('payments', 'amount')
            ->get()
            ->groupBy('customer_id')
            ->map(fn (Collection $transactions) => round((float) $transactions->sum(
                fn (Transaction $transaction) => $this->transactionOutstanding($transaction)
            ), 2));

        return $customers->mapWithKeys(function (Customer $customer) use ($outstandingByCustomer) {
            $creditLimit = round((float) $customer->credit_limit, 2);
            $outstanding = round((float) ($outstandingByCustomer[$customer->id] ?? 0), 2);

            return [$customer->id => [
                'credit_limit' => $creditLimit,
                'current_outstanding' => $outstanding,
                'available_credit' => round(max(0, $creditLimit - $outstanding), 2),
            ]];
        })->all();
    }

    /** @return array{credit_limit: float, current_outstanding: float, available_credit: float} */
    public function summary(Customer $customer): array
    {
        return $this->summaries(new Collection([$customer]))[$customer->id];
    }

    /**
     * The caller must lock the customer row before using this result to approve
     * a checkout. That lock serializes competing credit sales for one account.
     *
     * @return array{credit_limit: float, current_outstanding: float, available_credit: float, requested_credit: float, projected_outstanding: float, exceeds_limit: bool}
     */
    public function assessment(Customer $customer, float $requestedCredit): array
    {
        $summary = $this->summary($customer);
        $limitCents = $this->toCents($summary['credit_limit']);
        $outstandingCents = $this->toCents($summary['current_outstanding']);
        $requestedCents = max(0, $this->toCents($requestedCredit));
        $projectedCents = $outstandingCents + $requestedCents;

        return array_merge($summary, [
            'requested_credit' => $this->fromCents($requestedCents),
            'projected_outstanding' => $this->fromCents($projectedCents),
            'exceeds_limit' => $projectedCents > $limitCents,
        ]);
    }

    /** @param array{credit_limit: float, current_outstanding: float, available_credit: float, requested_credit: float} $assessment */
    public function limitExceededMessage(array $assessment): string
    {
        return sprintf(
            'Credit Limit: ₱%s Current Outstanding: ₱%s Available Credit: ₱%s Requested Credit: ₱%s Credit limit exceeded.',
            number_format($assessment['credit_limit'], 2),
            number_format($assessment['current_outstanding'], 2),
            number_format($assessment['available_credit'], 2),
            number_format($assessment['requested_credit'], 2),
        );
    }

    private function transactionOutstanding(Transaction $transaction): float
    {
        if ($transaction->status === 'voided') {
            return 0.0;
        }

        $netTotal = max(0, (float) $transaction->total - (float) ($transaction->refunds_sum_refund_amount ?? 0));

        return round(max(0, $netTotal - (float) ($transaction->payments_sum_amount ?? 0)), 2);
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
