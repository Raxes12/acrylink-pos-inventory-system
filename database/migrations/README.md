# Acry-Link Migration Guide

All active migrations use the same readable date prefix: `2026_06_01`.

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

Use the same prefix and the next available number:

```text
2026_06_01_000034_describe_the_change_here.php
```

Keep names short and describe the table or feature being changed.
