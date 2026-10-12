# VMC Auto Parts — POS & Inventory System

> **Laravel 11 · Vite 6 · MySQL**

A full-featured Point-of-Sale and Inventory Management system for VMC Auto Parts, featuring email OTP verification, device-based access control, intrusion detection, role-based authorization, and a dark-mode capable UI.

---

## What Changed in This Upgrade (L10 → L11 + Vite)

| Area | Before (Laravel 10) | After (Laravel 11 + Vite) |
|------|--------------------|-----------------------------|
| **Framework** | Laravel 10 / PHP 8.1 | Laravel 11 / PHP 8.2 |
| **App bootstrap** | `App\Http\Kernel` + `Console\Kernel` + `Exceptions\Handler` | Single `bootstrap/app.php` with fluent API |
| **Service providers** | `AuthServiceProvider`, `RouteServiceProvider` | Only `AppServiceProvider`; routes/middleware registered in `bootstrap/app.php` |
| **Asset pipeline** | Static files in `public/css/` and `public/js/` loaded via `asset()` | Vite 6 processes `resources/css/` and `resources/js/`; loaded via `@vite()` |
| **Hot Reload** | None | Vite HMR (`npm run dev`) |
| **Production assets** | Raw unminified files served directly | Fingerprinted, minified, tree-shaken bundles in `public/build/` |
| **Middleware** | Registered in `Kernel::$middlewareAliases` | Registered in `bootstrap/app.php` `->withMiddleware()` |
| **CSRF on Axios** | Manual `$.ajaxSetup` style | Axios pre-configured in `resources/js/app.js` with the CSRF meta token |

---

## Requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- **Composer 2.x**
- **Node.js 20+** and **npm 10+** (or pnpm/yarn)
- **MySQL 8.0+**

---

## Installation

### 1. Clone / Extract

```bash
git clone <repo-url> vmc-autoparts
cd vmc-autoparts
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm ci
```

Use `npm ci` when `package-lock.json` is present. It removes any copied or stale
`node_modules` directory and installs the exact dependency versions recorded in
the lockfile, including the correct platform-specific Rollup package.

If the lockfile is intentionally updated after changing `package.json`, run:

```bash
npm install
```

### 4. Environment

```bash
cp .env.example .env
```

Generate a local application key. Never copy an `APP_KEY` from another machine or from a shared ZIP.

```bash
php artisan key:generate
```

Edit `.env` and configure your own local database credentials:

```dotenv
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Configure mail for OTP and intrusion alert emails. Use your own SMTP account or app password:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
SECURITY_ALERT_EMAIL=security-alerts@example.com
```

### 5. Database

```bash
php artisan migrate
# Optional: seed with sample data
php artisan db:seed
```

### 6. Storage symlink

```bash
php artisan storage:link
```

---

## Safe Environment Sharing

- Do not commit or include the real `.env` file in ZIP submissions, deployments, screenshots, or documentation.
- Share `.env.example` only. It must contain placeholders, not real `APP_KEY`, database passwords, mail passwords, SMTP usernames, OTP-related secrets, or security alert credentials.
- If you need to send the project as a ZIP, remove `.env` first or create the archive from Git after confirming `.gitattributes` excludes local secret files.
- If real credentials were shared before, rotate them immediately: generate a new Gmail app password or SMTP password, update database passwords if needed, and run `php artisan key:generate` on each local install.

---

## Development

Run **both** commands in separate terminals:

```bash
# Terminal 1 — PHP development server
php artisan serve

# Terminal 2 — Vite dev server with HMR
npm run dev
```

Open **http://127.0.0.1:8000**. Vite will hot-reload CSS and JS on save.

---

## Production Build

```bash
# Install dependencies from the lockfile
npm ci

# Build & fingerprint all assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The compiled assets land in `public/build/`. Laravel's `@vite()` directive automatically reads `public/build/manifest.json` to resolve fingerprinted filenames.

Do not include `node_modules` in the submitted project ZIP. Submit
`package.json` and `package-lock.json`; the recipient can restore dependencies
with `npm ci`, then generate production assets with `npm run build`.

If Vite or Rollup reports a missing platform-specific optional dependency,
delete the copied `node_modules` directory and run `npm ci` on the target
machine. Do not copy `node_modules` between Windows and Linux.

On Windows PowerShell, if script execution blocks `npm.ps1`, use:

```powershell
npm.cmd ci
npm.cmd run build
```

---

## Project Structure

```
vmc-autoparts/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # AuthController, POSController, InventoryController …
│   │   └── Middleware/         # RoleMiddleware, ValidateAuthorizedDevice, TrackUserSession …
│   ├── Models/                 # User, Product, Transaction, AuditLog …
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Services/               # DeviceIdentityService, IntrusionDetectionService …
│
├── bootstrap/
│   ├── app.php                 # ← Laravel 11 application bootstrap (replaces Kernel files)
│   └── providers.php
│
├── config/                     # app, auth, cache, database, session …
│
├── database/
│   └── migrations/
│
├── resources/
│   ├── css/
│   │   ├── app.css             # ← Main CSS entry (imports layouts-app-1.css)
│   │   └── views/             # Per-view stylesheets — processed by Vite
│   ├── js/
│   │   ├── app.js             # ← Main JS entry (imports layout JS + configures Axios)
│   │   └── views/             # Per-view scripts — processed by Vite
│   └── views/                 # Blade templates (use @vite() directive)
│
├── routes/
│   ├── web.php                 # All web routes with role/device middleware
│   ├── api.php
│   └── console.php
│
├── public/
│   ├── build/                  # ← Generated by `npm run build` (gitignored)
│   ├── logo/
│   └── index.php               # Laravel 11 entry point
│
├── vite.config.js              # ← Vite + Laravel plugin configuration
└── package.json
```

---

## Roles & Permissions

| Feature | Admin | Inventory Staff | Cashier |
|---------|:-----:|:-------:|:-------:|
| Dashboard | ✔ | ✔ | ✔ |
| POS Terminal | ✔ | ✔ | ✔ |
| Process Refund | ✔ | ✔ | ✗ |
| Inventory (read) | ✔ | ✔ | ✔ |
| Inventory (write) | ✔ | ✔ | ✗ |
| Suppliers | ✔ | ✔ | ✗ |
| Sales Reports | ✔ | ✔ | ✗ |
| Audit Log | ✔ | ✔ | ✗ |
| Categories | ✔ | ✗ | ✗ |
| Users | ✔ | ✗ | ✗ |
| email OTP verification | ✔ | ✗ | ✗ |
| Security / Devices | ✔ | ✗ | ✗ |

---

## Security Features

- **OTP via Email** — 5-minute TOTP on every login
- **OTP via Email** - 5-minute code verification on admin login
- **Device Whitelisting** — IP/MAC-based authorized device registry
- **Intrusion Detection** — Alerts on blocked-device access attempts
- **Audit Log** — Immutable record of all sensitive operations
- **Rate Limiting** — 3 failed login attempts → 5-minute lockout

---

## Adding a New View with CSS/JS

1. Create `resources/css/views/myview-1.css` and `resources/js/views/myview-1.js`
2. Add both to the `input` array in `vite.config.js`
3. In your Blade view:
   ```blade
   @vite('resources/css/views/myview-1.css')
   ...
   @vite('resources/js/views/myview-1.js')
   ```

---

## License

Proprietary — VMC Auto Parts internal use only.
