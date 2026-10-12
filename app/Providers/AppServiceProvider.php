<?php

namespace App\Providers;

use App\Models\InventoryStock;
use App\Models\IntrusionEvent;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Services\LayoutMetricsService;
use App\Services\ReferenceDataService;
use App\Services\SystemSettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap pagination (matches the custom CSS in this project)
        Paginator::useBootstrap();

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $metrics = app(LayoutMetricsService::class);

            $view->with([
                'appSettings' => app(SystemSettingsService::class)->all(),
                'layoutStockAlerts' => $user?->canAccessInventory()
                    ? $metrics->stockAlerts()
                    : ['low' => 0, 'critical' => 0, 'out' => 0, 'total' => 0],
                'layoutSecurityAlerts' => $user?->isAdmin() ? $metrics->securityAlerts() : 0,
            ]);
        });

        InventoryStock::saved(fn () => app(LayoutMetricsService::class)->forgetStockAlerts());
        InventoryStock::deleted(fn () => app(LayoutMetricsService::class)->forgetStockAlerts());
        IntrusionEvent::saved(fn () => app(LayoutMetricsService::class)->forgetSecurityAlerts());
        IntrusionEvent::deleted(fn () => app(LayoutMetricsService::class)->forgetSecurityAlerts());

        Transaction::saved(function (Transaction $transaction) {
            $metrics = app(LayoutMetricsService::class);
            $metrics->forgetNetSales(
                $transaction->getRawOriginal('date') ?? $transaction->date,
                $transaction->getRawOriginal('cashier_id') ?? $transaction->cashier_id
            );
            $metrics->forgetNetSales($transaction->getOriginal('date'), $transaction->getOriginal('cashier_id'));
        });
        Transaction::deleted(fn (Transaction $transaction) => app(LayoutMetricsService::class)
            ->forgetNetSales($transaction->date, $transaction->cashier_id));
        Refund::saved(fn (Refund $refund) => $this->forgetRefundLayoutMetrics($refund));
        Refund::deleted(fn (Refund $refund) => $this->forgetRefundLayoutMetrics($refund));

        ProductCategory::saved(fn () => app(ReferenceDataService::class)->forgetCategories());
        ProductCategory::deleted(fn () => app(ReferenceDataService::class)->forgetCategories());
        Unit::saved(fn () => app(ReferenceDataService::class)->forgetUnits());
        Unit::deleted(fn () => app(ReferenceDataService::class)->forgetUnits());
        PaymentMethod::saved(fn () => app(ReferenceDataService::class)->forgetPaymentMethods());
        PaymentMethod::deleted(fn () => app(ReferenceDataService::class)->forgetPaymentMethods());
        Role::saved(fn () => app(ReferenceDataService::class)->forgetRoles());
        Role::deleted(fn () => app(ReferenceDataService::class)->forgetRoles());
    }

    private function forgetRefundLayoutMetrics(Refund $refund): void
    {
        $transaction = Transaction::query()->select(['id', 'date', 'cashier_id'])->find($refund->transaction_id);

        if ($transaction) {
            app(LayoutMetricsService::class)->forgetNetSales($transaction->date, $transaction->cashier_id);
        }
    }
}
