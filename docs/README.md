# Acry-Link POS and Inventory System

This Laravel 12 application uses Blade views, Vite 7, and MySQL. It includes a POS, inventory and supplier management, reports, audit logs, email OTP, device controls, and security monitoring.

## Requirements

- PHP 8.2 or newer with the extensions required by Laravel and the configured MySQL driver
- Composer 2
- Node.js 20 or newer and npm
- MySQL 8 or a compatible server

## Local setup

1. Run `composer install` and `npm ci` from the project root.
2. Copy `.env.example` to `.env`, then run `php artisan key:generate`.
3. Set the local database and mail values in `.env`.
4. Run `php artisan migrate`. For sample data, run `php artisan db:seed` separately.
5. Run `php artisan storage:link` for public uploads.
6. Start `php artisan serve` and `npm run dev` in separate terminals.

Keep `.env` private. Commit `.env.example`, `composer.lock`, and `package-lock.json` so other installations can reproduce dependency versions.

## Production assets

Run `npm ci` and `npm run build` before serving the application. Laravel's `@vite` directives use the generated `public/build/manifest.json`. `public/build`, `vendor`, `node_modules`, runtime storage, and the `public/storage` link are excluded from Git.

On Windows PowerShell, use `npm.cmd` when the execution policy blocks `npm.ps1`.

## Tests

- `php artisan test` runs the PHP unit and feature suites.
- `npm run test:browser` builds assets and runs the persistent-sidebar navigation test in headless Chrome. Set `CHROME_PATH` to the browser executable when Chrome is not installed in its default Windows location.

## Project layout

- `app/Http/Controllers`, `app/Http/Middleware`: HTTP endpoints and request middleware.
- `app/Models`, `app/Services`, `app/Support`: data and application logic.
- `database/migrations`, `database/factories`, `database/seeders`: schema and sample data.
- `resources/views`: Blade pages and components.
- `resources/css`, `resources/js`: Vite source assets. Page scripts register their initialization with `resources/js/page-lifecycle.js` so navigation can retain the sidebar.
- `routes/web.php`, `routes/api.php`, `routes/console.php`: route entry points; API routes are currently empty.
- `tests/Feature`, `tests/Unit`, `tests/browser`: automated checks.
- `public/logo`: images served directly by the web server.

The deployment notes are in `DEPLOYMENT_CHECKLIST.md`.
