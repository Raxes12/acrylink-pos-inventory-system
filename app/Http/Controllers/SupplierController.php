<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search        = $request->search;
        $status        = $request->status;
        $paymentMethod = $request->payment_method;

        $suppliers = Supplier::with('paymentMethod')
            ->withCount('products')
            ->when($search, fn($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when(in_array($status, ['active', 'inactive'], true), fn($q) => $q->where('status', $status))
            ->when(in_array($paymentMethod, ['cash', 'check', 'credit_account', 'gcash'], true), fn($q) => $q->whereHas('paymentMethod', fn($pm) => $pm->where('code', $paymentMethod)))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadMissing('paymentMethod');

        $variants = ProductVariant::query()
            ->where('supplier_id', $supplier->id)
            ->with([
                'product:id,name',
                'brand:id,name',
                'inventoryStock:id,variant_id,quantity_on_hand',
            ])
            ->orderBy('product_id')
            ->orderBy('variant_code')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.show', compact('supplier', 'variants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'contact' => 'nullable|string|max:100',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'status'  => 'required|in:active,inactive',
            'payment_method' => 'required|in:cash,check,credit_account,gcash',
        ]);

        $supplier = Supplier::create($data);
        AuditLog::record('SUPPLIER', "Added supplier: {$supplier->name}");
        return redirect()->route('suppliers.index')->with('success', "Supplier \"{$supplier->name}\" added.");
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'contact' => 'nullable|string|max:100',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'status'  => 'required|in:active,inactive',
            'payment_method' => 'required|in:cash,check,credit_account,gcash',
        ]);

        $supplier->update($data);
        AuditLog::record('SUPPLIER', "Updated supplier: {$supplier->name}");
        return redirect()->route('suppliers.index')->with('success', "Supplier \"{$supplier->name}\" updated.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {
            return back()->withErrors(['error' => "Cannot delete \"{$supplier->name}\" — it has linked products."]);
        }
        $name = $supplier->name;
        $supplier->delete();
        AuditLog::record('SUPPLIER', "Deleted supplier: {$name}");
        return back()->with('success', "Supplier \"{$name}\" deleted.");
    }
}
