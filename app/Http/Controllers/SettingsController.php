<?php

namespace App\Http\Controllers;

use App\Models\Setting;
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
            'tax' => Setting::getGroup('tax'),
            'invoice' => Setting::getGroup('invoice'),
            'pos' => Setting::getGroup('pos'),
            'printer' => Setting::getGroup('printer'),
            'currency' => Setting::getGroup('currency'),
            'email' => Setting::getGroup('email'),
            'backup' => Setting::getGroup('backup'),
        ];

        return view('settings.index', compact('settings'));
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

        // Save Printer settings
        Setting::setValue('printer_type', $validated['printer_type'] ?? 'thermal', 'printer');
        Setting::setValue('printer_paper_width', $validated['printer_paper_width'] ?? 80, 'printer');
        Setting::setValue('printer_auto_print', $request->has('printer_auto_print'), 'printer');
        Setting::setValue('printer_show_logo', $request->has('printer_show_logo'), 'printer');
        Setting::setValue('printer_copies', $validated['printer_copies'] ?? 1, 'printer');

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

        return redirect()->route('settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }
}
