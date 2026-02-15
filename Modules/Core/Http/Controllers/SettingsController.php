<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Core\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * SettingsController
 * Manages system-wide settings (Company, Tax, Invoice, POS)
 */
class SettingsController extends Controller
{
    /**
     * Show settings page grouped by category
     */
    public function index()
    {
        $settings = [
            'company' => Setting::getGroup('company'),
            'general' => Setting::getGroup('general'),
            'tax' => Setting::getGroup('tax'),
            'invoice' => Setting::getGroup('invoice'),
            'pos' => Setting::getGroup('pos'),
            'printer' => Setting::getGroup('printer'),
            'currency' => Setting::getGroup('currency'),
            'email' => Setting::getGroup('email'),
            'backup' => Setting::getGroup('backup'),
            'accounting' => Setting::getGroup('accounting'),
        ];

        // Fetch all non-header accounts for mapping
        $accounts = \Modules\Accounting\Models\Account::active()->postable()->orderBy('code')->get();

        return view('settings.index', compact('settings', 'accounts'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Company settings
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_tax_number' => 'nullable|string|max:50',
            'company_logo' => 'nullable|image|mimes:jpeg,png,gif|max:2048',

            // Language
            'app_language' => 'nullable|in:ar,en',

            // Tax settings
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            // 'tax_inclusive' => 'nullable|boolean', // Removed strict check

            // Invoice settings
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_next_number' => 'nullable|integer|min:1',
            'invoice_footer' => 'nullable|string|max:500',

            // POS settings
            'pos_default_customer' => 'nullable|integer',
            // 'pos_allow_negative_stock' => 'nullable|boolean', // Removed strict check
            // 'pos_print_receipt' => 'nullable|boolean', // Removed strict check

            // Printer settings
            'printer_type' => 'nullable|in:thermal,a4,a5',
            'printer_paper_width' => 'nullable|integer|min:58|max:80',
            // 'printer_auto_print' => 'nullable|boolean', // Removed strict check
            // 'printer_show_logo' => 'nullable|boolean', // Removed strict check
            'printer_copies' => 'nullable|integer|min:1|max:5',

            // Receipt Customization
            'pos_receipt_header_custom' => 'nullable|string|max:1000',
            'pos_receipt_footer_custom' => 'nullable|string|max:1000',
            'pos_receipt_qr_link' => 'nullable|string|max:255',

            // POS Security
            'pos_refund_pin' => 'nullable|string|max:10',
            'pos_manager_pin' => 'nullable|string|max:10',
            'pos_max_discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company', 'public');
            Setting::setValue('company_logo', $path, 'company');
        }

        // Save company settings
        Setting::setValue('company_name', $validated['company_name'] ?? '', 'company');
        Setting::setValue('company_address', $validated['company_address'] ?? '', 'company');
        Setting::setValue('company_phone', $validated['company_phone'] ?? '', 'company');
        Setting::setValue('company_email', $validated['company_email'] ?? '', 'company');
        Setting::setValue('company_tax_number', $validated['company_tax_number'] ?? '', 'company');

        // Save language setting
        Setting::setValue('app_language', $validated['app_language'] ?? 'ar', 'general');

        // Save tax settings
        Setting::setValue('default_tax_rate', $validated['default_tax_rate'] ?? 14, 'tax');
        Setting::setValue('tax_inclusive', $request->has('tax_inclusive'), 'tax');

        // Save invoice settings
        Setting::setValue('invoice_prefix', $validated['invoice_prefix'] ?? 'INV', 'invoice');
        Setting::setValue('invoice_next_number', $validated['invoice_next_number'] ?? 1, 'invoice');
        Setting::setValue('invoice_footer', $validated['invoice_footer'] ?? '', 'invoice');

        // Save POS settings
        Setting::setValue('pos_default_customer', $validated['pos_default_customer'] ?? null, 'pos');
        Setting::setValue('pos_allow_negative_stock', $request->has('pos_allow_negative_stock'), 'pos');
        Setting::setValue('pos_print_receipt', $request->has('pos_print_receipt'), 'pos');
        Setting::setValue('pos_refund_pin', $validated['pos_refund_pin'] ?? '1234', 'pos');
        // Phase 3: Security settings
        Setting::setValue('pos_manager_pin', $validated['pos_manager_pin'] ?? '', 'pos');
        Setting::setValue('pos_max_discount_percent', $validated['pos_max_discount_percent'] ?? 50, 'pos');

        // Save Printer settings
        Setting::setValue('printer_type', $validated['printer_type'] ?? 'thermal', 'printer');
        Setting::setValue('printer_paper_width', $validated['printer_paper_width'] ?? 80, 'printer');
        Setting::setValue('printer_auto_print', $request->has('printer_auto_print'), 'printer');
        Setting::setValue('printer_show_logo', $request->has('printer_show_logo'), 'printer');
        Setting::setValue('printer_copies', $validated['printer_copies'] ?? 1, 'printer');

        // Save Receipt Customization
        Setting::setValue('pos_receipt_header_custom', $validated['pos_receipt_header_custom'] ?? '', 'printer');
        Setting::setValue('pos_receipt_footer_custom', $validated['pos_receipt_footer_custom'] ?? '', 'printer');
        Setting::setValue('pos_receipt_qr_link', $validated['pos_receipt_qr_link'] ?? '', 'printer');
        Setting::setValue('pos_receipt_qr_enabled', $request->has('pos_receipt_qr_enabled'), 'printer');

        // Save Currency settings
        Setting::setValue('currency_code', $request->input('currency_code', 'EGP'), 'currency');
        Setting::setValue('currency_symbol', $request->input('currency_symbol', 'ج.م'), 'currency');
        Setting::setValue('currency_decimals', $request->input('currency_decimals', 2), 'currency');
        Setting::setValue('currency_decimal_separator', $request->input('currency_decimal_separator', '.'), 'currency');
        Setting::setValue('currency_thousands_separator', $request->input('currency_thousands_separator', ','), 'currency');

        // Save Email settings
        Setting::setValue('email_smtp_host', $request->input('email_smtp_host', ''), 'email');
        Setting::setValue('email_smtp_port', $request->input('email_smtp_port', 587), 'email');
        Setting::setValue('email_username', $request->input('email_username', ''), 'email');
        Setting::setValue('email_password', $request->input('email_password', ''), 'email');
        Setting::setValue('email_from_name', $request->input('email_from_name', ''), 'email');
        Setting::setValue('email_encryption', $request->input('email_encryption', 'tls'), 'email');

        // Save Backup settings
        Setting::setValue('backup_frequency', $request->input('backup_frequency', 'daily'), 'backup');
        Setting::setValue('backup_keep_count', $request->input('backup_keep_count', 7), 'backup');
        Setting::setValue('backup_path', $request->input('backup_path', 'backups'), 'backup');
        Setting::setValue('backup_notify', $request->has('backup_notify'), 'backup');

        // Save Accounting Integration settings
        $accountingKeys = [
            'acc_ar',
            'acc_ap',
            'acc_cash',
            'acc_bank',
            'acc_sales_revenue',
            'acc_tax_payable',
            'acc_tax_receivable',
            'acc_sales_discount',
            'acc_sales_return',
            'acc_shipping_revenue',
            'acc_pending_delivery',
            'acc_tax_receivable',
            'acc_purchase_discount',
            'acc_pos_change',
            'acc_inventory',
            'acc_cogs',
            'acc_inventory_adj',
            'acc_purchase_suspense',
            'acc_inventory_other',
            'acc_salaries_exp',
            'acc_salaries_payable',
            'acc_opening_balance'
        ];

        foreach ($accountingKeys as $key) {
            if ($request->has($key)) {
                Setting::setValue($key, $request->input($key), 'accounting');
            }
        }

        // Save Delivery Accounting Method (Revenue vs Liability)
        if ($request->has('pos_delivery_accounting_method')) {
            Setting::setValue('pos_delivery_accounting_method', $request->input('pos_delivery_accounting_method'), 'accounting');
        }

        return redirect()->route('settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    /**
     * Perform a safe system reset (Wipe transactions, keep COA)
     */
    public function systemReset(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $adminPin = Setting::getValue('pos_manager_pin', 'pos') ?: Setting::getValue('pos_refund_pin', 'pos') ?: '1234';

        if ($request->pin !== $adminPin) {
            return back()->withErrors(['pin' => 'رمز التحقق (PIN) غير صحيح']);
        }

        try {
            // Disable Foreign Key Checks (MUST happen before transaction for SQLite)
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');
            } else {
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            \Illuminate\Support\Facades\DB::transaction(function () {
                $tables = [
                    'journal_entry_lines',
                    'journal_entries',

                    // Sales & Customers
                    'sales_invoice_lines',
                    'sales_invoices',  // Corrected
                    'sales_order_lines',
                    'sales_orders',      // Corrected
                    'sales_return_lines',
                    'sales_returns',    // Corrected
                    'delivery_order_lines',
                    'delivery_orders', // Wrapped in Sales
                    'quotation_lines',
                    'quotations',          // Corrected
                    'customer_payment_allocations',
                    'customer_payments',
                    'pos_held_sales',
                    'pos_shifts',
                    'customers',

                    // Purchases & Suppliers
                    'purchase_invoice_lines',
                    'purchase_invoices', // Corrected
                    'purchase_order_lines',
                    'purchase_orders',     // Corrected
                    'purchase_return_lines',
                    'purchase_returns',   // Corrected
                    'grn_lines',
                    'grns',                           // Added
                    'supplier_payment_allocations',
                    'supplier_payments',
                    'suppliers',

                    // Inventory & Products
                    'stock_movements',
                    'product_stock',
                    'product_images',
                    'product_batches',
                    'product_serials',
                    'products',
                    'brands',
                    'categories',
                    'units',  // Units might be master data but often user-defined

                    // Logistics
                    'couriers',
                    'shipments',
                    'shipment_status_histories',

                    // HR & Expenses
                    'expenses',
                    'payroll_items',
                    'payrolls',
                    'hr_leaves',
                    'hr_documents',

                    // Finance
                    'treasury_transactions',

                    // Logs
                    'activity_logs',
                    'security_audit_logs',
                    'price_override_logs',
                    'notifications',
                    'personal_access_tokens'
                ];

                foreach ($tables as $table) {
                    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                        \Illuminate\Support\Facades\DB::table($table)->truncate();
                    }
                }

                // Reset balances and stock
                \Illuminate\Support\Facades\DB::table('accounts')->update(['balance' => 0]);

                // Products table doesn't hold stock, it's in product_stock (truncated above)

                if (\Illuminate\Support\Facades\Schema::hasTable('product_batches')) {
                    \Illuminate\Support\Facades\DB::table('product_batches')->truncate();
                }

                if (\Illuminate\Support\Facades\Schema::hasTable('product_serials')) {
                    \Illuminate\Support\Facades\DB::table('product_serials')->truncate();
                }
            });

            // Re-enable Foreign Key Checks (After transaction)
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
            } else {
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }


            // Log activity
            if (class_exists('\Modules\Core\Models\ActivityLog')) {
                \Modules\Core\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'user_name' => auth()->user()?->name ?? 'Admin',
                    'action' => 'system_reset',
                    'description' => 'تم تصفير بيانات السيستم بالكامل بنجاح بواسطة المدير',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return redirect()->route('settings.index')
                ->with('success', 'تم تصفير بيانات السيستم بنجاح (مع الحفاظ على دليل الحسابات)');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'فشل التصفير: ' . $e->getMessage()]);
        }
    }
}
