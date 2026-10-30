<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CreditPaymentController;
use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\AccountsReceivableReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::get('/login/otp', [AuthController::class, 'showOtp'])->name('login.otp')->middleware('guest');
Route::post('/login/otp', [AuthController::class, 'verifyOtp'])->name('login.otp.verify')->middleware('guest');
Route::post('/login/otp/resend', [AuthController::class, 'resendOtp'])->name('login.otp.resend')->middleware('guest');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'device', 'session.anomaly'])->group(function () {

    // Dashboard — admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
        Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/my-transactions', [DashboardController::class, 'myTransactions'])->name('dashboard.my-transactions');
    });

    // Sales — admin & cashier
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/pos',               [POSController::class, 'index'])->name('pos.index');
        Route::get('/pos/products',      [POSController::class, 'products'])->name('pos.products');
        Route::get('/pos/customers',     [POSController::class, 'customers'])->name('pos.customers');
        Route::get('/pos/variants/{variant}/details', [POSController::class, 'variantDetails'])->name('pos.variants.details');
        Route::get('/pos/transactions',  [POSController::class, 'transactions'])->name('pos.transactions');
        Route::post('/pos/checkout',     [POSController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{transaction}', [POSController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/transactions/{transaction}/payments/create', [CreditPaymentController::class, 'create'])->name('pos.credit-payments.create');
        Route::post('/pos/transactions/{transaction}/payments', [CreditPaymentController::class, 'store'])->name('pos.credit-payments.store');
        Route::get('/pos/credit-payments/{transactionPayment}', [CreditPaymentController::class, 'show'])->name('pos.credit-payments.show');
        Route::get('/accounts-receivable', [AccountsReceivableController::class, 'index'])->name('accounts-receivable.index');
        Route::get('/accounts-receivable/customers/{customer}', [AccountsReceivableController::class, 'show'])->name('accounts-receivable.customers.show');
    });

    // Refund / Return — admin and the cashier who owns the transaction
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/pos/refund/{transaction}',  [POSController::class, 'refundForm'])->name('pos.refund.form');
        Route::post('/pos/refund/{transaction}', [POSController::class, 'processRefund'])->name('pos.refund.process');
        Route::post('/pos/transactions/{transaction}/void', [POSController::class, 'void'])->name('pos.transactions.void');
    });

    // Inventory — admin & inventory staff
    Route::middleware('role:admin,inventory_staff')->group(function () {
        Route::get('/inventory',              [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/stock-overview', [InventoryController::class, 'stockOverview'])->name('inventory.stock-overview');
        Route::get('/inventory/movements',    [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::get('/inventory/create',       [InventoryController::class, 'create'])->name('inventory.create');
        Route::get('/inventory/search/products', [InventoryController::class, 'searchProducts'])->name('inventory.search.products');
        Route::get('/inventory/search/suppliers', [InventoryController::class, 'searchSuppliers'])->name('inventory.search.suppliers');
        Route::get('/inventory/stock-receipts', [StockReceiptController::class, 'index'])->name('stock-receipts.index');
        Route::get('/inventory/stock-receipts/create', [StockReceiptController::class, 'create'])->name('stock-receipts.create');
        Route::get('/inventory/stock-receipts/search/suppliers', [StockReceiptController::class, 'searchSuppliers'])->name('stock-receipts.search.suppliers');
        Route::get('/inventory/stock-receipts/search/variants', [StockReceiptController::class, 'searchVariants'])->name('stock-receipts.search.variants');
        Route::post('/inventory/stock-receipts', [StockReceiptController::class, 'store'])->name('stock-receipts.store');
        Route::get('/inventory/stock-receipts/{stockReceipt}', [StockReceiptController::class, 'show'])->name('stock-receipts.show');
        Route::get('/inventory/{variant}',    [InventoryController::class, 'show'])->name('inventory.show');
        Route::get('/inventory/{variant}/edit',[InventoryController::class, 'edit'])->name('inventory.edit');
        Route::post('/inventory',             [InventoryController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/{variant}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');
        Route::put('/inventory/{variant}',    [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{variant}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

        Route::get('/suppliers',              [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/create',       [SupplierController::class, 'create'])->name('suppliers.create');
        Route::get('/suppliers/{supplier}',    [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('/suppliers/{supplier}/edit',[SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::post('/suppliers',             [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}',   [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}',[SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // Reports & audit — admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/inventory', [ReportsController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/accounts-receivable', [AccountsReceivableReportController::class, 'index'])->name('reports.accounts-receivable');
        Route::get('/reports/accounts-receivable/export/csv', [AccountsReceivableReportController::class, 'export'])->name('reports.accounts-receivable.export');
        Route::get('/reports/accounts-receivable/export/excel', [AccountsReceivableReportController::class, 'exportExcel'])->name('reports.accounts-receivable.export.xlsx');
        Route::get('/reports/accounts-receivable/export/pdf', [AccountsReceivableReportController::class, 'exportPdf'])->name('reports.accounts-receivable.export.pdf');
        Route::get('/reports/export/excel', [ReportsController::class, 'exportExcel'])->name('reports.export.xlsx');
        Route::get('/reports/export/pdf', [ReportsController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
        Route::get('/reports/inventory/export/csv', [ReportsController::class, 'exportInventory'])->name('reports.inventory.export');
        Route::get('/reports/inventory/export/excel', [ReportsController::class, 'exportInventoryExcel'])->name('reports.inventory.export.xlsx');
        Route::get('/reports/inventory/export/pdf', [ReportsController::class, 'exportInventoryPdf'])->name('reports.inventory.export.pdf');
        Route::get('/audit/results', [AuditLogController::class, 'results'])->name('audit.results');
        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    });

    // User Management — admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/security/devices', [SecurityController::class, 'devices'])->name('security.devices');
        Route::post('/security/devices', [SecurityController::class, 'storeDevice'])->name('security.devices.store');
        Route::put('/security/devices/{device}', [SecurityController::class, 'updateDevice'])->name('security.devices.update');
        Route::delete('/security/devices/{device}',[SecurityController::class, 'destroyDevice'])->name('security.devices.destroy');
        Route::get('/security/blocked-entities', [SecurityController::class, 'blockedEntities'])->name('security.blocked-entities');
        Route::patch('/security/blocked-entities/{blockedEntity}/expire', [SecurityController::class, 'expireBlockedEntity'])->name('security.blocked-entities.expire');
        Route::patch('/security/blocked-entities/{blockedEntity}/remove', [SecurityController::class, 'removeBlockedEntity'])->name('security.blocked-entities.remove');
        Route::get('/security/intrusions', [SecurityController::class, 'events'])->name('security.events');
        Route::get('/security/intrusions/{event}/details', [SecurityController::class, 'showEvent'])->name('security.events.show');
        Route::patch('/security/intrusions/{event}', [SecurityController::class, 'updateEvent'])->name('security.events.update');
        Route::post('/security/intrusions/{event}/block-device', [SecurityController::class, 'blockEventDevice'])->name('security.events.block-device');
        Route::get('/security/intrusions/alerts/{alert}', [SecurityController::class, 'showAlert'])->name('security.events.alerts.show');
        Route::patch('/security/intrusions/alerts/{alert}/acknowledge', [SecurityController::class, 'acknowledgeAlert'])->name('security.events.alerts.acknowledge');
        Route::patch('/security/intrusions/alerts/{alert}/resolve', [SecurityController::class, 'resolveAlert'])->name('security.events.alerts.resolve');
        Route::patch('/security/intrusions/alerts/{alert}/reopen', [SecurityController::class, 'reopenAlert'])->name('security.events.alerts.reopen');
        Route::post('/security/intrusions/alerts/{alert}/resend-email', [SecurityController::class, 'resendAlertEmail'])->name('security.events.alerts.resend-email');
        Route::get('/security/rules', [SecurityController::class, 'rules'])->name('security.rules');
        Route::put('/security/rules/{rule}', [SecurityController::class, 'updateRule'])->name('security.rules.update');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::put('/settings/receipt', [SettingsController::class, 'updateReceipt'])->name('settings.receipt.update');
        Route::post('/settings/units', [UnitController::class, 'store'])->name('settings.units.store');
        Route::put('/settings/units/{unit}', [UnitController::class, 'update'])->name('settings.units.update');
        Route::delete('/settings/units/{unit}', [UnitController::class, 'destroy'])->name('settings.units.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/users',                    [UserController::class, 'index'])->name('users.index');
        Route::resource('customers', CustomerController::class)->except('destroy');
        Route::patch('/customers/{customer}/status', [CustomerController::class, 'updateStatus'])->name('customers.status');
        Route::get('/users/create',             [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{user}',              [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit',        [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users',                   [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',             [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle',    [UserController::class, 'toggleStatus'])->name('users.toggle');
    });
});
