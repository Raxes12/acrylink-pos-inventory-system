# Acry-Link Migration Guide

Earlier foundation migrations use the `2026_06_01` prefix. Later additive
migrations retain their actual release dates so already-run files are never renamed.

Read the files from top to bottom by their number. Some numbers are skipped
because old temporary/backfill migrations were removed after their final columns
were folded into the main table-creation migrations. The gaps are intentional:
renaming already-run migrations can make Laravel treat them as new migrations on
an existing database.

## Active Migrations

| Order | Migration | What It Creates Or Adds |
| --- | --- | --- |
| 001 | `2026_06_01_000001_create_users_table.php` | Base user accounts. |
| 002 | `2026_06_01_000002_create_lookup_tables.php` | Product categories, units, and payment methods. |
| 003 | `2026_06_01_000003_create_suppliers_table.php` | Supplier records. |
| 004 | `2026_06_01_000004_create_products_table.php` | General product identity only: name, category, description, and status. |
| 005 | `2026_06_01_000005_create_transactions_table.php` | POS transactions and transaction line items. |
| 006 | `2026_06_01_000006_create_refunds_table.php` | Refund headers and refunded line items. |
| 007 | `2026_06_01_000007_create_audit_logs_table.php` | Audit trail with user, request, affected record, and old/new JSON context. |
| 008 | `2026_06_01_000008_create_security_tables.php` | Login attempts, authorized devices, IDS events, and IDS user sessions. |
| 009 | `2026_06_01_000009_create_sessions_table.php` | Laravel's database session payload table. |
| 010 | `2026_06_01_000010_create_roles_table_and_add_role_id_to_users.php` | Normalized roles table and `users.role_id`. |
| 011 | `2026_06_01_000011_create_mfa_challenges_table_and_add_mfa_enabled_to_users.php` | MFA challenge records and `users.mfa_enabled`. |
| 012 | `2026_06_01_000012_create_security_rules_table.php` | Configurable IDS/security thresholds and actions. |
| 013 | `2026_06_01_000013_expand_intrusion_events_schema.php` | Links IDS events to security rules and keeps compatibility with older local databases. |
| 015 | `2026_06_01_000015_create_input_attack_logs_and_intrusion_alerts_tables.php` | Input attack logs, dashboard/email intrusion alerts, and notification logs. |
| 019 | `2026_06_01_000019_create_blocked_entities_table.php` | Blocklist entries for users, IPs, MAC addresses, and devices. |
| 024 | `2026_06_01_000024_create_variant_inventory_tables.php` | Brands, product variants, inventory stocks, stock receipts, inventory movements, and stock alerts. |
| 028 | `2026_06_01_000028_add_variant_snapshots_to_transaction_items_table.php` | Variant link and sold-item snapshot fields for transaction line items. |
| 029 | `2026_09_06_000029_add_transaction_query_indexes.php` | Composite transaction reporting indexes. |
| 030 | `2026_09_09_000030_enforce_duplicate_session_rule.php` | Enforces duplicate-session blocking. |
| 031 | `2026_09_09_000031_normalize_duplicate_session_threshold.php` | Normalizes the duplicate-session threshold. |
| 032 | `2026_09_14_000032_add_document_sequences_and_checkout_idempotency.php` | Adds locked document sequences and checkout-token uniqueness. |
| 033 | `2026_09_14_000033_create_customers_table.php` | Customer and credit-setting foundation. |
| 034 | `2026_09_15_000034_add_credit_sales_foundation.php` | Customer transaction links, credit-sale terms, and payment statuses. |
| 035 | `2026_09_15_000035_create_transaction_payments_table.php` | Multi-payment ledger and safe legacy-payment backfill. |
| 036 | `2026_09_16_000036_add_credit_account_payment_method.php` | Adds Credit Sale terminology while preserving Credit Card codes and records. |

## Similar Tables That Are Not Duplicates

| Table | Used By | Why It Exists |
| --- | --- | --- |
| `sessions` | Laravel | Stores the actual session payload when `SESSION_DRIVER=database`. |
| `user_sessions` | Acry-Link IDS | Stores login device/IP/user-agent history for duplicate-session and session anomaly detection. |

Do not remove `sessions` while `.env` uses `SESSION_DRIVER=database`.
Do not remove `user_sessions` while session-layer IDS middleware is enabled.

## What Was Cleaned

The old migration chain contained temporary steps such as:

- adding a column in one file and removing it in a later file
- creating legacy product stock columns and later moving them to variants
- adding product-based inventory movement rows and later converting them to variant-based rows
- creating old intrusion alert columns named `title` and `message`, then replacing them with `alert_title` and `alert_message`
- historical backfills that are only needed for older databases

Those final structures now live directly in the active create migrations above,
so a fresh database is easier to understand.

## Naming Rule For New Migrations

Use the implementation date and the next available sequence number:

```text
YYYY_MM_DD_000037_describe_the_change_here.php
```

Keep names short and describe the table or feature being changed.

Migration 038 (`2026_09_25_000038_restrict_available_payment_methods.php`) limits available methods to Cash, GCash, Credit Sale, and Check; historical references remain intact.
