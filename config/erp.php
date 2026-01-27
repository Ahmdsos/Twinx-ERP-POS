<?php

/**
 * Twinx ERP Configuration
 * 
 * Central configuration file for all ERP-specific settings.
 * These settings control business logic, accounting rules, and system behavior.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */
    'name' => env('ERP_NAME', 'Twinx ERP'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'default' => env('ERP_DEFAULT_CURRENCY', 'EGP'),
        'decimal_places' => env('ERP_DECIMAL_PLACES', 2),
        'thousand_separator' => ',',
        'decimal_separator' => '.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fiscal Year Settings
    |--------------------------------------------------------------------------
    */
    'fiscal_year' => [
        'start_month' => env('ERP_FISCAL_YEAR_START_MONTH', 1),  // January
        'start_day' => env('ERP_FISCAL_YEAR_START_DAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Inventory Settings
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        // Costing method: 'fifo' or 'average'
        'costing_method' => env('ERP_COSTING_METHOD', 'fifo'),

        // Allow negative stock (backorders)
        'allow_negative_stock' => env('ERP_ALLOW_NEGATIVE_STOCK', false),

        // Low stock threshold percentage
        'low_stock_threshold' => env('ERP_LOW_STOCK_THRESHOLD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Accounting Settings
    |--------------------------------------------------------------------------
    */
    'accounting' => [
        // Auto-post journal entries (false = require manual posting)
        'auto_post_journals' => env('ERP_AUTO_POST_JOURNALS', false),

        // Journal entry number prefix
        'journal_prefix' => env('ERP_JOURNAL_PREFIX', 'JE'),

        // Require narration/description for journal entries
        'require_journal_narration' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Numbering
    |--------------------------------------------------------------------------
    | Prefixes and formats for various document types
    */
    'numbering' => [
        'journal_entry' => ['prefix' => 'JE', 'padding' => 6],
        'quotation' => ['prefix' => 'QT', 'padding' => 6],
        'sales_order' => ['prefix' => 'SO', 'padding' => 6],
        'sales_invoice' => ['prefix' => 'INV', 'padding' => 6],
        'purchase_order' => ['prefix' => 'PO', 'padding' => 6],
        'grn' => ['prefix' => 'GRN', 'padding' => 6],
        'purchase_invoice' => ['prefix' => 'PI', 'padding' => 6],
        'delivery_order' => ['prefix' => 'DO', 'padding' => 6],
        'payment_receipt' => ['prefix' => 'RV', 'padding' => 6],
        'payment_voucher' => ['prefix' => 'PV', 'padding' => 6],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'rate_limit' => env('API_RATE_LIMIT', 60),
        'rate_limit_window' => env('API_RATE_LIMIT_WINDOW', 1), // minutes
        'pagination_limit' => env('API_PAGINATION_LIMIT', 25),
        'max_pagination_limit' => env('API_MAX_PAGINATION_LIMIT', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    | Roles that will be seeded during initial setup
    */
    'roles' => [
        'super_admin' => [
            'name' => 'Super Admin',
            'description' => 'Full system access',
        ],
        'admin' => [
            'name' => 'Admin',
            'description' => 'Administrative access with some restrictions',
        ],
        'accountant' => [
            'name' => 'Accountant',
            'description' => 'Access to accounting and financial modules',
        ],
        'sales' => [
            'name' => 'Sales',
            'description' => 'Access to sales and customer modules',
        ],
        'purchasing' => [
            'name' => 'Purchasing',
            'description' => 'Access to purchase and supplier modules',
        ],
        'warehouse' => [
            'name' => 'Warehouse',
            'description' => 'Access to inventory and stock modules',
        ],
        'delivery' => [
            'name' => 'Delivery',
            'description' => 'Access to delivery and shipping modules',
        ],
    ],

];
