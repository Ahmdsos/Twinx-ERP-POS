<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * SettingsSeeder - Creates default system settings
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Company Settings
            ['group' => 'company', 'key' => 'company_name', 'value' => 'شركة تونكس', 'type' => 'string'],
            ['group' => 'company', 'key' => 'company_address', 'value' => 'القاهرة، مصر', 'type' => 'string'],
            ['group' => 'company', 'key' => 'company_phone', 'value' => '01000000000', 'type' => 'string'],
            ['group' => 'company', 'key' => 'company_email', 'value' => 'info@twinx.local', 'type' => 'string'],
            ['group' => 'company', 'key' => 'company_tax_number', 'value' => '', 'type' => 'string'],

            // Tax Settings
            ['group' => 'tax', 'key' => 'default_tax_rate', 'value' => '14', 'type' => 'float'],
            ['group' => 'tax', 'key' => 'tax_inclusive', 'value' => '0', 'type' => 'boolean'],

            // Invoice Settings
            ['group' => 'invoice', 'key' => 'invoice_prefix', 'value' => 'INV-', 'type' => 'string'],
            ['group' => 'invoice', 'key' => 'invoice_next_number', 'value' => '1', 'type' => 'integer'],
            ['group' => 'invoice', 'key' => 'invoice_footer', 'value' => 'شكراً لتعاملكم معنا', 'type' => 'string'],

            // POS Settings
            ['group' => 'pos', 'key' => 'pos_allow_negative_stock', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'pos', 'key' => 'pos_print_receipt', 'value' => '1', 'type' => 'boolean'],

            // Printer Settings
            ['group' => 'printer', 'key' => 'printer_type', 'value' => 'thermal', 'type' => 'string'],
            ['group' => 'printer', 'key' => 'printer_paper_width', 'value' => '80', 'type' => 'integer'],
            ['group' => 'printer', 'key' => 'printer_auto_print', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'printer', 'key' => 'printer_show_logo', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'printer', 'key' => 'printer_copies', 'value' => '1', 'type' => 'integer'],

            // Currency Settings
            ['group' => 'currency', 'key' => 'default_currency', 'value' => 'EGP', 'type' => 'string'],
            ['group' => 'currency', 'key' => 'currency_symbol', 'value' => 'ج.م', 'type' => 'string'],
            ['group' => 'currency', 'key' => 'currency_position', 'value' => 'after', 'type' => 'string'],

            // Email Settings
            ['group' => 'email', 'key' => 'email_from_name', 'value' => 'Twinx ERP', 'type' => 'string'],
            ['group' => 'email', 'key' => 'email_from_name', 'value' => 'Twinx ERP', 'type' => 'string'],
            ['group' => 'email', 'key' => 'email_from_address', 'value' => 'noreply@twinx.local', 'type' => 'string'],

            // ═══════════════════════════════════════════════════════════════
            // Accounting Integration Defaults
            // All codes below are LEAF accounts (is_header=false) from ChartOfAccountsSeeder
            // ═══════════════════════════════════════════════════════════════

            // --- Sales & Revenue ---
            ['group' => 'accounting', 'key' => 'acc_ar', 'value' => '1201', 'type' => 'string'],              // Accounts Receivable (العملاء/المدينون) — LEAF under 1200
            ['group' => 'accounting', 'key' => 'acc_sales_revenue', 'value' => '4101', 'type' => 'string'],    // Product Sales (مبيعات بضاعة) — LEAF under 4100
            ['group' => 'accounting', 'key' => 'acc_sales_return', 'value' => '4110', 'type' => 'string'],     // Sales Returns (مرتجعات مبيعات) — LEAF under 4100
            ['group' => 'accounting', 'key' => 'acc_tax_payable', 'value' => '2201', 'type' => 'string'],      // VAT Payable - Output (ض.ق.م مخرجات) — LEAF under 2200
            ['group' => 'accounting', 'key' => 'acc_tax_receivable', 'value' => '2202', 'type' => 'string'],   // VAT Receivable - Input (ض.ق.م مدخلات) — LEAF under 2200
            ['group' => 'accounting', 'key' => 'acc_sales_discount', 'value' => '4120', 'type' => 'string'],   // Sales Discounts (خصومات مبيعات) — LEAF under 4100
            ['group' => 'accounting', 'key' => 'acc_shipping_revenue', 'value' => '4103', 'type' => 'string'], // Shipping & Delivery Revenue (إيرادات شحن) — LEAF under 4100
            ['group' => 'accounting', 'key' => 'acc_delivery_liability', 'value' => '2120', 'type' => 'string'], // Delivery Driver Liability (أمانات مناديب) — LEAF under 2100
            ['group' => 'accounting', 'key' => 'acc_pending_delivery', 'value' => '1202', 'type' => 'string'], // Pending Delivery Receivable (مستحقات توصيل معلقة) — LEAF under 1200

            // --- Purchases & Expenses ---
            ['group' => 'accounting', 'key' => 'acc_ap', 'value' => '2101', 'type' => 'string'],              // Accounts Payable (الموردون) — LEAF under 2100
            ['group' => 'accounting', 'key' => 'acc_purchase_discount', 'value' => '5120', 'type' => 'string'], // Purchase Discounts (خصومات مشتريات) — LEAF under 5100

            // --- Inventory & COGS ---
            ['group' => 'accounting', 'key' => 'acc_inventory', 'value' => '1301', 'type' => 'string'],       // Merchandise Inventory (مخزون بضاعة) — LEAF under 1300
            ['group' => 'accounting', 'key' => 'acc_cogs', 'value' => '5101', 'type' => 'string'],            // Cost of Goods Sold (تكلفة البضاعة المباعة) — LEAF under 5100
            ['group' => 'accounting', 'key' => 'acc_inventory_adj', 'value' => '5201', 'type' => 'string'],   // Inventory Adjustments (تسويات الجرد) — LEAF under 5200
            ['group' => 'accounting', 'key' => 'acc_purchase_suspense', 'value' => '2101', 'type' => 'string'], // Purchase Suspense → AP (الموردون) — LEAF under 2100
            ['group' => 'accounting', 'key' => 'acc_inventory_other', 'value' => '5202', 'type' => 'string'], // Other Inventory Differences (تسويات مخزون أخرى) — LEAF under 5200

            // --- Payment Methods & Cash ---
            ['group' => 'accounting', 'key' => 'acc_cash', 'value' => '1101', 'type' => 'string'],            // Cash on Hand (نقدية بالصندوق) — LEAF under 1100
            ['group' => 'accounting', 'key' => 'acc_bank', 'value' => '1110', 'type' => 'string'],            // Bank Main Account (البنك الرئيسي) — LEAF under 1100
            ['group' => 'accounting', 'key' => 'acc_pos_change', 'value' => '1101', 'type' => 'string'],      // POS Change → same as Cash (نقدية بالصندوق) — LEAF under 1100

            // --- HR & Payroll ---
            ['group' => 'accounting', 'key' => 'acc_salaries_exp', 'value' => '5211', 'type' => 'string'],    // Salaries Expense (مصروف الرواتب) — LEAF under 5210
            ['group' => 'accounting', 'key' => 'acc_salaries_payable', 'value' => '2400', 'type' => 'string'], // Salaries Payable (رواتب مستحقة) — LEAF under 2000
            ['group' => 'accounting', 'key' => 'acc_employee_advances', 'value' => '1210', 'type' => 'string'], // Employee Advances (سلف موظفين) — LEAF under 1200

            // --- System ---
            ['group' => 'accounting', 'key' => 'acc_opening_balance', 'value' => '3101', 'type' => 'string'], // Opening Balance Equity (رصيد افتتاحي) — LEAF under 3000
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Default settings created!');
    }
}
