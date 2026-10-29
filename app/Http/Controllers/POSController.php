<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CreditSale;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\SecurityRule;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\CustomerCreditService;
use App\Services\IntrusionDetectionService;
use App\Services\InventoryMonitoringService;
use App\Services\LayoutMetricsService;
use App\Services\ReferenceDataService;
use App\Services\TransactionSettlementService;
use App\Support\CaseInsensitiveSearch;
use App\Support\InputLimits;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class POSController extends Controller
{
    private const POS_PRODUCTS_PER_PAGE = 30;

    private const POS_PRODUCTS_MAX_PER_PAGE = 60;

    public function index(Request $request)
    {
        $filters = $this->posFilters($request);
        $initialProductError = null;

        try {
            $posVariants = $filters['search'] === ''
                ? $this->emptyPosVariants($request, $filters['per_page'])
                : $this->paginatedPosVariants($filters);
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Products could not be loaded.'], 500);
            }

            $initialProductError = 'Products could not be loaded. Check your connection and try again.';
            $posVariants = new LengthAwarePaginator([], 0, self::POS_PRODUCTS_PER_PAGE, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json($this->posVariantPagePayload($posVariants));
        }

        return view('pos.index', compact('filters', 'posVariants', 'initialProductError'));
    }

    public function products(Request $request)
    {
        $filters = $this->posFilters($request);

        try {
            return response()->json(
                $this->posVariantPagePayload(
                    $filters['search'] === ''
                        ? $this->emptyPosVariants($request, $filters['per_page'])
                        : $this->paginatedPosVariants($filters, $filters['per_page'])
                )
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Products could not be loaded.',
            ], 500);
        }
    }

    public function variantDetails(ProductVariant $variant)
    {
        $variant->load([
            'product:id,name,description,category_id,status',
            'product.productCategory:id,name',
            'brand:id,name',
            'unit:id,code',
            'supplier:id,name',
            'inventoryStock:id,variant_id,quantity_on_hand',
        ]);

        abort_if($variant->status !== 'active' || ! $variant->product || $variant->product->status !== 'active', 404);

        return response()->json($this->posVariantDetailPayload($variant));
    }

    public function customers(Request $request, CustomerCreditService $customerCredit)
    {
        $filters = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'credit_only' => 'nullable|boolean',
        ]);

        $search = trim($filters['search']);
        $creditOnly = filter_var($filters['credit_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $customers = Customer::query()
            ->select([
                'id', 'customer_code', 'customer_name', 'phone', 'email',
                'credit_enabled', 'credit_limit', 'default_credit_term_days', 'status',
            ])
            ->where('status', 'active')
            ->when($creditOnly, fn ($query) => $query->where('credit_enabled', true))
            ->where(function ($query) use ($search) {
                CaseInsensitiveSearch::where($query, 'customer_code', $search);
                CaseInsensitiveSearch::orWhere($query, 'customer_name', $search);
                CaseInsensitiveSearch::orWhere($query, 'phone', $search);
                CaseInsensitiveSearch::orWhere($query, 'email', $search);
            })
            ->orderBy('customer_name')
            ->limit(10)
            ->get();
        $summaries = $creditOnly ? $customerCredit->summaries($customers) : [];

        return response()->json([
            'customers' => $customers->map(function (Customer $customer) use ($summaries, $creditOnly) {
                $summary = $summaries[$customer->id] ?? null;

                return [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'customer_name' => $customer->customer_name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'credit_enabled' => $customer->credit_enabled,
                    'default_credit_term_days' => $customer->default_credit_term_days,
                    'credit_limit' => $creditOnly ? $summary['credit_limit'] : null,
                    'current_outstanding' => $creditOnly ? $summary['current_outstanding'] : null,
                    'available_credit' => $creditOnly ? $summary['available_credit'] : null,
                ];
            })->values(),
        ]);
    }

    public function transactions(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:completed,partial_refund,refunded,voided',
            'payment_method' => 'nullable|string|max:30',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();
        $cashierId = $user?->isCashier() ? $user->id : null;

        $transactions = Transaction::with('cashier', 'customer', 'creditSale', 'payments', 'refunds', 'paymentMethod')
            ->when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    CaseInsensitiveSearch::where($query, 'receipt_no', $search);
                    $query
                        ->orWhereHas('cashier', function ($cashierQuery) use ($search) {
                            CaseInsensitiveSearch::where($cashierQuery, 'name', $search);
                            CaseInsensitiveSearch::orWhere($cashierQuery, 'username', $search);
                        })
                        ->orWhere(function ($salesAssociateQuery) use ($search) {
                            CaseInsensitiveSearch::where($salesAssociateQuery, 'sales_associate', $search);
                        })
                        ->orWhereHas('paymentMethod', function ($paymentQuery) use ($search) {
                            CaseInsensitiveSearch::where($paymentQuery, 'code', $search);
                            CaseInsensitiveSearch::orWhere($paymentQuery, 'name', $search);
                        })
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            CaseInsensitiveSearch::where($customerQuery, 'customer_code', $search);
                            CaseInsensitiveSearch::orWhere($customerQuery, 'customer_name', $search);
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['payment_method'] ?? null, function ($query, string $paymentMethod) {
                $query->whereHas('paymentMethod', fn ($paymentQuery) => $paymentQuery->where('code', $paymentMethod));
            })
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('date', '<=', $date))
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(10)
            ->withQueryString();

        $today = now()->toDateString();
        $todaySales = app(LayoutMetricsService::class)->netSalesForDate($today, $cashierId);
        $todayTxns = Transaction::whereDate('date', $today)
            ->when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))
            ->count();
        $totalReceipts = Transaction::when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))->count();
        $paymentMethods = app(ReferenceDataService::class)->paymentMethods();
        $statuses = [
            'completed' => 'Completed',
            'partial_refund' => 'Partial Refund',
            'refunded' => 'Refunded',
            'voided' => 'Voided',
        ];

        return view('pos.transactions', compact(
            'transactions',
            'todaySales',
            'todayTxns',
            'totalReceipts',
            'paymentMethods',
            'statuses',
            'filters'
        ));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1|max:500',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:'.InputLimits::DATABASE_INTEGER_MAX,
            'items.*.unit_price' => 'required|numeric|min:0|max:'.InputLimits::DATABASE_MONEY_MAX,
            'items.*.discount' => 'nullable|numeric|min:0|max:'.InputLimits::DATABASE_MONEY_MAX,
            'discount' => 'nullable|numeric|min:0|max:'.InputLimits::DATABASE_MONEY_MAX,
            'payment_method' => 'required|in:cash,gcash,check,credit_account',
            'payment_reference' => 'nullable|required_if:payment_method,gcash|string|max:100',
            'payment_card_type' => 'nullable|required_if:payment_method,credit|in:credit',
            'payment_card_last4' => 'nullable|digits:4',
            'payment_payer_name' => 'nullable|string|max:120',
            'check_number' => 'nullable|required_if:payment_method,check|string|max:100',
            'check_bank' => 'nullable|required_if:payment_method,check|string|max:100',
            'check_date' => 'nullable|required_if:payment_method,check|date',
            'payment' => 'required|numeric|min:0|max:'.InputLimits::DATABASE_MONEY_MAX,
            'sales_associate' => 'nullable|string|max:120',
            'customer_id' => 'exclude_unless:payment_method,credit_account|required|integer|exists:customers,id',
            'credit_term_days' => 'exclude_unless:payment_method,credit_account|required|integer|min:1|max:65535',
            'initial_payment_method' => 'nullable|in:cash,gcash,check',
            'initial_payment_reference' => 'nullable|string|max:100',
            'initial_payment_card_type' => 'nullable|in:credit',
            'initial_payment_card_last4' => 'nullable|digits:4',
            'initial_payment_payer_name' => 'nullable|string|max:120',
            'initial_check_number' => 'nullable|string|max:100',
            'initial_check_bank' => 'nullable|string|max:100',
            'initial_check_date' => 'nullable|date',
            'checkout_token' => 'nullable|uuid',
        ]);

        $checkoutToken = $request->input('checkout_token');
        if ($checkoutToken) {
            $existingTransaction = Transaction::where('checkout_token', $checkoutToken)
                ->where('cashier_id', auth()->id())
                ->first();

            if ($existingTransaction) {
                return redirect()->route('pos.receipt', $existingTransaction);
            }
        }

        $creditLimitAttempt = null;
        DB::beginTransaction();
        try {
            $subtotal = 0;
            $discount = 0;
            $lineDiscounts = [];
            $lineUnitPrices = [];
            $hasLineDiscounts = collect($request->items)->contains(
                fn (array $item) => array_key_exists('discount', $item)
            );
            $quantitiesByVariant = [];
            $lockedVariants = [];
            foreach ($request->items as $item) {
                $variantId = (int) $item['variant_id'];
                $quantitiesByVariant[$variantId] = ($quantitiesByVariant[$variantId] ?? 0) + (int) $item['quantity'];
            }

            $variants = ProductVariant::with([
                'product',
                'brand',
                'unit',
                'inventoryStock' => fn ($query) => $query->lockForUpdate(),
            ])
                ->whereIn('id', array_keys($quantitiesByVariant))
                ->where('status', 'active')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantitiesByVariant as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                if (! $variant) {
                    throw (new ModelNotFoundException)->setModel(ProductVariant::class, [$variantId]);
                }

                $stock = $variant->inventoryStock;

                if (! $stock || $stock->quantity_on_hand < $quantity) {
                    DB::rollBack();

                    return back()->withErrors(['items' => "Insufficient stock for: {$this->variantLineName($variant)}"]);
                }

                $lockedVariants[$variantId] = compact('variant', 'stock');
            }

            foreach ($request->items as $index => $item) {
                $unitPrice = round((float) $item['unit_price'], 2);
                $lineSubtotal = round((int) $item['quantity'] * $unitPrice, 2);
                $subtotal = round($subtotal + $lineSubtotal, 2);

                if ($subtotal > InputLimits::DATABASE_MONEY_MAX) {
                    DB::rollBack();

                    return back()->withErrors(['items' => 'The sale total is too large. Reduce the item quantities.'])->withInput();
                }

                $lineUnitPrices[$index] = $unitPrice;

                if ($hasLineDiscounts) {
                    $lineDiscount = round((float) ($item['discount'] ?? 0), 2);

                    if ($lineDiscount > $lineSubtotal) {
                        throw ValidationException::withMessages([
                            "items.{$index}.discount" => 'The item discount cannot exceed its line subtotal.',
                        ]);
                    }

                    $lineDiscounts[$index] = $lineDiscount;
                    $discount += $lineDiscount;
                }
            }

            if ($hasLineDiscounts) {
                $discount = round($discount, 2);
            } else {
                // Compatibility for older clients and historical checkout tests.
                $discount = min((float) ($request->discount ?? 0), $subtotal);
            }
            $total = $subtotal - $discount;
            $paymentMethod = $request->payment_method;
            $customer = null;
            $creditTermDays = null;
            if ($paymentMethod === 'credit_account' && $request->filled('customer_id')) {
                $customer = Customer::whereKey($request->integer('customer_id'))->lockForUpdate()->firstOrFail();
                if ($customer->status !== 'active') {
                    throw ValidationException::withMessages(['customer_id' => 'The selected customer is inactive.']);
                }
            }
            if ($paymentMethod === 'credit_account') {
                if (! $customer?->credit_enabled) {
                    throw ValidationException::withMessages(['customer_id' => 'Select an active customer with credit enabled.']);
                }
                if ((float) $customer->credit_limit <= 0) {
                    throw ValidationException::withMessages(['customer_id' => 'The customer credit limit must be greater than zero.']);
                }
                $creditTermDays = $request->integer('credit_term_days');
            }
            $initialPaymentMethod = $paymentMethod === 'credit_account' && (float) $request->payment > 0
                ? $request->initial_payment_method
                : null;
            $actualPaymentMethod = $paymentMethod === 'credit_account' ? $initialPaymentMethod : $paymentMethod;
            $paymentReference = in_array($actualPaymentMethod, ['gcash', 'credit'], true)
                ? ($paymentMethod === 'credit_account' ? $request->initial_payment_reference : $request->payment_reference)
                : null;
            $paymentCardType = $actualPaymentMethod === 'credit'
                ? ($paymentMethod === 'credit_account' ? $request->initial_payment_card_type : $request->payment_card_type)
                : null;
            $paymentCardLast4 = $actualPaymentMethod === 'credit'
                ? ($paymentMethod === 'credit_account' ? $request->initial_payment_card_last4 : $request->payment_card_last4)
                : null;
            $paymentPayerName = $actualPaymentMethod === 'credit'
                ? ($paymentMethod === 'credit_account' ? $request->initial_payment_payer_name : $request->payment_payer_name)
                : null;
            $checkNumber = $actualPaymentMethod === 'check'
                ? ($paymentMethod === 'credit_account' ? $request->initial_check_number : $request->check_number)
                : null;
            $checkBank = $actualPaymentMethod === 'check'
                ? ($paymentMethod === 'credit_account' ? $request->initial_check_bank : $request->check_bank)
                : null;
            $checkDate = $actualPaymentMethod === 'check'
                ? ($paymentMethod === 'credit_account' ? $request->initial_check_date : $request->check_date)
                : null;
            $payment = round((float) $request->payment, 2);
            $changeDue = 0;

            if ($paymentMethod === 'cash') {
                if ($payment < $total) {
                    DB::rollBack();

                    return back()->withErrors(['payment' => 'Payment is less than total amount.']);
                }
                $changeDue = $payment - $total;
            } elseif ($paymentMethod === 'credit_account') {
                if ($payment > $total + 0.001) {
                    throw ValidationException::withMessages(['payment' => 'The initial payment cannot exceed the sale total.']);
                }
                if ($payment > 0 && ! $initialPaymentMethod) {
                    throw ValidationException::withMessages(['initial_payment_method' => 'Select the actual method used for the initial payment.']);
                }
                if ($payment > 0 && in_array($initialPaymentMethod, ['gcash', 'credit'], true) && ! $paymentReference) {
                    throw ValidationException::withMessages(['initial_payment_reference' => 'Enter the initial payment reference number.']);
                }
                if ($payment > 0 && $initialPaymentMethod === 'credit' && ! $paymentCardType) {
                    throw ValidationException::withMessages(['initial_payment_card_type' => 'Select the initial payment card type.']);
                }
                if ($payment > 0 && $initialPaymentMethod === 'check' && (! $checkNumber || ! $checkBank || ! $checkDate)) {
                    throw ValidationException::withMessages(['initial_check_number' => 'Enter the initial payment check number, bank, and date.']);
                }
                $creditExposure = round(max(0, $total - $payment), 2);
                $creditService = app(CustomerCreditService::class);
                $creditAssessment = $creditService->assessment($customer, $creditExposure);
                if ($creditAssessment['exceeds_limit']) {
                    $creditLimitAttempt = [
                        'customer_id' => $customer->id,
                        'customer_code' => $customer->customer_code,
                        'credit_limit' => $creditAssessment['credit_limit'],
                        'current_outstanding' => $creditAssessment['current_outstanding'],
                        'available_credit' => $creditAssessment['available_credit'],
                        'requested_credit' => $creditAssessment['requested_credit'],
                        'projected_outstanding' => $creditAssessment['projected_outstanding'],
                    ];
                    throw ValidationException::withMessages([
                        'customer_id' => $creditService->limitExceededMessage($creditAssessment),
                    ]);
                }
            } else {
                if (abs($payment - $total) > 0.01) {
                    DB::rollBack();

                    return back()->withErrors(['payment' => 'For non-cash payments, amount must match the total.']);
                }
            }

            if ($blockedReason = $this->detectCheckoutAnomalies($request, $subtotal, $discount, $total)) {
                DB::commit();

                return back()->withErrors(['security' => $blockedReason])->withInput();
            }

            $txn = Transaction::create([
                'receipt_no' => Transaction::generateReceiptNo(),
                'checkout_token' => $checkoutToken,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'cashier_id' => auth()->id(),
                'sales_associate' => $request->filled('sales_associate') ? $request->input('sales_associate') : null,
                'customer_id' => $customer?->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'payment_card_type' => $paymentCardType,
                'payment_card_last4' => $paymentCardLast4,
                'payment_payer_name' => $paymentPayerName,
                'check_number' => $checkNumber,
                'check_bank' => $checkBank,
                'check_date' => $checkDate,
                'payment' => $payment,
                'change_due' => $changeDue,
                'status' => 'completed',
                'payment_status' => $paymentMethod === 'credit_account' ? 'unpaid' : 'paid',
            ]);

            if ($paymentMethod === 'credit_account') {
                $creditSale = CreditSale::create([
                    'transaction_id' => $txn->id,
                    'customer_id' => $customer->id,
                    'credit_term_days' => $creditTermDays,
                    'due_date' => $txn->date->copy()->addDays($creditTermDays),
                    'original_credit_amount' => $total,
                ]);
                if ($payment > 0) {
                    app(TransactionSettlementService::class)->recordAppliedPayment($txn, [
                        'payment_method_id' => PaymentMethod::idForCode($initialPaymentMethod),
                        'amount' => $payment,
                        'payment_reference' => $paymentReference,
                        'check_number' => $initialPaymentMethod === 'check' ? $checkNumber : null,
                        'check_bank' => $initialPaymentMethod === 'check' ? $checkBank : null,
                        'check_date' => $initialPaymentMethod === 'check' ? $checkDate : null,
                        'received_by' => auth()->id(),
                        'paid_at' => now(),
                        'notes' => 'POS credit sale initial payment',
                    ]);
                }
            } else {
                app(TransactionSettlementService::class)->recordAppliedPayment($txn, [
                    'payment_method_id' => $txn->payment_method_id,
                    'amount' => $total,
                    'payment_reference' => $paymentReference,
                    'check_number' => $paymentMethod === 'check' ? $checkNumber : null,
                    'check_bank' => $paymentMethod === 'check' ? $checkBank : null,
                    'check_date' => $paymentMethod === 'check' ? $checkDate : null,
                    'received_by' => auth()->id(),
                    'paid_at' => now(),
                    'notes' => 'POS checkout payment',
                ]);
            }
            app(TransactionSettlementService::class)->synchronizePaymentStatus($txn);

            $inventoryMonitoring = app(InventoryMonitoringService::class);
            $transactionItems = [];

            foreach ($request->items as $index => $item) {
                $variantId = (int) $item['variant_id'];
                $variant = $lockedVariants[$variantId]['variant'];
                $stock = $lockedVariants[$variantId]['stock'];
                $product = $variant->product;
                $productName = $this->variantLineName($variant);
                $unitPrice = $lineUnitPrices[$index];
                $quantity = (int) $item['quantity'];
                $lineSubtotal = round($quantity * $unitPrice, 2);
                $lineDiscount = $hasLineDiscounts ? ($lineDiscounts[$index] ?? 0) : 0;
                $quantityBefore = (int) $stock->quantity_on_hand;
                $quantityAfter = $quantityBefore - $quantity;

                $transactionItems[] = [
                    'transaction_id' => $txn->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'product_name_snapshot' => $product->name,
                    'part_number_snapshot' => $variant->part_number,
                    'brand_snapshot' => $variant->brand?->name,
                    'size_snapshot' => $variant->size,
                    'color_snapshot' => $variant->color,
                    'unit_snapshot' => $variant->unit?->code ?? 'pcs',
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'discount' => $lineDiscount,
                ];

                $stock->update(['quantity_on_hand' => $quantityAfter]);
                $stock->quantity_on_hand = $quantityAfter;

                $movement = InventoryMovement::create([
                    'variant_id' => $variant->id,
                    'user_id' => auth()->id(),
                    'movement_type' => 'sale',
                    'quantity_change' => -1 * $quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'reference_type' => 'sale',
                    'reference_id' => $txn->id,
                    'remarks' => "Sold on receipt {$txn->receipt_no}.",
                    'created_at' => now(),
                ]);

                $variant->setRelation('inventoryStock', $stock);
                $inventoryMonitoring->syncStockAlert($variant);
                $inventoryMonitoring->inspectMovementForAnomalies($movement);
            }

            TransactionItem::insert($transactionItems);

            AuditLog::record('SALE', "Sale - Receipt: {$txn->receipt_no}, Total: PHP ".number_format($total, 2).', Method: '.Transaction::paymentMethodLabel($paymentMethod), null, [
                'affected_table' => 'transactions',
                'affected_record_id' => $txn->id,
                'new_values' => [
                    'receipt_no' => $txn->receipt_no,
                    'cashier_id' => $txn->cashier_id,
                    'sales_associate' => $txn->sales_associate,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'customer_id' => $customer?->id,
                    'credit_term_days' => $creditTermDays,
                    'initial_payment_method' => $initialPaymentMethod,
                    'initial_payment_amount' => $paymentMethod === 'credit_account' ? $payment : null,
                    'status' => $txn->status,
                    'item_count' => count($request->items),
                ],
            ]);
            if ($paymentMethod === 'credit_account') {
                AuditLog::record('CREDIT_SALE', "Credit sale {$txn->receipt_no} for customer {$customer->customer_code}", null, [
                    'affected_table' => 'credit_sales',
                    'affected_record_id' => $creditSale->id,
                    'new_values' => [
                        'transaction_id' => $txn->id,
                        'customer_id' => $customer->id,
                        'sale_total' => $total,
                        'initial_payment_amount' => $payment,
                        'requested_credit' => $creditExposure,
                        'credit_term_days' => $creditTermDays,
                        'due_date' => $creditSale->due_date->toDateString(),
                    ],
                ]);
            }
            DB::commit();

            return redirect()->route('pos.receipt', $txn->id)->with('success', 'Sale completed!');
        } catch (ValidationException $e) {
            DB::rollBack();
            if ($creditLimitAttempt !== null) {
                AuditLog::record('CREDIT_LIMIT_EXCEEDED_ATTEMPT', 'Credit limit exceeded for customer '.$creditLimitAttempt['customer_code'], null, [
                    'affected_table' => 'customers',
                    'affected_record_id' => $creditLimitAttempt['customer_id'],
                    'new_values' => $creditLimitAttempt,
                ]);
            }

            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['error' => 'The sale could not be completed. Please review the details and try again.']);
        }
    }

    public function receipt(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);
        $transaction->load('items', 'cashier', 'customer', 'creditSale', 'paymentMethod', 'payments.paymentMethod', 'refunds');

        return view('pos.receipt', compact('transaction'));
    }

    public function refundForm(Transaction $transaction)
    {
        Gate::authorize('refund', $transaction);
        abort_unless(in_array($transaction->status, ['completed', 'partial_refund'], true), 409, 'This transaction cannot be refunded.');
        $transaction->load('items.product', 'items.productVariant.brand', 'cashier', 'creditSale', 'payments', 'refunds.items');

        return view('pos.refund', compact('transaction'));
    }

    public function processRefund(Request $request, Transaction $transaction)
    {
        Gate::authorize('refund', $transaction);
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'refund_method' => 'nullable|in:cash,gcash,check',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:500',
            'items.*.transaction_item_id' => ['required', 'integer', 'distinct', Rule::exists('transaction_items', 'id')->where('transaction_id', $transaction->id)],
            'items.*.quantity' => 'required|integer|min:0|max:'.InputLimits::DATABASE_INTEGER_MAX,
            'items.*.restock' => 'nullable|boolean',
        ], [
            'items.*.transaction_item_id.exists' => 'One of the selected transaction items is invalid.',
            'items.*.quantity.integer' => 'Each refund quantity must be a whole number.',
            'items.*.quantity.min' => 'Refund quantities cannot be negative.',
            'items.*.restock.boolean' => 'The restock selection must be checked or unchecked.',
        ]);

        $refundItems = array_filter($validated['items'], fn ($item) => (int) $item['quantity'] > 0);

        if (empty($refundItems)) {
            return back()->withErrors(['items' => 'Select at least one item with quantity greater than 0.']);
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if (! in_array($transaction->status, ['completed', 'partial_refund'], true)) {
                throw ValidationException::withMessages(['items' => 'Only completed or partially refunded transactions can be refunded.']);
            }
            $transactionItems = TransactionItem::where('transaction_id', $transaction->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $refundedQuantities = RefundItem::whereIn('transaction_item_id', $transactionItems->keys())
                ->selectRaw('transaction_item_id, SUM(quantity) as refunded_quantity')
                ->groupBy('transaction_item_id')
                ->pluck('refunded_quantity', 'transaction_item_id');

            $refundAmount = 0;
            foreach ($refundItems as $rItem) {
                $txnItem = $transactionItems->get((int) $rItem['transaction_item_id']);
                if (! $txnItem) {
                    DB::rollBack();

                    return back()
                        ->withErrors(['items' => 'One of the selected items does not belong to this transaction.'])
                        ->withInput();
                }

                $previouslyRefunded = (int) ($refundedQuantities[$txnItem->id] ?? 0);
                $remainingQuantity = max(0, (int) $txnItem->quantity - $previouslyRefunded);
                $requestedQuantity = (int) $rItem['quantity'];
                $restock = filter_var($rItem['restock'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $itemName = $txnItem->product_name_snapshot ?: $txnItem->product_name;

                if ($requestedQuantity > $remainingQuantity) {
                    DB::rollBack();

                    return back()
                        ->withErrors([
                            'items' => "Cannot refund {$requestedQuantity} of '{$itemName}'. Only {$remainingQuantity} item(s) remain refundable after previous refunds.",
                        ])
                        ->withInput();
                }

                if ($restock && ! $txnItem->variant_id) {
                    DB::rollBack();

                    return back()
                        ->withErrors([
                            'items' => "Cannot restock '{$itemName}' because this transaction item is missing its exact variant reference.",
                        ])
                        ->withInput();
                }

                $grossRefund = $requestedQuantity * (float) $txnItem->unit_price;
                if ((float) $txnItem->discount > 0 && (int) $txnItem->quantity > 0) {
                    $grossRefund -= $requestedQuantity * ((float) $txnItem->discount / (int) $txnItem->quantity);
                }
                $refundAmount += $grossRefund;
            }

            // Historical transactions stored only a receipt-level discount.
            if ($transaction->subtotal > 0 && $transaction->discount > 0 && $transactionItems->sum('discount') <= 0) {
                $refundAmount = $refundAmount * (1 - ($transaction->discount / $transaction->subtotal));
            }

            $alreadyRefunded = $transaction->totalRefunded();
            $remaining = $transaction->total - $alreadyRefunded;
            $refundAmount = min(round($refundAmount, 2), round($remaining, 2));

            if ($refundAmount <= 0) {
                DB::rollBack();

                return back()->withErrors(['items' => 'This transaction has already been fully refunded.']);
            }

            $transaction->load(['creditSale', 'payments', 'refunds']);
            $settlement = app(TransactionSettlementService::class);
            $allocation = $settlement->refundAllocation($transaction, $refundAmount);
            if ($allocation['reimbursement'] > 0 && blank($validated['refund_method'] ?? null)) {
                throw ValidationException::withMessages([
                    'refund_method' => 'Select how the customer reimbursement will be paid.',
                ]);
            }

            if ($blockedReason = $this->detectRefundAnomalies($request, $transaction, $refundAmount)) {
                DB::commit();

                return back()->withErrors(['security' => $blockedReason])->withInput();
            }

            $refundType = (round($refundAmount, 2) >= round($remaining, 2)) ? 'full' : 'partial';

            $refund = Refund::create([
                'refund_no' => Refund::generateRefundNo(),
                'transaction_id' => $transaction->id,
                'processed_by' => auth()->id(),
                'reason' => $validated['reason'],
                'refund_type' => $refundType,
                'refund_amount' => $refundAmount,
                'receivable_adjustment_amount' => $allocation['receivable_adjustment'],
                'reimbursement_amount' => $allocation['reimbursement'],
                'refund_method' => $allocation['reimbursement'] > 0 ? $validated['refund_method'] : null,
                'notes' => $validated['notes'] ?? null,
            ]);
            $transaction->unsetRelation('refunds');

            foreach ($refundItems as $rItem) {
                $txnItem = $transactionItems->get((int) $rItem['transaction_item_id']);
                $restock = filter_var($rItem['restock'] ?? false, FILTER_VALIDATE_BOOLEAN);

                RefundItem::create([
                    'refund_id' => $refund->id,
                    'transaction_item_id' => $txnItem->id,
                    'product_name' => $txnItem->product_name_snapshot ?: $txnItem->product_name,
                    'quantity' => $rItem['quantity'],
                    'unit_price' => $txnItem->unit_price,
                    'restock' => $restock,
                ]);

                if ($restock) {
                    app(InventoryMonitoringService::class)->recordMovement(
                        (int) $txnItem->variant_id,
                        'sale_return',
                        (int) $rItem['quantity'],
                        'refund',
                        $refund->id,
                        "Restocked from refund {$refund->refund_no}."
                    );
                }
            }

            $oldTransactionStatus = $transaction->status;
            $totalRefunded = $transaction->totalRefunded();
            $newStatus = round($totalRefunded, 2) >= round((float) $transaction->total, 2)
                ? 'refunded'
                : 'partial_refund';
            $transaction->update(['status' => $newStatus]);
            $settlement->synchronizePaymentStatus($transaction);

            AuditLog::record('REFUND', "Refund {$refund->refund_no} for Receipt {$transaction->receipt_no} - PHP ".number_format($refundAmount, 2), null, [
                'affected_table' => 'refunds',
                'affected_record_id' => $refund->id,
                'old_values' => [
                    'transaction_status' => $oldTransactionStatus,
                ],
                'new_values' => [
                    'refund_no' => $refund->refund_no,
                    'transaction_id' => $transaction->id,
                    'receipt_no' => $transaction->receipt_no,
                    'refund_type' => $refundType,
                    'refund_amount' => $refundAmount,
                    'receivable_adjustment_amount' => $allocation['receivable_adjustment'],
                    'reimbursement_amount' => $allocation['reimbursement'],
                    'refund_method' => $allocation['reimbursement'] > 0 ? $validated['refund_method'] : null,
                    'transaction_status' => $newStatus,
                    'item_count' => count($refundItems),
                ],
            ]);
            DB::commit();

            $message = "Return {$refund->refund_no} processed. PHP ".number_format($refundAmount, 2).' reduced from the sale.';
            if ($allocation['receivable_adjustment'] > 0) {
                $message .= ' PHP '.number_format($allocation['receivable_adjustment'], 2).' reduced from the customer receivable.';
            }
            if ($allocation['reimbursement'] > 0) {
                $message .= ' PHP '.number_format($allocation['reimbursement'], 2).' reimbursed to the customer.';
            }

            return redirect()->route('pos.receipt', $transaction->id)->with('success', $message);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            report($e);

            return back()->withErrors(['error' => 'The refund could not be completed. Please review the details and try again.']);
        }
    }

    public function void(Request $request, Transaction $transaction)
    {
        Gate::authorize('void', $transaction);
        $validated = $request->validate([
            'void_reason' => 'required|string|min:10|max:500',
        ], [
            'void_reason.required' => 'Please enter the reason for voiding this transaction.',
            'void_reason.min' => 'The void reason must be at least 10 characters.',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::with('items')
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status === 'voided') {
                DB::rollBack();

                return back()->withErrors(['void_reason' => 'This transaction is already voided.']);
            }

            if ($transaction->status !== 'completed') {
                DB::rollBack();

                return back()->withErrors(['void_reason' => 'Only completed transactions can be voided. Use Return / Refund for refunded receipts.']);
            }

            foreach ($transaction->items as $item) {
                if (! $item->variant_id) {
                    DB::rollBack();

                    return back()->withErrors(['void_reason' => 'This transaction cannot be voided because one item is missing its variant reference.']);
                }

                app(InventoryMonitoringService::class)->recordMovement(
                    (int) $item->variant_id,
                    'sale_return',
                    (int) $item->quantity,
                    'void',
                    $transaction->id,
                    "Restored from voided receipt {$transaction->receipt_no}."
                );
            }

            $oldStatus = $transaction->status;
            $transaction->update(['status' => 'voided']);
            app(TransactionSettlementService::class)->synchronizePaymentStatus($transaction);

            AuditLog::record('VOID', "Voided Receipt {$transaction->receipt_no}", null, [
                'affected_table' => 'transactions',
                'affected_record_id' => $transaction->id,
                'old_values' => [
                    'status' => $oldStatus,
                ],
                'new_values' => [
                    'status' => 'voided',
                    'reason' => $validated['void_reason'],
                    'restored_items' => $transaction->items->count(),
                ],
            ]);

            DB::commit();

            return redirect()->route('pos.transactions')
                ->with('success', "Receipt {$transaction->receipt_no} has been voided and stock was restored.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['void_reason' => 'The transaction could not be voided. Please try again.']);
        }
    }

    private function isOffHoursTransaction(): bool
    {
        $hour = (int) now()->format('G');
        $startHour = (int) config('security.business_hours.start_hour', 8);
        $endHour = (int) config('security.business_hours.end_hour', 17);

        return $hour < $startHour || $hour >= $endHour;
    }

    private function detectCheckoutAnomalies(Request $request, float $subtotal, float $discount, float $total): ?string
    {
        $blocked = null;

        // Off-hours transaction: flags sales processed outside configured store operating hours.
        if ($this->isOffHoursTransaction()) {
            $reason = $this->recordTransactionAnomaly(
                'off_hours_transaction',
                'off_hours_transaction',
                'Sale was processed outside configured business hours.',
                $request
            );
            $blocked ??= $reason;
        }

        // Large transaction: flags a single receipt whose total reaches the configured amount threshold.
        $largeThreshold = $this->ruleThreshold('large_transaction', 'large_transaction_amount');
        if ($total >= $largeThreshold) {
            $reason = $this->recordTransactionAnomaly(
                'large_transaction',
                'large_transaction',
                'Sale total PHP '.number_format($total, 2).' reached the configured large transaction threshold of PHP '.number_format($largeThreshold, 2).'.',
                $request
            );
            $blocked ??= $reason;
        }

        // Rapid successive transactions: flags bursts of receipts from the same cashier in a short window.
        $rapidWindow = $this->ruleWindow('rapid_successive_transaction');
        $rapidLimit = $this->ruleThreshold('rapid_successive_transaction');
        $recentTransactions = Transaction::where('cashier_id', auth()->id())
            ->where('created_at', '>=', now()->subMinutes($rapidWindow))
            ->count();
        if (($recentTransactions + 1) >= $rapidLimit) {
            $reason = $this->recordTransactionAnomaly(
                'rapid_successive_transaction',
                'rapid_successive_transaction',
                ($recentTransactions + 1).' transactions were processed by this cashier within '.$rapidWindow.' minute(s).',
                $request
            );
            $blocked ??= $reason;
        }

        // Excessive discount: compares the receipt discount percentage against a configurable percentage threshold.
        $discountPercent = $subtotal > 0 ? ($discount / $subtotal) * 100 : 0;
        $discountThreshold = $this->ruleThreshold('excessive_discount');
        if ($discountPercent >= $discountThreshold) {
            $reason = $this->recordTransactionAnomaly(
                'excessive_discount',
                'excessive_discount',
                'Sale discount of '.number_format($discountPercent, 2).'% reached the configured threshold of '.number_format($discountThreshold, 2).'%.',
                $request
            );
            $blocked ??= $reason;
        }

        return $blocked;
    }

    private function detectRefundAnomalies(Request $request, Transaction $transaction, float $refundAmount): ?string
    {
        $blocked = null;

        // Excessive void/refund pattern: flags repeated refunds by the same staff member in a configured window.
        $blocked = $this->detectRepeatedReversals($request);

        // Large refund: uses the same large transaction threshold to flag unusually large reversal amounts.
        $largeThreshold = $this->ruleThreshold('large_transaction', 'large_transaction_amount');
        if ($refundAmount >= $largeThreshold) {
            $blocked ??= $this->recordTransactionAnomaly(
                'large_transaction',
                'large_transaction',
                'Refund for receipt '.$transaction->receipt_no.' reached PHP '.number_format($refundAmount, 2).'.',
                $request
            );
        }

        return $blocked;
    }

    private function detectRepeatedReversals(Request $request): ?string
    {
        $window = $this->ruleWindow('excessive_void');
        $limit = $this->ruleThreshold('excessive_void');
        $recentRefunds = Refund::where('processed_by', auth()->id())
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();
        $recentVoids = AuditLog::where('action', 'VOID')
            ->where('user_id', auth()->id())
            ->where('timestamp', '>=', now()->subMinutes($window))
            ->count();
        $reversals = $recentRefunds + $recentVoids + 1;

        if ($reversals >= $limit) {
            return $this->recordTransactionAnomaly(
                'excessive_void',
                'excessive_void',
                "{$reversals} refunds or voids were processed by this staff member within {$window} minute(s).",
                $request
            );
        }

        return null;
    }

    private function recordTransactionAnomaly(string $ruleName, string $eventType, string $description, Request $request): ?string
    {
        $ids = app(IntrusionDetectionService::class);
        $rule = $this->securityRule($ruleName);

        if ($rule === false) {
            return null;
        }

        $severity = $ids->ruleSeverity($ruleName, (string) config("security.rules.{$ruleName}.severity", 'medium'));
        $action = $ids->ruleAction($ruleName, (string) config("security.rules.{$ruleName}.action", 'alert'));

        $ids->createIntrusionEvent(
            $eventType,
            $severity,
            $description,
            $request,
            auth()->user(),
            $rule instanceof SecurityRule ? $rule : null,
            'transaction'
        );

        return $action === 'block'
            ? 'Transaction blocked by security rule: '.str_replace('_', ' ', $ruleName).'.'
            : null;
    }

    private function securityRule(string $ruleName): SecurityRule|false|null
    {
        $rule = app(IntrusionDetectionService::class)->ruleFor($ruleName);

        if ($rule || $rule === false || $ruleName !== 'large_transaction') {
            return $rule;
        }

        return app(IntrusionDetectionService::class)->ruleFor('large_transaction_amount');
    }

    private function ruleThreshold(string $ruleName, ?string $fallbackRuleName = null): float
    {
        $fallback = (float) config("security.rules.{$ruleName}.threshold_value", 0);
        $threshold = app(IntrusionDetectionService::class)->ruleThreshold($ruleName, $fallback);

        if ($threshold > 0 || ! $fallbackRuleName) {
            return (float) $threshold;
        }

        return (float) app(IntrusionDetectionService::class)->ruleThreshold(
            $fallbackRuleName,
            (float) config("security.rules.{$fallbackRuleName}.threshold_value", $fallback)
        );
    }

    private function ruleWindow(string $ruleName): int
    {
        return app(IntrusionDetectionService::class)->ruleWindow(
            $ruleName,
            (int) config("security.rules.{$ruleName}.time_window_minutes", 60)
        );
    }

    private function variantLineName(ProductVariant $variant): string
    {
        $variant->loadMissing('product', 'brand');

        $details = collect([
            $variant->variant_code,
            $variant->part_number,
            $variant->brand?->name,
            $variant->size,
            $variant->color,
        ])->filter()->unique()->join(' / ');

        return $details !== ''
            ? "{$variant->product?->name} ({$details})"
            : (string) $variant->product?->name;
    }

    private function posFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:'.self::POS_PRODUCTS_MAX_PER_PAGE,
        ]);

        $category = trim((string) ($validated['category'] ?? ''));

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'category' => $category === 'All' ? '' : $category,
            'per_page' => (int) ($validated['per_page'] ?? self::POS_PRODUCTS_PER_PAGE),
        ];
    }

    private function paginatedPosVariants(array $filters, ?int $perPage = null)
    {
        $perPage = min(
            max((int) ($perPage ?: self::POS_PRODUCTS_PER_PAGE), 1),
            self::POS_PRODUCTS_MAX_PER_PAGE
        );

        $query = ProductVariant::query()
            ->with([
                'product:id,name,category_id',
                'product.productCategory:id,name',
                'brand:id,name',
                'unit:id,code',
                'inventoryStock:id,variant_id,quantity_on_hand,minimum_stock',
            ])
            ->where('product_variants.status', 'active')
            ->whereHas('product', fn ($productQuery) => $productQuery->where('status', 'active'));

        if ($filters['search'] !== '') {
            $this->applyPosVariantSearch($query, $filters['search']);
        }

        if ($filters['category'] !== '') {
            $this->applyPosCategoryFilter($query, $filters['category']);
        }

        $paginator = $query
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select([
                'product_variants.id',
                'product_variants.product_id',
                'product_variants.brand_id',
                'product_variants.unit_id',
                'product_variants.variant_code',
                'product_variants.barcode',
                'product_variants.part_number',
                'product_variants.size',
                'product_variants.color',
                'product_variants.image_path',
                'product_variants.selling_price',
            ])
            ->orderBy('products.name')
            ->orderBy('product_variants.variant_code')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (ProductVariant $variant) => $this->posVariantCardPayload($variant))
        );

        return $paginator;
    }

    private function emptyPosVariants(Request $request, int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage, max(1, $request->integer('page', 1)), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function applyPosVariantSearch($query, string $search): void
    {
        $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($terms as $term) {
            $query->where(function ($query) use ($term) {
                CaseInsensitiveSearch::where($query, 'product_variants.variant_code', $term);
                CaseInsensitiveSearch::orWhere($query, 'product_variants.barcode', $term);
                CaseInsensitiveSearch::orWhere($query, 'product_variants.part_number', $term);
                CaseInsensitiveSearch::orWhere($query, 'product_variants.size', $term);
                CaseInsensitiveSearch::orWhere($query, 'product_variants.color', $term);
                $query
                    ->orWhereHas('product', function ($productQuery) use ($term) {
                        CaseInsensitiveSearch::where($productQuery, 'name', $term);
                    })
                    ->orWhereHas('brand', fn ($brandQuery) => CaseInsensitiveSearch::where($brandQuery, 'name', $term));
            });
        }
    }

    private function applyPosCategoryFilter($query, string $category): void
    {
        $query->whereHas('product.productCategory', fn ($categoryQuery) => $categoryQuery->where('name', $category));
    }

    private function posVariantCardPayload(ProductVariant $variant): array
    {
        $product = $variant->product;
        $stock = (int) ($variant->inventoryStock?->quantity_on_hand ?? 0);
        $minimumStock = (int) ($variant->inventoryStock?->minimum_stock ?? 0);
        $stockStatus = $stock <= 0 ? 'out' : ($minimumStock > 0 && $stock <= $minimumStock ? 'low' : 'in');

        return [
            'id' => (int) $variant->id,
            'product_id' => (int) $variant->product_id,
            'product_name' => $product?->name,
            'category' => $product?->category,
            'variant_code' => $variant->variant_code,
            'barcode' => $variant->barcode,
            'part_number' => $variant->part_number,
            'brand' => $variant->brand?->name,
            'size' => $variant->size,
            'color' => $variant->color,
            'unit' => $variant->unit?->code ?? 'pcs',
            'price' => (float) $variant->selling_price,
            'stock' => $stock,
            'stock_status' => $stockStatus,
            'image_url' => $variant->image_path ? asset('storage/'.$variant->image_path) : null,
            'details_url' => route('pos.variants.details', $variant, false),
        ];
    }

    private function posVariantDetailPayload(ProductVariant $variant): array
    {
        return [
            'id' => (int) $variant->id,
            'product_name' => $variant->product?->name,
            'category' => $variant->product?->category,
            'variant_code' => $variant->variant_code,
            'description' => $variant->product?->description,
            'barcode' => $variant->barcode,
            'part_number' => $variant->part_number,
            'brand' => $variant->brand?->name,
            'unit' => $variant->unit?->code ?? 'pcs',
            'supplier' => $variant->supplier?->name,
            'size' => $variant->size,
            'color' => $variant->color,
            'stock' => (int) ($variant->inventoryStock?->quantity_on_hand ?? 0),
            'image_url' => $variant->image_path ? asset('storage/'.$variant->image_path) : null,
        ];
    }

    private function posVariantPagePayload($posVariants): array
    {
        return [
            'data' => $posVariants->items(),
            'has_more' => $posVariants->hasMorePages(),
            'next_page' => $posVariants->hasMorePages() ? $posVariants->currentPage() + 1 : null,
            'batch_size' => $posVariants->perPage(),
        ];
    }
}
