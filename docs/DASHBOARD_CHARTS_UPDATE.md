# Dashboard Charts Update

## Charts Implemented

### Top Selling Products

Chart Type: Horizontal Bar Chart

Metric: Net units sold during the current month. Refunded quantities and voided transactions are excluded. Revenue in the tooltip uses the historical transaction item unit price.

Database Source: `transactions`, `transaction_items`, and aggregated `refund_items`.

Files Modified: `DashboardAnalyticsService.php`, `dashboard/index.blade.php`, `dashboard-index-1.js`, and `dashboard-index-2.css`.

---

### Category Sales Distribution

Chart Type: Doughnut Chart

Metric: Revenue from retained sold units during the current month, grouped by the product's category. The five largest categories are shown and every remaining category is accurately combined into `Others`.

Database Source: `transactions`, `transaction_items`, `refund_items`, `products`, and `product_categories`.

Files Modified: `DashboardAnalyticsService.php`, `dashboard/index.blade.php`, `dashboard-index-1.js`, and `dashboard-index-2.css`.

---

### Cashier Sales Performance

Chart Type: Horizontal Bar Chart

Metric: Net sales processed during the current month. Tooltips also show transaction count. Voided transactions are excluded and refund amounts are deducted.

Database Source: `transactions`, aggregated `refunds`, and `users` through `transactions.cashier_id`.

Files Modified: `DashboardAnalyticsService.php`, `dashboard/index.blade.php`, `dashboard-index-1.js`, and `dashboard-index-2.css`.

## Files Modified

| File | Change |
| ---- | ------ |
| `app/Services/DashboardAnalyticsService.php` | Added grouped refund-aware chart queries and calculated the category `Others` segment. |
| `resources/views/dashboard/index.blade.php` | Replaced three text lists with chart containers, clear periods/metrics, empty states, and safely encoded JSON data. |
| `resources/js/views/dashboard-index-1.js` | Added accessible horizontal-bar and doughnut renderers, Peso formatting, labels, percentages, and tooltips. |
| `resources/css/views/dashboard-index-2.css` | Added responsive dashboard chart layouts, keyboard focus, restrained colors, and reduced-motion support. |
| `tests/Feature/DashboardBusinessIntelligenceTest.php` | Added dashboard rendering and chart-data regression coverage. |

## Queries Changed

- Top products are aggregated in SQL by product snapshot with `SUM`, refund quantities, ordering, and a five-record limit.
- Category revenue is aggregated in SQL. Only the grouped result set is loaded; categories after the top five are combined into `Others` in PHP.
- Cashier sales are aggregated in SQL by cashier with net revenue and transaction count.
- All queries use stored transaction prices rather than current product prices.
- No query runs once per product, category, or cashier.

## JavaScript Changed

- Chart data is read from one `application/json` element produced by Blade's `@json` encoder.
- Database labels are inserted with `textContent`, not HTML interpolation.
- Native focusable rows and accessible labels expose the same tooltip information to keyboard users.
- Empty datasets are handled by Blade and do not initialize a chart.
- No chart dependency or CDN was added because the existing Net Sales chart uses project-owned CSS and JavaScript.

## Testing

- `php artisan route:list --path=dashboard`: passed; both dashboard routes were registered.
- Focused Laravel dashboard/refund tests: passed, 9 tests and 85 assertions.
- Full Laravel test suite: passed, 125 tests and 753 assertions.
- `npm.cmd run build`: passed; Vite transformed 33 modules and generated the production bundle.
- Browser console and visual viewport checks: not automated because this project has no browser-test dependency configured.
