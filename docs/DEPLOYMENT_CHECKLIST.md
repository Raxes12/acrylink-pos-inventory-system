# Acry-Link Production Deployment Checklist

## Before Release

- [ ] Put the project under version control and tag the reviewed release.
- [ ] Resolve all MUST FIX items in `PROJECT_AUDIT_REPORT.md`.
- [ ] Replace known seeder passwords/email with deployment-safe provisioning.
- [ ] Rotate any credentials that have ever been shared or seeded.
- [ ] Remove logs, PHPUnit caches, cache temp files, and recovery SQL from the release artifact.
- [ ] Back up the production database and test a restore in an isolated environment.
- [ ] Run `composer audit --locked` and `npm audit` from a networked CI runner.

## Production Environment

Configure secrets directly on the server; never commit `.env`.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-production-domain.example
APP_TIMEZONE=Asia/Manila
LOG_CHANNEL=stack
LOG_LEVEL=warning
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

- [ ] Generate/set a unique `APP_KEY`; do not replace it after encrypted production data exists.
- [ ] Use a dedicated least-privilege database user with a strong password.
- [ ] Configure real SMTP credentials and an approved sender/security-alert recipient.
- [ ] Decide whether mail/alerts remain synchronous or move to a durable queue.
- [ ] If using multiple app instances, use a shared atomic cache/rate limiter such as Redis.
- [ ] Do not run `db:seed` in production until default credential provisioning is fixed.

## Proxy, Cloudflare, and Device Security

- [ ] Replace MAC/header identity with a random administrator-issued device credential or managed certificate.
- [ ] Hash device credentials server-side and support rotation/revocation.
- [ ] Configure only actual reverse proxies as trusted.
- [ ] Restrict origin traffic to Cloudflare/tunnel/reverse-proxy addresses so clients cannot bypass it.
- [ ] Confirm Laravel reports the real client IP through the deployed proxy chain.
- [ ] Test whitelist assignment, denied-user auditing, blocklist recovery, and admin recovery in staging.

## Build Release

From a clean checkout using the lock files:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Route caching was verified compatible during this audit.

- [ ] Ensure `public/hot` does not exist.
- [ ] Do not deploy `node_modules`, tests, local `.env`, logs, recovery dumps, or editor files.
- [ ] Confirm `public/build/manifest.json` and hashed assets are present.
- [ ] Recheck the Vite background asset when hosted below a URL subdirectory.

## Web Server

- [ ] Point Apache/Nginx document root to the project’s `public/` directory only.
- [ ] Deny access to `.env`, Composer files, storage internals, and dotfiles.
- [ ] Redirect HTTP to HTTPS.
- [ ] Set upload/body limits compatible with the application’s 2 MB image limit.
- [ ] Add HSTS only after HTTPS and proxy detection are verified.
- [ ] Ensure `storage/` and `bootstrap/cache/` are writable by the PHP worker, not world-writable.

```bash
php artisan storage:link
php artisan migrate --force
```

- [ ] Review pending migrations and take a backup before `migrate --force`.
- [ ] Never use `migrate:fresh`, `db:wipe`, reset, truncate, or destructive SQL in production.

## Transactions and Data Integrity

- [x] Implement a database-backed sequence/reference generator for sales, refunds, and stock receipts.
- [x] Add a unique server-side checkout idempotency key.
- [ ] Load-test simultaneous checkout, refund, stock receipt, and stock adjustment requests.
- [ ] Verify stock never becomes negative and failed transactions roll back fully.
- [ ] Consider database check constraints after MariaDB compatibility review.

## Queue and Scheduler

- [ ] If switching from `sync`, create/verify queue tables and supervise `php artisan queue:work`.
- [ ] Configure retries, timeout, failed jobs, monitoring, and restart workers after release.
- [ ] Review `routes/console.php`; the current hourly quote is not business-critical.
- [ ] Add cron for `php artisan schedule:run` only if approved scheduled jobs remain.

## Final Verification

```bash
php artisan about
php artisan migrate:status
php artisan route:list --except-vendor
php artisan test --compact
```

- [x] Final suite reports 122 tests and 730 assertions.
- [ ] Test admin OTP with real mail in staging.
- [ ] Test every role against allowed and forbidden URLs.
- [ ] Test POS search, details, cart, checkout, duplicate click/retry, receipt PDF, refund, and stock deduction.
- [ ] Test product upload and storage URLs.
- [ ] Test reports/exports with large representative data.
- [ ] Test desktop, laptop, tablet, and mobile layouts with browser console open.
- [ ] Confirm security headers and cookies using browser developer tools.
- [ ] Confirm debug pages and stack traces are not exposed.
- [ ] Monitor application/server logs after deployment and perform a rollback drill.
