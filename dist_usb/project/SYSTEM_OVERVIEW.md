# Twinx ERP — System Overview

## Detected Stack

| Component | Value |
|---|---|
| Framework | **Laravel 12** (`laravel/framework ^12.0`) |
| PHP Version | **≥ 8.2** |
| Frontend Build | **Vite 7 + TailwindCSS 4** (`laravel-vite-plugin ^2.0`) |
| CSS Framework | TailwindCSS v4 via `@tailwindcss/vite` plugin |
| Node Required? | **YES** — Vite build is mandatory for front-end assets |
| Database (current) | **SQLite** (`database/database.sqlite`, ~10 MB) |
| Database (target) | **MySQL / MariaDB** (config already present in `config/database.php`) |
| Modules System | `nwidart/laravel-modules ^12.0` |
| Auth/Permissions | `spatie/laravel-permission ^6.24`, `laravel/sanctum ^4.2` |
| Desktop/Electron | `nativephp/electron ^1.3` (dev/packaging only) |
| Spreadsheets | `maatwebsite/excel ^3.1` |

## Entrypoints

- **Web**: `public/index.php` (standard Laravel)
- **CLI**: `artisan` (project root)
- **Vite dev**: `npm run dev` → Vite HMR server
- **Composer dev**: `composer dev` → runs artisan serve + queue + pail + Vite concurrently

## Modules (9 total, under `Modules/`)

| Module | Purpose |
|---|---|
| Accounting | Chart of accounts, journals, ledgers |
| Auth | Authentication & authorization |
| Core | Dashboard, settings, shared components |
| Finance | Treasury, expenses, payment vouchers |
| HR | Employees, payroll, leaves, advances |
| Inventory | Products, warehouses, stock movements |
| Purchasing | Suppliers, purchase orders, GRNs |
| Reporting | Reports & analytics |
| Sales | Customers, invoices, POS, quotations, delivery |

> **Note**: `modules_statuses.json` currently only enables `HR`. All modules are loaded via `nwidart/laravel-modules` autoloader.

## Dependencies

### PHP (Composer)
- Production: `laravel/framework`, `laravel/sanctum`, `laravel/tinker`, `maatwebsite/excel`, `nativephp/electron`, `nwidart/laravel-modules`, `spatie/laravel-permission`
- Dev-only: `barryvdh/laravel-ide-helper`, `fakerphp/faker`, `laravel/pail`, `laravel/pint`, `laravel/sail`, `mockery/mockery`, `nunomaduro/collision`, `phpunit/phpunit`

### Required PHP Extensions (for MySQL + Excel)
`pdo_mysql`, `pdo_sqlite` (optional, for fallback), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd` or `imagick`, `zip`, `fileinfo`

### Node / npm
- `vite ^7.0.7`, `laravel-vite-plugin ^2.0`, `@tailwindcss/vite ^4.0`, `tailwindcss ^4.0`, `axios ^1.11`, `concurrently ^9.0.1`

## Key Folders

| Folder | Purpose |
|---|---|
| `app/` | Core Laravel app (Models, Controllers, Services, Exports, etc.) |
| `Modules/` | nwidart modular structure (9 modules) |
| `config/` | Laravel + ERP config (`erp.php`, `modules.php`, etc.) |
| `routes/` | `web.php`, `api.php`, `console.php` |
| `database/migrations/` | 64 migration files |
| `database/seeders/` | 9 seeder classes |
| `resources/` | Blade views, CSS, JS source |
| `public/` | Web root — includes pre-built assets in `public/build/` |
| `storage/` | Logs (~10MB), cache, framework temp |
| `stubs/` | nwidart module stubs |
| `vendor/` | Composer dependencies (gitignored) |
| `node_modules/` | npm dependencies (gitignored) |

## Root-Level Debug / One-Off Scripts (JUNK — candidates for removal)

| File | Type |
|---|---|
| `audit_accounts.php` | Debug script |
| `check_columns.php` | Debug script |
| `check_db_balance.php` | Debug script |
| `check_truth.php` | Debug script |
| `check_user.php` | Debug script |
| `debug_stock.php` | Debug script |
| `dump_invoice.php` | Debug script |
| `recalc_verify.php` | Debug script |
| `test-system.php` | Test script |
| `test_full_erp_cycle.php` | Test script |
| `test_pos_e2e.php` | Test script |
| `verify_integrity.php` | Verification script |
| `verify_inventory_write_path.php` | Verification script |
| `verify_phase3_fix.php` | Verification script |
| `verify_pos_hardening.php` | Verification script |
| `verify_precision.php` | Verification script |
| `verify_shift_lifecycle.php` | Verification script |
| `add_account_translations.cjs` | One-off Node script |
| `batch_fix_blade.cjs` | One-off Node script |
| `batch_fix_blade.js` | One-off Node script |
| `fix_theme_regressions.js` | One-off Node script |
| `update_translations.js` | One-off Node script |

## Current Database

- **SQLite** file: `database/database.sqlite` (~10 MB)
- **NativePHP SQLite**: `database/nativephp.sqlite` (~260 KB) + WAL/SHM files
- Migration to MySQL: config already supports it, just needs `.env` switch
- 2 migrations reference SQLite-specific logic (index checking in `add_performance_indexes.php`)

## Build State

- Pre-built Vite assets exist in `public/build/` (`app-CKl8NZMC.js`, `app-DuFNn03-.css`, `manifest.json`)
- These can be shipped for offline use without requiring Node on the client

## Risks / Unknowns

1. **`nativephp/electron`** — only needed for desktop packaging; should NOT be required for XAMPP web deployment. May cause `composer install` to run post-install scripts related to Electron.
2. **`modules_statuses.json`** — only `HR` is explicitly enabled. Other modules may auto-enable via service providers. Needs verification.
3. **Session/Cache drivers** set to `database` — requires cache + sessions tables (already in migrations).
4. **SQLite-specific index handling** in `add_performance_indexes.php` — uses a custom `indexExistsSQLite()` helper; needs MySQL-compatible path.
5. **Large log file** (`storage/logs/laravel.log` ~10MB) — should be excluded from distribution.
6. **`.license.key`** at root — appears to be app-specific licensing; review if it should be shipped.
