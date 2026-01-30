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
            ['group' => 'email', 'key' => 'email_from_address', 'value' => 'noreply@twinx.local', 'type' => 'string'],
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
