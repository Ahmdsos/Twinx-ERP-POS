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
            'tax_inclusive' => 'nullable|boolean',

            // Invoice settings
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_next_number' => 'nullable|integer|min:1',
            'invoice_footer' => 'nullable|string|max:500',

            // POS settings
            'pos_default_customer' => 'nullable|integer',
            'pos_allow_negative_stock' => 'nullable|boolean',
            'pos_print_receipt' => 'nullable|boolean',
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

        return redirect()->route('settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }
}
