<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TransactionPayment;
use App\Services\AccountsReceivableService;
use App\Services\CustomerCreditService;
use Illuminate\Http\Request;

class AccountsReceivableController extends Controller
{
    public function index(Request $request, AccountsReceivableService $receivables)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:150',
            'customer' => 'nullable|string|max:150',
            'receipt' => 'nullable|string|max:50',
            'payment_status' => 'nullable|in:open,due_soon,overdue,partially_paid,paid,unpaid',
            'due_from' => 'nullable|date',
            'due_to' => 'nullable|date|after_or_equal:due_from',
            'transaction_from' => 'nullable|date',
            'transaction_to' => 'nullable|date|after_or_equal:transaction_from',
        ]);
        if (auth()->user()?->isCashier()) {
            $filters['cashier_id'] = auth()->id();
        }

        $summary = $receivables->summary($filters);
        $transactions = $receivables->receivablesQuery($filters)
            ->orderBy('credit_sales.due_date')
            ->orderByDesc('transactions.date')
            ->paginate(20)
            ->withQueryString();

        return view('accounts-receivable.index', compact('transactions', 'summary', 'filters', 'receivables'));
    }

    public function show(Customer $customer, AccountsReceivableService $receivables, CustomerCreditService $credit)
    {
        $cashierId = auth()->user()?->isCashier() ? auth()->id() : null;
        $customerSummary = $credit->summary($customer);
        $transactions = $receivables->openReceivablesQuery(array_filter(['cashier_id' => $cashierId]))
            ->where('transactions.customer_id', $customer->id)
            ->orderBy('credit_sales.due_date')
            ->paginate(10, ['*'], 'credit_page')
            ->withQueryString();
        $payments = TransactionPayment::query()
            ->whereHas('transaction', fn ($query) => $query
                ->where('customer_id', $customer->id)
                ->when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))
                ->whereHas('creditSale'))
            ->with([
                'transaction:id,receipt_no,customer_id',
                'paymentMethod:id,code,name',
                'receivedBy:id,name',
            ])
            ->latest('paid_at')
            ->paginate(15, ['*'], 'payment_page')
            ->withQueryString();

        return view('accounts-receivable.show', compact(
            'customer', 'customerSummary', 'transactions', 'payments', 'receivables'
        ));
    }
}
