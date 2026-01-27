# Twinx ERP

<p align="center">
  <strong>Production-Ready ERP System for MENA Region</strong>
</p>

<p align="center">
  Accounting-Driven | API-First | Modular Monolith | Mobile Ready
</p>

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.x
- MySQL 8.0+
- Node.js 18+ (for frontend assets)

### Installation

```bash
# 1. Clone the repository
git clone <repository-url> twinx-erp
cd twinx-erp

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure database in .env
# DB_DATABASE=twinx_erp
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 6. Run migrations
php artisan migrate

# 7. Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# 8. Start development server
php artisan serve
```

### Default Credentials

After seeding, you can login with:

| Email | Password | Role |
|-------|----------|------|
| admin@twinx.local | password | Super Admin |

---

## 📁 Project Structure

```
twinx-erp/
├── app/                    # Core Laravel app
├── Modules/                # ERP Modules (Modular Monolith)
│   ├── Core/               # Shared utilities, contracts, traits
│   │   ├── Contracts/      # Interfaces (AccountableContract)
│   │   ├── Traits/         # Shared traits (HasDocumentNumber, HasAuditTrail)
│   │   └── Helpers/        # Utility classes (MoneyHelper)
│   ├── Auth/               # Authentication module
│   │   ├── Http/Controllers/Api/
│   │   ├── Services/
│   │   └── routes/
│   ├── Accounting/         # (Sprint 1) Chart of Accounts, Journals
│   ├── Inventory/          # (Sprint 2) Products, Stock, Warehouses
│   ├── Sales/              # (Sprint 3-4) Quotations, Orders, Invoices
│   ├── Purchases/          # (Sprint 5) POs, GRNs, Supplier Invoices
│   ├── Delivery/           # (Sprint 6) Shipping, Tracking
│   └── Reports/            # (Sprint 7) Financial & Operational Reports
├── config/
│   ├── erp.php             # ERP-specific configuration
│   ├── sanctum.php         # API authentication
│   └── permission.php      # Roles & permissions
├── database/
│   ├── migrations/
│   └── seeders/
└── tests/
```

---

## 🔐 API Authentication

Twinx ERP uses **Laravel Sanctum** for API token authentication.

### Login

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "admin@twinx.local",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": { "id": 1, "name": "Super Admin", ... },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### Using the Token

```bash
GET /api/v1/auth/user
Authorization: Bearer 1|abc123...
```

### Available Auth Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Login & get token |
| GET | `/api/v1/auth/user` | Get current user |
| POST | `/api/v1/auth/logout` | Logout (revoke token) |
| POST | `/api/v1/auth/logout-all` | Logout all devices |
| POST | `/api/v1/auth/refresh` | Refresh token |

---

## 👥 Roles & Permissions

| Role | Description |
|------|-------------|
| `super_admin` | Full system access |
| `admin` | Administrative access (no delete users) |
| `accountant` | Accounting & financial modules |
| `sales` | Sales & customer modules |
| `purchasing` | Purchase & supplier modules |
| `warehouse` | Inventory & delivery modules |
| `delivery` | Delivery status updates only |

---

## ⚙️ Configuration

Key settings in `config/erp.php`:

```php
// Currency
'currency' => [
    'default' => 'EGP',
    'decimal_places' => 2,
],

// Inventory Costing
'inventory' => [
    'costing_method' => 'fifo', // or 'average'
],

// Document Numbering
'numbering' => [
    'sales_invoice' => ['prefix' => 'INV', 'padding' => 6],
    // INV-2026-000001
],
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## 📅 Sprint Roadmap

| Sprint | Focus | Status |
|--------|-------|--------|
| 0 | Foundation & Architecture | ✅ Complete |
| 1 | Chart of Accounts + Journal Engine | 🔜 Next |
| 2 | Products & Inventory | ⏳ Planned |
| 3-4 | Sales Module | ⏳ Planned |
| 5 | Purchase Module | ⏳ Planned |
| 6 | Delivery & Payments | ⏳ Planned |
| 7 | Reports | ⏳ Planned |
| 8 | API Layer & Mobile | ⏳ Planned |
| 9 | Security & Audit | ⏳ Planned |
| 10 | Optimization & Deployment | ⏳ Planned |

---

## 🛠️ Development Commands

```bash
# Create a new module
php artisan module:make ModuleName

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models -N

# Clear all caches
php artisan optimize:clear

# Run code formatting
./vendor/bin/pint
```

---

## 📝 License

Proprietary - All rights reserved.

---

<p align="center">
  <strong>Built with ❤️ for the MENA region</strong>
</p>
