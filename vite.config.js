import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devHost = env.VITE_DEV_HOST || '127.0.0.1';
    const devPort = Number(env.VITE_DEV_PORT || 5173);

    return {
        plugins: [
            laravel({
            input: [
                // ── Shared / layout assets ────────────────────────────────
                'resources/css/app.css',
                'resources/js/app.js',

                // ── Auth views ────────────────────────────────────────────
                'resources/css/views/auth-login-1.css',
                'resources/js/views/auth-login-1.js',
                'resources/css/views/auth-otp-1.css',
                'resources/js/views/auth-otp-1.js',

                // ── Dashboard ─────────────────────────────────────────────
                'resources/css/views/dashboard-index-2.css',
                'resources/css/views/dashboard-my-transactions-1.css',
                'resources/js/views/dashboard-index-1.js',

                // ── POS ───────────────────────────────────────────────────
                'resources/css/views/pos-index-1.css',
                'resources/css/views/pos-receipt-1.css',
                'resources/css/views/pos-refund-1.css',
                'resources/js/views/pos-index-1.js',
                'resources/js/views/pos-receipt-1.js',
                'resources/js/views/pos-refund-1.js',
                'resources/css/views/credit-payments-1.css',
                'resources/css/views/accounts-receivable-1.css',
                'resources/css/views/customers-1.css',
                'resources/js/views/customers-index-1.js',
                'resources/js/views/credit-payments-1.js',

                // ── Inventory ─────────────────────────────────────────────
                'resources/css/views/inventory-index-1.css',
                'resources/css/views/inventory-show.css',
                'resources/js/views/inventory-index-1.js',
                'resources/js/views/inventory-form-1.js',
                'resources/js/views/stock-receipts-create-1.js',

                // ── Categories ────────────────────────────────────────────
                'resources/css/views/categories-index-1.css',

                // ── Suppliers ─────────────────────────────────────────────
                'resources/css/views/suppliers-index-1.css',
                'resources/css/views/suppliers-show-1.css',
                'resources/js/views/suppliers-index-1.js',
                'resources/js/views/suppliers-_form-1.js',

                // ── Users ─────────────────────────────────────────────────
                'resources/css/views/users-index-1.css',
                'resources/css/views/users-show-2.css',
                'resources/js/views/users-index-1.js',

                // ── Reports ───────────────────────────────────────────────
                'resources/css/views/reports-index-1.css',

                // ── Audit ─────────────────────────────────────────────────
                'resources/css/views/audit-index-1.css',
                'resources/js/views/audit-index-1.js',

                // ── Security ──────────────────────────────────────────────
                'resources/css/views/security-devices-1.css',
                'resources/css/views/security-pages-1.css',
                'resources/js/views/security-devices-1.js',

                // System settings
                'resources/css/views/settings-1.css',

                // ── Errors ────────────────────────────────────────────────
                'resources/css/views/errors-403-1.css',
            ],
            refresh: [
                'resources/views/**',
                'routes/**',
            ],
            }),
        ],
        server: {
            host: '0.0.0.0',
            port: devPort,
            strictPort: true,
            origin: `http://${devHost}:${devPort}`,
            hmr: {
                host: 'localhost',
                port: devPort,
            },
        },
        build: {
            // Laravel's @vite directive reads public/build/manifest.json.
            manifest: 'manifest.json',
            outDir: 'public/build',
            rollupOptions: {
                output: {
                    // Keep descriptive chunk names in production
                    manualChunks: undefined,
                },
            },
        },
    };
});
