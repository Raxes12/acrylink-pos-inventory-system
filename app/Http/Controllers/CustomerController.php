<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\TransactionPayment;
use App\Services\AccountsReceivableService;
use App\Services\CustomerCreditService;
use App\Support\CaseInsensitiveSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    private const FIELDS = [
        'customer_name', 'phone', 'email', 'address', 'status',
        'credit_enabled', 'credit_limit', 'default_credit_term_days',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:150',
            'status' => 'nullable|in:active,inactive',
            'credit_enabled' => 'nullable|in:0,1',
            'page' => 'nullable|integer|min:1',
        ]);
        $search = trim($filters['search'] ?? '');

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    foreach (['customer_name', 'customer_code', 'phone', 'email', 'address'] as $column) {
                        CaseInsensitiveSearch::orWhere($query, $column, $search);
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(isset($filters['credit_enabled']), fn ($query) => $query->where('credit_enabled', (bool) $filters['credit_enabled']))
            ->orderBy('customer_name')->orderBy('id')
            ->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'filters'));
    }

    public function create()
    {
        return view('customers.form');
    }

    public function show(Customer $customer, AccountsReceivableService $receivables, CustomerCreditService $credit)
    {
        $creditSummary = $credit->summary($customer);
        $creditTransactions = $receivables->receivablesQuery()
            ->where('transactions.customer_id', $customer->id)
            ->orderByDesc('transactions.date')->orderByDesc('transactions.id')
            ->paginate(8, ['*'], 'credit_page')->withQueryString();
        $payments = TransactionPayment::query()
            ->whereHas('transaction', fn ($query) => $query->where('customer_id', $customer->id)->whereHas('creditSale'))
            ->with(['transaction:id,receipt_no,customer_id', 'paymentMethod:id,code,name', 'receivedBy:id,name'])
            ->latest('paid_at')->paginate(10, ['*'], 'payment_page')->withQueryString();

        return view('customers.show', compact('customer', 'creditSummary', 'creditTransactions', 'payments', 'receivables'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedCustomer($request);
        $customer = DB::transaction(function () use ($data) {
            $customer = Customer::createWithGeneratedCode($data);
            $this->audit('CUSTOMER_CREATED', $customer, [], $customer->only(self::FIELDS));

            return $customer;
        }, 3);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created.');
    }

    public function update(Request $request, Customer $customer)
    {
        $this->saveChanges($customer, $this->validatedCustomer($request));

        return redirect()->route('customers.show', $customer)->with('success', 'Customer saved.');
    }

    public function updateStatus(Request $request, Customer $customer)
    {
        $data = $request->validate(['status' => 'required|in:active,inactive']);
        $this->saveChanges($customer, $data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer status saved.');
    }

    private function validatedCustomer(Request $request): array
    {
        return $request->validate([
            'customer_code' => 'prohibited',
            'customer_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
            'credit_enabled' => 'required|boolean',
            'credit_limit' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'default_credit_term_days' => 'required|integer|min:1|max:65535',
        ]);
    }

    private function saveChanges(Customer $customer, array $data): void
    {
        DB::transaction(function () use ($customer, $data) {
            $locked = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $before = $locked->only(self::FIELDS);
            $locked->fill($data);
            $changed = array_keys($locked->getDirty());
            if ($changed === []) {
                return;
            }
            $locked->save();
            $after = $locked->only(self::FIELDS);
            $this->audit('CUSTOMER_UPDATED', $locked, array_intersect_key($before, array_flip($changed)), array_intersect_key($after, array_flip($changed)));

            $events = [
                'status' => $locked->status === 'active' ? 'CUSTOMER_ACTIVATED' : 'CUSTOMER_DEACTIVATED',
                'credit_enabled' => $locked->credit_enabled ? 'CREDIT_ENABLED' : 'CREDIT_DISABLED',
                'credit_limit' => 'CREDIT_LIMIT_CHANGED',
                'default_credit_term_days' => 'CREDIT_TERM_CHANGED',
            ];
            foreach ($events as $field => $action) {
                if (in_array($field, $changed, true)) {
                    $this->audit($action, $locked, [$field => $before[$field]], [$field => $after[$field]]);
                }
            }
        }, 3);
    }

    private function audit(string $action, Customer $customer, array $old, array $new): void
    {
        AuditLog::record($action, str_replace('_', ' ', $action).": {$customer->customer_code} — {$customer->customer_name}", null, [
            'affected_table' => 'customers',
            'affected_record_id' => $customer->id,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
