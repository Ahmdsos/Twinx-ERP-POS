<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounting\Enums\AccountType;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\FiscalYear;

/**
 * ChartOfAccountsSeeder - Creates default Chart of Accounts
 * 
 * This seeder creates a comprehensive chart of accounts suitable
 * for a trading/distribution business.
 * 
 * Run with: php artisan db:seed --class=ChartOfAccountsSeeder
 */
class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create current fiscal year first
        $this->createFiscalYear();

        // Create Chart of Accounts by type
        $this->createAssets();
        $this->createLiabilities();
        $this->createEquity();
        $this->createRevenue();
        $this->createExpenses();

        $this->command->info('Chart of Accounts created successfully!');
    }

    protected function createFiscalYear(): void
    {
        FiscalYear::firstOrCreate(
            ['name' => date('Y')],
            [
                'start_date' => date('Y-01-01'),
                'end_date' => date('Y-12-31'),
                'is_active' => true,
                'is_closed' => false,
            ]
        );
    }

    protected function createAssets(): void
    {
        // Current Assets - Header
        $currentAssets = $this->createAccount('1000', 'Current Assets', 'الأصول المتداولة', AccountType::ASSET, null, true, true);

        // Cash and Bank
        $cashBank = $this->createAccount('1100', 'Cash and Bank', 'النقدية والبنوك', AccountType::ASSET, $currentAssets->id, true);
        $this->createAccount('1101', 'Cash on Hand', 'نقدية بالصندوق', AccountType::ASSET, $cashBank->id, false, true);
        $this->createAccount('1102', 'Petty Cash', 'عهدة نقدية', AccountType::ASSET, $cashBank->id);
        $this->createAccount('1110', 'Bank - Main Account', 'البنك - الحساب الرئيسي', AccountType::ASSET, $cashBank->id, false, true);
        $this->createAccount('1111', 'Bank - USD Account', 'البنك - حساب دولاري', AccountType::ASSET, $cashBank->id);

        // Receivables
        $receivables = $this->createAccount('1200', 'Receivables', 'المدينون والذمم', AccountType::ASSET, $currentAssets->id, true);
        $this->createAccount('1201', 'Accounts Receivable', 'العملاء / المدينون', AccountType::ASSET, $receivables->id, false, true);
        $this->createAccount('1202', 'Pending Delivery Receivable', 'مستحقات توصيل معلقة', AccountType::ASSET, $receivables->id);
        $this->createAccount('1210', 'Employee Advances', 'سلف موظفين', AccountType::ASSET, $receivables->id);
        $this->createAccount('1220', 'Other Receivables', 'أرصدة مدينة أخرى', AccountType::ASSET, $receivables->id);

        // Inventory
        $inventory = $this->createAccount('1300', 'Inventory', 'المخزون', AccountType::ASSET, $currentAssets->id, true);
        $this->createAccount('1301', 'Merchandise Inventory', 'مخزون بضاعة', AccountType::ASSET, $inventory->id, false, true);
        $this->createAccount('1310', 'Inventory in Transit', 'مخزون بالطريق', AccountType::ASSET, $inventory->id);

        // Fixed Assets
        $fixedAssets = $this->createAccount('1500', 'Fixed Assets', 'الأصول الثابتة', AccountType::ASSET, null, true, true);

        $ppe = $this->createAccount('1510', 'Property, Plant & Equipment', 'العقارات والآلات والمعدات', AccountType::ASSET, $fixedAssets->id, true);
        $this->createAccount('1511', 'Furniture & Fixtures', 'أثاث وتجهيزات', AccountType::ASSET, $ppe->id);
        $this->createAccount('1512', 'Computer Equipment', 'أجهزة كمبيوتر', AccountType::ASSET, $ppe->id);
        $this->createAccount('1513', 'Vehicles', 'وسائل نقل وانتقال', AccountType::ASSET, $ppe->id);

        $depreciation = $this->createAccount('1590', 'Accumulated Depreciation', 'مجمع الإهلاك', AccountType::ASSET, $fixedAssets->id, true);
        $this->createAccount('1591', 'Accum. Depr. - Furniture', 'مجمع إهلاك أثاث', AccountType::ASSET, $depreciation->id);
        $this->createAccount('1592', 'Accum. Depr. - Computers', 'مجمع إهلاك كمبيوتر', AccountType::ASSET, $depreciation->id);
        $this->createAccount('1593', 'Accum. Depr. - Vehicles', 'مجمع إهلاك سيارات', AccountType::ASSET, $depreciation->id);
    }

    protected function createLiabilities(): void
    {
        // Current Liabilities
        $currentLiabilities = $this->createAccount('2000', 'Current Liabilities', 'الالتزامات المتداولة', AccountType::LIABILITY, null, true, true);

        // Payables
        $payables = $this->createAccount('2100', 'Payables', 'الموردون والدائنون', AccountType::LIABILITY, $currentLiabilities->id, true);
        $this->createAccount('2101', 'Accounts Payable', 'الموردون', AccountType::LIABILITY, $payables->id, false, true);
        $this->createAccount('2110', 'Accrued Expenses', 'مصروفات مستحقة', AccountType::LIABILITY, $payables->id);
        $this->createAccount('2120', 'Delivery Driver Liability', 'أمانات مناديب التوصيل', AccountType::LIABILITY, $payables->id);

        // Taxes
        $taxes = $this->createAccount('2200', 'Taxes Payable', 'ضرائب مستحقة', AccountType::LIABILITY, $currentLiabilities->id, true);
        $this->createAccount('2201', 'VAT Payable', 'ضريبة القيمة المضافة - مخرجات', AccountType::LIABILITY, $taxes->id, false, true);
        $this->createAccount('2202', 'VAT Receivable', 'ضريبة القيمة المضافة - مدخلات', AccountType::LIABILITY, $taxes->id);
        $this->createAccount('2210', 'Income Tax Payable', 'ضريبة الدخل مستحقة', AccountType::LIABILITY, $taxes->id);
        $this->createAccount('2220', 'Payroll Taxes Payable', 'ضرائب كسب عمل مستحقة', AccountType::LIABILITY, $taxes->id);

        // Other
        $this->createAccount('2300', 'Customer Deposits', 'تأمينات للغير', AccountType::LIABILITY, $currentLiabilities->id);
        $this->createAccount('2400', 'Salaries Payable', 'رواتب مستحقة', AccountType::LIABILITY, $currentLiabilities->id);

        // Long-term Liabilities
        $longTermLiabilities = $this->createAccount('2500', 'Long-term Liabilities', 'الالتزامات طويلة الأجل', AccountType::LIABILITY, null, true, true);
        $this->createAccount('2510', 'Bank Loans', 'قروض بنكية', AccountType::LIABILITY, $longTermLiabilities->id);
    }

    protected function createEquity(): void
    {
        $equity = $this->createAccount('3000', 'Owner\'s Equity', 'حقوق الملكية', AccountType::EQUITY, null, true, true);

        $this->createAccount('3100', 'Paid-in Capital', 'رأس المال المدفوع', AccountType::EQUITY, $equity->id, false, true);
        $this->createAccount('3101', 'Opening Balance Equity', 'رصيد افتتاحي', AccountType::EQUITY, $equity->id, false, true);
        $this->createAccount('3200', 'Retained Earnings', 'أرباح مبقاة', AccountType::EQUITY, $equity->id, false, true);
        $this->createAccount('3300', 'Owner\'s Drawings', 'مسحوبات الشركاء', AccountType::EQUITY, $equity->id);
        $this->createAccount('3400', 'Current Year Earnings', 'صافي أرباح العام الحالي', AccountType::EQUITY, $equity->id, false, true);
    }

    protected function createRevenue(): void
    {
        $revenue = $this->createAccount('4000', 'Revenue', 'الإيرادات', AccountType::REVENUE, null, true, true);

        // Sales Revenue
        $sales = $this->createAccount('4100', 'Sales Revenue', 'إيرادات المبيعات', AccountType::REVENUE, $revenue->id, true);
        $this->createAccount('4101', 'Product Sales', 'مبيعات بضاعة', AccountType::REVENUE, $sales->id, false, true);
        $this->createAccount('4102', 'Service Revenue', 'إيرادات خدمات', AccountType::REVENUE, $sales->id);
        $this->createAccount('4103', 'Shipping & Delivery Revenue', 'إيرادات شحن وتوصيل', AccountType::REVENUE, $sales->id);
        $this->createAccount('4110', 'Sales Returns', 'مرتجعات مبيعات', AccountType::REVENUE, $sales->id);
        $this->createAccount('4120', 'Sales Discounts', 'خصومات مبيعات', AccountType::REVENUE, $sales->id);

        // Other Income
        $otherIncome = $this->createAccount('4200', 'Other Income', 'إيرادات أخرى', AccountType::REVENUE, $revenue->id, true);
        $this->createAccount('4201', 'Interest Income', 'إيرادات فوائد', AccountType::REVENUE, $otherIncome->id);
        $this->createAccount('4202', 'Foreign Exchange Gain', 'أرباح فروق عملة', AccountType::REVENUE, $otherIncome->id);
        $this->createAccount('4203', 'Miscellaneous Income', 'إيرادات متنوعة', AccountType::REVENUE, $otherIncome->id);
    }

    protected function createExpenses(): void
    {
        $expenses = $this->createAccount('5000', 'Expenses', 'المصروفات', AccountType::EXPENSE, null, true, true);

        // Cost of Sales
        $costOfSales = $this->createAccount('5100', 'Cost of Sales', 'تكلفة المبيعات', AccountType::EXPENSE, $expenses->id, true);
        $this->createAccount('5101', 'Cost of Goods Sold', 'تكلفة البضاعة المباعة', AccountType::EXPENSE, $costOfSales->id, false, true);
        $this->createAccount('5110', 'Purchase Returns', 'مرتجعات مشتريات', AccountType::EXPENSE, $costOfSales->id);
        $this->createAccount('5120', 'Purchase Discounts', 'خصومات مشتريات', AccountType::EXPENSE, $costOfSales->id);
        $this->createAccount('5130', 'Freight In', 'شحن ومصاريف نقل للداخل', AccountType::EXPENSE, $costOfSales->id);

        // Operating Expenses
        $operating = $this->createAccount('5200', 'Operating Expenses', 'مصروفات تشغيلية', AccountType::EXPENSE, $expenses->id, true);

        // Salaries & Benefits
        $salaries = $this->createAccount('5210', 'Salaries & Benefits', 'الرواتب والمزايا', AccountType::EXPENSE, $operating->id, true);
        $this->createAccount('5211', 'Salaries Expense', 'مصروف الرواتب', AccountType::EXPENSE, $salaries->id);
        $this->createAccount('5212', 'Commissions Expense', 'مصروف عمولات', AccountType::EXPENSE, $salaries->id);
        $this->createAccount('5213', 'Benefits Expense', 'مصروف مزايا موظفين', AccountType::EXPENSE, $salaries->id);
        $this->createAccount('5214', 'Payroll Tax Expense', 'مصروف ضرائب الرواتب', AccountType::EXPENSE, $salaries->id);

        // Office & Admin
        $office = $this->createAccount('5220', 'Office & Administrative', 'مصروفات إدارية وعمومية', AccountType::EXPENSE, $operating->id, true);
        $this->createAccount('5221', 'Rent Expense', 'مصروف الإيجار', AccountType::EXPENSE, $office->id);
        $this->createAccount('5222', 'Utilities Expense', 'مصروف مرافق (كهرباء ومياه)', AccountType::EXPENSE, $office->id);
        $this->createAccount('5223', 'Office Supplies', 'أدوات مكتبية', AccountType::EXPENSE, $office->id);
        $this->createAccount('5224', 'Telephone & Internet', 'هاتف وإنترنت', AccountType::EXPENSE, $office->id);
        $this->createAccount('5225', 'Insurance Expense', 'مصروف تأمين', AccountType::EXPENSE, $office->id);

        // Marketing
        $marketing = $this->createAccount('5230', 'Marketing & Sales', 'مصروفات بيعية وتسويقية', AccountType::EXPENSE, $operating->id, true);
        $this->createAccount('5231', 'Advertising Expense', 'مصروف إعلان', AccountType::EXPENSE, $marketing->id);
        $this->createAccount('5232', 'Marketing Expense', 'مصروف تسويق', AccountType::EXPENSE, $marketing->id);
        $this->createAccount('5233', 'Travel Expense', 'مصروفات سفر وانتقال', AccountType::EXPENSE, $marketing->id);

        // Other
        $this->createAccount('5240', 'Depreciation Expense', 'مصروف الإهلاك', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5250', 'Bank Charges', 'مصاريف بنكية', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5260', 'Professional Fees', 'أتعاب مهنية', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5270', 'Delivery Expense', 'مصاريف شحن وتوصيل', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5280', 'Maintenance & Repairs', 'صيانة وإصلاحات', AccountType::EXPENSE, $operating->id);

        // Inventory Adjustments (Missing accounts fixed)
        $this->createAccount('5201', 'Inventory Adjustments', 'تسويات الجرد', AccountType::EXPENSE, $operating->id, false, true);
        $this->createAccount('5202', 'Inventory - Other', 'تسويات مخزون أخرى', AccountType::EXPENSE, $operating->id, false, true);

        // Other Expenses
        $otherExpenses = $this->createAccount('5300', 'Other Expenses', 'مصروفات أخرى', AccountType::EXPENSE, $expenses->id, true);
        $this->createAccount('5301', 'Interest Expense', 'مصروف فوائد', AccountType::EXPENSE, $otherExpenses->id);
        $this->createAccount('5302', 'Foreign Exchange Loss', 'خسائر فروق عملة', AccountType::EXPENSE, $otherExpenses->id);
        $this->createAccount('5303', 'Miscellaneous Expense', 'مصروفات متنوعة', AccountType::EXPENSE, $otherExpenses->id);
    }

    /**
     * Helper to create an account
     */
    protected function createAccount(
        string $code,
        string $name,
        string $nameAr,
        AccountType $type,
        ?int $parentId = null,
        bool $isHeader = false,
        bool $isSystem = false
    ): Account {
        return Account::updateOrCreate(['code' => $code], [
            'name' => $name,
            'name_ar' => $nameAr,
            'type' => $type,
            'parent_id' => $parentId,
            'is_header' => $isHeader,
            'is_system' => $isSystem,
            'is_active' => true,
        ]);
    }
}
