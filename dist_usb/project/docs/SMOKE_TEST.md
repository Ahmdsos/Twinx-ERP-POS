# Twinx ERP — Smoke Test

Minimal verification steps to confirm the application boots correctly after installation.

---

## Prerequisites
- XAMPP running (Apache + MySQL)
- Database `twinx_erp` created
- `php artisan migrate --seed` completed successfully

---

## Test 1: Application Boots

```bash
cd C:\xampp\htdocs\twinx-erp
php artisan route:list --compact
```

**Expected**: List of routes printed without errors.

---

## Test 2: Database Connection

```bash
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';"
```

**Expected**: `DB OK`

---

## Test 3: Admin User Exists

```bash
php artisan tinker --execute="echo App\Models\User::where('email','admin@local.test')->exists() ? 'PASS' : 'FAIL';"
```

**Expected**: `PASS`

---

## Test 4: Login Page Loads

1. Open browser: `http://localhost/twinx-erp/public`
2. **Expected**: Login page renders with email/password fields

---

## Test 5: Admin Login

1. Enter credentials:
   - Email: `admin@local.test`
   - Password: `Admin@12345`
2. Click Login
3. **Expected**: Redirected to Dashboard

---

## Test 6: Key Module Pages

After login, verify these pages load:

| URL Path | Expected |
|---|---|
| `/dashboard` | Dashboard with widgets |
| `/inventory/products` | Products list |
| `/sales/invoices` | Sales invoices list |
| `/accounting/accounts` | Chart of accounts |

---

## Quick CLI Smoke Test (All-in-One)

```bash
cd C:\xampp\htdocs\twinx-erp
php artisan migrate:status | findstr "Ran"
php artisan tinker --execute="echo 'DB: '.DB::connection()->getDatabaseName().PHP_EOL.'Users: '.App\Models\User::count().PHP_EOL.'Admin: '.(App\Models\User::where('email','admin@local.test')->exists()?'YES':'NO');"
```

**Expected output**:
```
DB: twinx_erp
Users: 1
Admin: YES
```

---

## ❌ If Any Test Fails

1. Check `.env` → `DB_CONNECTION=mysql`, `DB_DATABASE=twinx_erp`
2. Check MySQL is running: XAMPP Control Panel
3. Check logs: `storage\logs\laravel.log`
4. Re-run: `php artisan migrate --seed --force`
5. Clear caches: `php artisan config:clear && php artisan cache:clear`
