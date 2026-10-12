# Acry-Link Project Audit Report

Audit date: September 14, 2026

## 1. Executive Summary

**Overall status: NOT YET PRODUCTION READY**

Acry-Link is a Laravel 12 POS and inventory application with good functional coverage, server-side validation, role middleware, transactional stock updates, row locking, audit trails, and a successful production asset build. The audit found 25 meaningful findings: 2 critical, 7 high, 11 medium, and 5 low. Seven findings were fully fixed during this audit. Eighteen remain or require deployment decisions; one of those was partially mitigated with ignore rules.

Critical blockers are the production device-identity design and unrestricted proxy trust. High-risk blockers include count-based receipt numbers, missing server-side checkout idempotency, known credentials in seeders, production environment hardening, and sensitive backup handling.

## 2. Project Architecture Summary

- Laravel 12.69.2 on PHP 8.2.12; PHP requirement is `^8.2`.
- MySQL/MariaDB locally; SQLite `:memory:` is enforced for automated tests.
- Session authentication with custom roles: admin, cashier, and inventory staff.
- Email OTP for MFA, database sessions, audit logs, IDS rules, blocklists, and device whitelist middleware.
- Blade templates with page-specific CSS/JavaScript and Vite 7.
- Main modules: POS, transactions/refunds, products/variants, inventory, stock receipts, suppliers, reports, users, audit, and security administration.
- Stock mutations use `InventoryMonitoringService`, transactions, and row locks.
- Shared layout metrics and lookup data use short-lived caches with model-event invalidation.

## 3. Complete Findings Table

| ID | Severity | File | Line/Area | Category | Problem | Risk | Status |
| -- | -------- | ---- | --------- | -------- | ------- | ---- | ------ |
| SEC-001 | CRITICAL | `bootstrap/app.php` | proxy setup | Security | All proxies are trusted with `trustProxies(at: '*')`. | Direct clients may spoof forwarding headers used by IP controls. | REQUIRES DECISION |
| SEC-002 | CRITICAL | `app/Services/DeviceIdentityService.php` | `macAddress()` | Security | MAC identity accepts `X-Client-Mac`/input and otherwise uses ARP. | Client value is spoofable; ARP does not work across the Internet or proxies. | REQUIRES DECISION |
| DB-001 | HIGH | `app/Models/Transaction.php`, `Refund.php` | number generators | Data integrity | Receipt/refund numbers used `count() + 1`. | Deletes and concurrent requests could create duplicate unique keys. | FIXED |
| TXN-001 | HIGH | `app/Http/Controllers/POSController.php` | `checkout()` | Data integrity | No server-side idempotency token existed. | Retries or duplicate POSTs could create multiple sales and stock deductions. | FIXED |
| DB-002 | HIGH | `app/Http/Controllers/StockReceiptController.php` | receipt numbers | Data integrity | Daily count-based number generation could collide. | Concurrent receipts could fail with duplicate references. | FIXED |
| SEC-003 | HIGH | `database/seeders/UserSeeder.php` | seeded users | Security | Predictable admin/staff passwords and a real email are embedded in source. | Accidental production seeding exposes privileged accounts and personal data. | NOT FIXED |
| CONFIG-001 | HIGH | `.env` runtime | environment | Deployment | Current runtime is local with debug enabled and HTTP URL. | Detailed errors and unsafe production defaults if deployed unchanged. | NOT FIXED |
| SEC-004 | HIGH | `.env`, `config/session.php` | session settings | Security | Session encryption is false; secure-cookie settings are not configured. | Production cookies/data may lack expected transport/storage hardening. | NOT FIXED |
| DEPLOY-001 | HIGH | `storage/app/recovery/*.sql` | recovery dump | Deployment | A local SQL recovery dump contains audit, session, IP, and email data. | Packaging the workspace could disclose sensitive operational data. | NOT FIXED |
| API-001 | MEDIUM | `routes/api.php` | default route | API | `/api/user` required Sanctum although Sanctum is not installed. | Endpoint would fail when requested. | FIXED |
| SEC-005 | MEDIUM | web responses | headers | Security | Basic anti-sniffing, framing, referrer, and permissions headers were absent. | Weaker browser-side defense in depth. | FIXED |
| AUTH-001 | MEDIUM | `app/Http/Controllers/UserController.php` | password rules | Authentication | User passwords permit six characters. | Weak passwords are easier to guess. | NOT FIXED |
| AUTH-002 | MEDIUM | cache/rate limiter config | login throttling | Authentication | File cache is local-node state. | Throttles and cached security state diverge across multiple servers. | NOT FIXED |
| PERF-001 | MEDIUM | `app/Http/Controllers/ReportsController.php` | sales report | Performance | Interactive sales reports load every matching transaction and nested item/refund into PHP for metrics. | Memory and latency grow with transaction history. | NOT FIXED |
| PERF-002 | MEDIUM | device middleware | every web request | Performance | Authorized/blocked device collections are repeatedly loaded and filtered in PHP. | Request cost grows with device count. | NOT FIXED |
| PERF-003 | MEDIUM | `DeviceIdentityService.php` | ARP lookup | Performance | A shell command may execute during request/audit processing. | Adds latency and is unavailable on many hosts. | NOT FIXED |
| PERF-004 | MEDIUM | POS/audit searches | `%term%` predicates | Database | Leading-wildcard searches cannot effectively use normal B-tree indexes. | Search degrades as tables grow. | ACCEPTABLE AT CURRENT SCALE |
| DB-003 | MEDIUM | inventory migrations | numeric constraints | Database | Application validates bounds, but database check constraints are absent. | Direct writes/imports can violate business bounds. | NOT FIXED |
| DEPLOY-002 | MEDIUM | `.env`, queue config | OTP/alerts | Deployment | Queue is synchronous; deferred work still runs in the PHP request lifecycle. | Slow SMTP can occupy workers and reduce throughput. | NOT FIXED |
| DEPLOY-003 | MEDIUM | repository root | source control | Change control | Workspace has no Git repository. | No reliable baseline, review history, or rollback mechanism. | NOT FIXED |
| QA-001 | LOW | `VariantInventoryProcessTest.php` | refund test | QA | Test used inventory staff for an admin-only refund route. | False regression failure. | FIXED |
| QA-002 | LOW | `VariantInventorySchemaTest.php` | seed assertions | QA | Tests expected removed legacy seed `AP001`. | False regression failures. | FIXED |
| FRONT-001 | LOW | `vite.config.js`, CSS | build warning | Frontend | Absolute background URL is left for runtime resolution. | Harmless while `/public/logo/...` exists; verify under subdirectory hosting. | ACCEPTABLE |
| FRONT-002 | LOW | `resources/views/layouts/app.blade.php` | external assets | Frontend | Fonts and Bootstrap Icons depend on third-party CDNs. | UI changes or degrades during CDN/network failure; CSP is harder. | NOT FIXED |
| CLEAN-001 | LOW | generated files | cache/recovery | Cleanup | Runtime/test artifacts exist in the workspace. | They may be accidentally packaged. | PARTIALLY FIXED |

## 4. Critical Issues

### SEC-001: Unrestricted Proxy Trust

Affected file: `bootstrap/app.php`  
Affected area: middleware configuration  
Problem: every proxy is trusted while IP addresses drive whitelist, throttling, audit, blocking, and IDS behavior.  
Consequence: if the origin is directly reachable, a client can forge forwarding headers.  
Fix: restrict the origin firewall to Cloudflare/reverse-proxy traffic and configure explicit trusted proxy ranges/headers per deployment.  
Status: requires deployment decision.

### SEC-002: Device Identity Is Not Internet-Safe

Affected file: `app/Services/DeviceIdentityService.php`  
Affected function: `macAddress()`  
Problem: browsers cannot reveal their LAN MAC address. `X-Client-Mac` is client controlled, while server ARP sees only same-segment devices.  
Consequence: identity can be spoofed or becomes unavailable behind Cloudflare/Nginx/NAT.  
Fix: use an administrator-issued, random, revocable device credential stored in a secure cookie or managed client certificate; hash the server-side token. Keep IP as a risk signal, not primary identity.  
Status: requires architecture decision.

### DB-001/TXN-001: Sale Integrity

Affected files: `Transaction.php`, `Refund.php`, `POSController.php`, `StockReceiptController.php`  
Problem: count-based references are not concurrency-safe and checkout has no idempotency key.  
Consequence: collisions, failed sales, or duplicate sales/stock deductions under retries.  
Fix: add a database-backed sequence/reference service and a unique checkout idempotency key processed inside the same transaction.  
Status: fixed with a transaction-locked `document_sequences` table and a unique POS checkout token. The migration must be applied during deployment after backup.

## 5. Files Modified

| File | Why Changed | What Changed |
| ---- | ----------- | ------------ |
| `.gitignore` | Prevent accidental packaging | Ignored test cache, cache temp files, and recovery directory. |
| `app/Http/Middleware/AddSecurityHeaders.php` | Browser hardening | Added conservative response security headers. |
| `bootstrap/app.php` | Register hardening | Added security-header middleware. |
| `routes/api.php` | Remove confirmed broken skeleton endpoint | Removed unused Sanctum-dependent route. |
| `tests/Feature/LoginPageTest.php` | Verify headers | Added response-header assertions. |
| `tests/Feature/VariantInventoryProcessTest.php` | Correct stale role assumption | Refund scenario now uses admin. |
| `tests/Feature/VariantInventorySchemaTest.php` | Correct stale fixture assumptions | Assertions now target current normalized seed. |
| `app/Http/Controllers/POSController.php` | Prevent duplicate sales | Validates and reuses a unique checkout token. |
| `app/Http/Controllers/StockReceiptController.php` | Prevent receipt collisions | Uses the shared transaction-safe sequence generator. |
| `app/Models/Transaction.php` | Safe sale references | Uses the document sequence service and stores checkout tokens. |
| `app/Models/Refund.php` | Safe refund references | Uses the document sequence service. |
| `app/Models/StockReceipt.php` | Safe stock receipt references | Uses a daily document sequence. |
| `resources/views/pos/index.blade.php` | Submit idempotency key | Adds a UUID to each rendered checkout form. |
| `tests/Feature/POSCheckoutPaymentReferenceTest.php` | Duplicate-submit coverage | Verifies one transaction and one stock deduction. |

## 6. Detailed Change Log

### `.gitignore`
Reason: deployment hygiene. Added ignores for PHPUnit/cache/recovery artifacts. Existing files were not deleted. Side effect: recovery dumps no longer appear in future Git status. Verified syntax manually.

### `app/Http/Middleware/AddSecurityHeaders.php`
Reason: missing defense-in-depth headers. Adds `nosniff`, `SAMEORIGIN`, strict-origin referrer policy, and disables camera/geolocation/microphone. It intentionally does not impose CSP or HSTS. Verified by feature test and PHP lint.

### `bootstrap/app.php`
Reason: register the new web middleware. No authentication ordering or business middleware was changed. Route registration, Pint, and tests passed.

### `routes/api.php`
Reason: the only route referenced absent `laravel/sanctum` and had no project callers. API routes now remain intentionally empty. Route listing/cache passed.

### `tests/Feature/LoginPageTest.php`
Reason: regression coverage for headers. Four header assertions added. Passed.

### `tests/Feature/VariantInventoryProcessTest.php`
Reason: refunds are admin-only by route design. Changed only the refund actor and removed an unused import via Pint. Ten tests passed.

### `tests/Feature/VariantInventorySchemaTest.php`
Reason: legacy `AP001` no longer exists. Updated expected variant, supplier, price, cost, and stock to the current seed. Six tests passed.

## 7. Files Inspected But Not Changed

Reviewed all files under `app/`, `bootstrap/`, `config/`, `database/migrations`, `database/seeders`, `routes/`, `resources/views`, `resources/js`, and `resources/css`, plus Composer/npm manifests, Vite, PHPUnit configuration, README, ignore rules, generated asset manifest, storage layout, and Laravel logs. Business controllers, models, migrations, security services, layouts, and POS assets were left unchanged except where listed above.

## 8. Unused / Suspicious / Duplicate Files

| File | Finding | Recommendation |
| ---- | ------- | -------------- |
| `storage/app/recovery/*.sql` | Sensitive local recovery data | KEEP securely for recovery, but exclude from deployment and delete securely when no longer needed. |
| `bootstrap/cache/*.tmp` | Stale generated cache temporary file | REMOVE before packaging. |
| `.phpunit.result.cache` | Generated local test metadata | REMOVE before packaging. |
| `resources/css/views/dashboard-index-1.css` | Superseded-looking CSS; not a Vite entry | VERIFY BEFORE REMOVING. |
| `resources/css/views/auth-shell-1.css` | Not a Vite entry; may be historical | VERIFY BEFORE REMOVING. |
| `routes/console.php` default hourly quote | No business purpose | VERIFY BEFORE REMOVING. |

## 9. Security Audit

- Authentication: credentials are validated generically; rate limiting, active status, session regeneration, and logout invalidation exist. Password minimum is weak.
- Authorization: all business routes are under `auth`, device, session anomaly, and role middleware. Module-access tests pass. Refund/void are admin-only.
- CSRF: state-changing Blade forms include CSRF tokens; API route was removed.
- XSS: Blade output is escaped; no raw Blade output was found. Dynamic POS HTML uses escaping helpers, but future edits must preserve them.
- SQL injection: queries use Eloquent/bindings. Dynamic search values are parameterized.
- IDOR: route-model resources remain behind role groups. No confirmed IDOR found.
- Sessions/cookies: regeneration/invalidation are present; production encryption and secure-cookie settings remain required.
- OTP: six random digits, HMAC storage, five-attempt limit, expiry, resend throttle, and non-resetting expiry tests exist. Mail delivery should move to a durable queue for scale.
- Device whitelist: effective on a controlled LAN but not trustworthy over the public Internet as implemented.
- Rate limiting: appropriate for one node; use Redis/database atomic storage for multiple nodes.
- Uploads: image MIME/type and 2 MB limit are validated; files use generated storage names. No executable upload path was found.
- Secrets: `.env` was inspected only through redacted status checks. No secret values are printed here. Known credentials exist in seeders.
- Headers: basic headers were added. CSP/HSTS require HTTPS/CDN planning.
- Audit logging: sensitive keys are recursively redacted. The recovery dump demonstrates why database backups must be protected.

## 10. Device Whitelist / Intrusion Detection Review

Public IP detection can work only when Laravel trusts the actual reverse proxy and the origin cannot be bypassed. Private/LAN IPs are not propagated reliably across Internet proxies. A browser cannot obtain its physical MAC address. ARP can resolve a MAC only when the web server and client are on a compatible local network segment. Client-supplied MAC headers are assertions, not proof.

Behind Cloudflare, configure exact trusted proxy behavior and lock the origin to Cloudflare/tunnel traffic. Replace MAC identity with a random administrator-issued device token or managed certificate. Store only a hash server-side, rotate/revoke tokens, bind assignments to users, and retain IP/user-agent changes as IDS signals.

## 11. Database Audit

- All 20 migrations report applied. Foreign keys and major relationship indexes exist.
- Live read-only checks found zero variants without stock, negative stock, invalid thresholds, transaction-total mismatches, or orphan transaction/refund items.
- Unique constraints protect transaction, refund, stock-receipt, variant-code, and stock-per-variant identity.
- Checkout/refund/void/stock receipt operations use transactions and relevant stock row locks.
- Reference generation remains concurrency-unsafe despite unique constraints; uniqueness turns races into failed requests rather than corruption.
- Normal B-tree indexes cannot optimize `%term%` searches. Exact barcode lookup can use the barcode index.
- Consider database check constraints for nonnegative stock, valid thresholds, positive quantities, and nonnegative money after confirming MariaDB support and existing data.

## 12. Routes & Middleware Audit

- 82 application routes remain after removing the unused API route.
- Business routes use correct HTTP mutation verbs and CSRF protection.
- Role groups match tested admin/cashier/inventory access.
- Route cache succeeds.
- No duplicate named routes or GET mutation routes were confirmed.
- Middleware ordering is security-sensitive and should not be casually changed.
- Unrestricted proxy trust and spoofable device identity remain the principal middleware risks.

## 13. Performance Audit

- POS uses server-side search, a 30-record internal batch, eager loading, lightweight payloads, lazy details, and infinite scrolling without visible pagination.
- Confirmed N+1 regression tests pass for payment-method accessors.
- Shared layout metrics and reference data are cached and invalidated on model changes.
- Sales report aggregation loads the full filtered result graph into PHP; replace with SQL aggregates/precomputed reporting when data volume warrants it.
- Device middleware scans collections and may call ARP every request; query exact normalized identifiers and eliminate shell lookup in a production token design.
- Audit/POS contains-search will scan at scale. Full-text/prefix strategy should be based on measured volume.
- Exports intentionally load all filtered rows and should eventually stream/chunk large exports.

## 14. Frontend / Vite Audit

- Vite production build passed: 33 modules transformed.
- No missing imports or compilation failures were found.
- One known warning leaves `/logo/Acry-Link_Background.png` for runtime resolution; the public asset exists.
- No insecure HTTP subresources were found in the shared layout, but external Google Fonts/jsDelivr dependencies remain.
- No raw Blade rendering (`{!! !!}`) was found.
- Dynamic HTML insertion exists and currently uses escaping helpers or server-rendered trusted fragments.
- `public/hot` is absent, so production does not point at the Vite dev server.

## 15. Deployment Blockers

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [x] `APP_KEY` configured (value not inspected/reported)
- [ ] `APP_URL` set to final HTTPS URL
- [ ] Dedicated least-privilege DB credentials configured
- [ ] HTTPS enabled
- [ ] Trusted proxies restricted and origin protected
- [ ] Secure/encrypted session settings configured
- [x] Vite production build successful
- [x] Composer dependencies installed locally
- [x] Storage link configured locally
- [ ] Production storage/cache permissions verified
- [x] Database migrations reviewed/applied locally
- [ ] Queue architecture configured if asynchronous OTP/alerts are adopted
- [ ] Logging switched to production-appropriate level/rotation
- [ ] Automated encrypted backups and restore test configured
- [ ] Seeded default credentials removed/rotated
- [ ] Sensitive local artifacts excluded

## 16. Commands Executed

| Command | Result |
| ------- | ------ |
| `php -v` / `php artisan --version` | PASSED |
| PHP lint over 160 PHP files | PASSED |
| `composer validate --no-check-publish` | PASSED |
| `composer audit --no-interaction` | FAILED: Packagist unreachable; no advisory conclusion |
| `composer show laravel/sanctum` | FAILED as expected: package absent |
| `php artisan about` | PASSED |
| `php artisan route:list --except-vendor` | PASSED |
| `php artisan migrate:status` | PASSED; all migrations ran |
| `php artisan route:cache` then `route:clear` | PASSED |
| `php artisan test --compact` initial | FAILED: 3 stale tests |
| affected test files after fixes | PASSED |
| final `php artisan test` | PASSED: 122 tests |
| `npm audit --omit=dev` | PASSED; no production npm dependencies/vulnerabilities |
| full `npm audit` | FAILED: registry audit endpoint unavailable |
| `npm run build` | PASSED with one runtime-resolved asset warning |
| Pint on modified PHP files | PASSED after formatting two files |
| read-only live integrity/index queries | PASSED; no listed integrity defects |

## 17. Test Results

Final result after integrity fixes: **122 passed, 0 failed, 730 assertions, 27.64 seconds**.

Coverage includes role access, login/OTP, device authorization, IDS, checkout payment methods, stock locking behavior, refunds, inventory validation, search endpoints, reports/exports, caching, N+1 checks, and CRUD. Not tested under real concurrent HTTP workers, real SMTP failure modes, Cloudflare production headers, browser console automation, printer hardware, backup restore automation, or sustained load.

## 18. Remaining Recommendations

### MUST FIX BEFORE DEPLOYMENT

1. Replace spoofable MAC identity and restrict proxy trust/origin access.
2. Add database-backed reference sequences and checkout idempotency.
3. Remove/rotate seeded credentials and production accounts.
4. apply production environment, HTTPS, cookie, logging, and DB-user settings.
5. Establish protected backups and exclude sensitive local artifacts.

### SHOULD FIX SOON

1. Raise password requirements and consider compromised-password screening.
2. Use a shared atomic cache/rate limiter for multiple workers/nodes.
3. Optimize report aggregation/export for large histories.
4. Add database check constraints after compatibility review.
5. Self-host or intentionally approve external frontend dependencies.

### OPTIONAL IMPROVEMENTS

1. Add CSP using nonces after removing/allowing required inline styles.
2. Add browser E2E tests at desktop/tablet/mobile widths.
3. Add load/concurrency tests and production observability.
4. Remove verified obsolete style/default console files.

## 19. Production Deployment Checklist

Use `DEPLOYMENT_CHECKLIST.md`. Do not deploy the current `.env`, seeders as credentials, logs, caches, test results, recovery SQL, `node_modules`, or `vendor` from this workstation as an archive. Build and install from a controlled release artifact.

## 20. Final Verdict

**NOT YET READY FOR PRODUCTION**

Core business behavior is well tested and currently passes, but Internet-facing device identity/proxy trust and sale/reference concurrency can undermine security or transaction reliability. Deployment configuration, credentials, cookies, and backup handling also require explicit production setup.
