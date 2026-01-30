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
        FiscalYear::create([
            'name' => date('Y'),
            'start_date' => date('Y-01-01'),
            'end_date' => date('Y-12-31'),
            'is_active' => true,
            'is_closed' => false,
        ]);
    }

    protected function createAssets(): void
    {
        // Current Assets - Header
        $currentAssets = $this->createAccount('1000', 'Current Assets', AccountType::ASSET, null, true, true);

        // Cash and Bank
        $cashBank = $this->createAccount('1100', 'Cash and Bank', AccountType::ASSET, $currentAssets->id, true);
        $this->createAccount('1101', 'Cash on Hand', AccountType::ASSET, $cashBank->id, false, true);
        $this->createAccount('1102', 'Petty Cash', AccountType::ASSET, $cashBank->id);
        $this->createAccount('1110', 'Bank - Main Account', AccountType::ASSET, $cashBank->id, false, true);
        $this->createAccount('1111', 'Bank - USD Account', AccountType::ASSET, $cashBank->id);

        // Receivables
        $receivables = $this->createAccount('1200', 'Receivables', AccountType::ASSET, $currentAssets->id, true);
        $this->createAccount('1201', 'Accounts Receivable', AccountType::ASSET, $receivables->id, false, true);
        $this->createAccount('1210', 'Employee Advances', AccountType::ASSET, $receivables->id);
        $this->createAccount('1220', 'Other Receivables', AccountType::ASSET, $receivables->id);

        // Inventory
        $inventory = $this->createAccount('1300', 'Inventory', AccountType::ASSET, $currentAssets->id, true);
        $this->createAccount('1301', 'Merchandise Inventory', AccountType::ASSET, $inventory->id, false, true);
        $this->createAccount('1310', 'Inventory in Transit', AccountType::ASSET, $inventory->id);

        // Fixed Assets
        $fixedAssets = $this->createAccount('1500', 'Fixed Assets', AccountType::ASSET, null, true, true);

        $ppe = $this->createAccount('1510', 'Property, Plant & Equipment', AccountType::ASSET, $fixedAssets->id, true);
        $this->createAccount('1511', 'Furniture & Fixtures', AccountType::ASSET, $ppe->id);
        $this->createAccount('1512', 'Computer Equipment', AccountType::ASSET, $ppe->id);
        $this->createAccount('1513', 'Vehicles', AccountType::ASSET, $ppe->id);

        $depreciation = $this->createAccount('1590', 'Accumulated Depreciation', AccountType::ASSET, $fixedAssets->id, true);
        $this->createAccount('1591', 'Accum. Depr. - Furniture', AccountType::ASSET, $depreciation->id);
        $this->createAccount('1592', 'Accum. Depr. - Computers', AccountType::ASSET, $depreciation->id);
        $this->createAccount('1593', 'Accum. Depr. - Vehicles', AccountType::ASSET, $depreciation->id);
    }

    protected function createLiabilities(): void
    {
        // Current Liabilities
        $currentLiabilities = $this->createAccount('2000', 'Current Liabilities', AccountType::LIABILITY, null, true, true);

        // Payables
        $payables = $this->createAccount('2100', 'Payables', AccountType::LIABILITY, $currentLiabilities->id, true);
        $this->createAccount('2101', 'Accounts Payable', AccountType::LIABILITY, $payables->id, false, true);
        $this->createAccount('2110', 'Accrued Expenses', AccountType::LIABILITY, $payables->id);

        // Taxes
        $taxes = $this->createAccount('2200', 'Taxes Payable', AccountType::LIABILITY, $currentLiabilities->id, true);
        $this->createAccount('2201', 'VAT Payable', AccountType::LIABILITY, $taxes->id, false, true);
        $this->createAccount('2202', 'VAT Receivable', AccountType::LIABILITY, $taxes->id);
        $this->createAccount('2210', 'Income Tax Payable', AccountType::LIABILITY, $taxes->id);
        $this->createAccount('2220', 'Payroll Taxes Payable', AccountType::LIABILITY, $taxes->id);
        // GRN Clearing Account - Intermediate liability for received goods without invoice
        $this->createAccount('2120', 'GRN Clearing', AccountType::LIABILITY, $payables->id);

        // Other
        $this->createAccount('2300', 'Customer Deposits', AccountType::LIABILITY, $currentLiabilities->id);
        $this->createAccount('2400', 'Salaries Payable', AccountType::LIABILITY, $currentLiabilities->id);

        // Long-term Liabilities
        $longTermLiabilities = $this->createAccount('2500', 'Long-term Liabilities', AccountType::LIABILITY, null, true, true);
        $this->createAccount('2510', 'Bank Loans', AccountType::LIABILITY, $longTermLiabilities->id);
    }

    protected function createEquity(): void
    {
        $equity = $this->createAccount('3000', 'Owner\'s Equity', AccountType::EQUITY, null, true, true);

        $this->createAccount('3100', 'Paid-in Capital', AccountType::EQUITY, $equity->id, false, true);
        $this->createAccount('3200', 'Retained Earnings', AccountType::EQUITY, $equity->id, false, true);
        $this->createAccount('3300', 'Owner\'s Drawings', AccountType::EQUITY, $equity->id);
        $this->createAccount('3400', 'Current Year Earnings', AccountType::EQUITY, $equity->id, false, true);
    }

    protected function createRevenue(): void
    {
        $revenue = $this->createAccount('4000', 'Revenue', AccountType::REVENUE, null, true, true);

        // Sales Revenue
        $sales = $this->createAccount('4100', 'Sales Revenue', AccountType::REVENUE, $revenue->id, true);
        $this->createAccount('4101', 'Product Sales', AccountType::REVENUE, $sales->id, false, true);
        $this->createAccount('4102', 'Service Revenue', AccountType::REVENUE, $sales->id);
        $this->createAccount('4110', 'Sales Returns', AccountType::REVENUE, $sales->id);
        $this->createAccount('4120', 'Sales Discounts', AccountType::REVENUE, $sales->id);

        // Other Income
        $otherIncome = $this->createAccount('4200', 'Other Income', AccountType::REVENUE, $revenue->id, true);
        $this->createAccount('4201', 'Interest Income', AccountType::REVENUE, $otherIncome->id);
        $this->createAccount('4202', 'Foreign Exchange Gain', AccountType::REVENUE, $otherIncome->id);
        $this->createAccount('4203', 'Miscellaneous Income', AccountType::REVENUE, $otherIncome->id);
        $this->createAccount('4204', 'Inventory Gain', AccountType::REVENUE, $otherIncome->id);
    }

    protected function createExpenses(): void
    {
        $expenses = $this->createAccount('5000', 'Expenses', AccountType::EXPENSE, null, true, true);

        // Cost of Sales
        $costOfSales = $this->createAccount('5100', 'Cost of Sales', AccountType::EXPENSE, $expenses->id, true);
        $this->createAccount('5101', 'Cost of Goods Sold', AccountType::EXPENSE, $costOfSales->id, false, true);
        $this->createAccount('5110', 'Purchase Returns', AccountType::EXPENSE, $costOfSales->id);
        $this->createAccount('5120', 'Purchase Discounts', AccountType::EXPENSE, $costOfSales->id);
        $this->createAccount('5130', 'Freight In', AccountType::EXPENSE, $costOfSales->id);
        $this->createAccount('5140', 'Inventory Loss', AccountType::EXPENSE, $costOfSales->id);

        // Operating Expenses
        $operating = $this->createAccount('5200', 'Operating Expenses', AccountType::EXPENSE, $expenses->id, true);

        // Salaries & Benefits
        $salaries = $this->createAccount('5210', 'Salaries & Benefits', AccountType::EXPENSE, $operating->id, true);
        $this->createAccount('5211', 'Salaries Expense', AccountType::EXPENSE, $salaries->id);
        $this->createAccount('5212', 'Commissions Expense', AccountType::EXPENSE, $salaries->id);
        $this->createAccount('5213', 'Benefits Expense', AccountType::EXPENSE, $salaries->id);
        $this->createAccount('5214', 'Payroll Tax Expense', AccountType::EXPENSE, $salaries->id);

        // Office & Admin
        $office = $this->createAccount('5220', 'Office & Administrative', AccountType::EXPENSE, $operating->id, true);
        $this->createAccount('5221', 'Rent Expense', AccountType::EXPENSE, $office->id);
        $this->createAccount('5222', 'Utilities Expense', AccountType::EXPENSE, $office->id);
        $this->createAccount('5223', 'Office Supplies', AccountType::EXPENSE, $office->id);
        $this->createAccount('5224', 'Telephone & Internet', AccountType::EXPENSE, $office->id);
        $this->createAccount('5225', 'Insurance Expense', AccountType::EXPENSE, $office->id);

        // Marketing
        $marketing = $this->createAccount('5230', 'Marketing & Sales', AccountType::EXPENSE, $operating->id, true);
        $this->createAccount('5231', 'Advertising Expense', AccountType::EXPENSE, $marketing->id);
        $this->createAccount('5232', 'Marketing Expense', AccountType::EXPENSE, $marketing->id);
        $this->createAccount('5233', 'Travel Expense', AccountType::EXPENSE, $marketing->id);

        // Other
        $this->createAccount('5240', 'Depreciation Expense', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5250', 'Bank Charges', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5260', 'Professional Fees', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5270', 'Delivery Expense', AccountType::EXPENSE, $operating->id);
        $this->createAccount('5280', 'Maintenance & Repairs', AccountType::EXPENSE, $operating->id);

        // Other Expenses
        $otherExpenses = $this->createAccount('5300', 'Other Expenses', AccountType::EXPENSE, $expenses->id, true);
        $this->createAccount('5301', 'Interest Expense', AccountType::EXPENSE, $otherExpenses->id);
        $this->createAccount('5302', 'Foreign Exchange Loss', AccountType::EXPENSE, $otherExpenses->id);
        $this->createAccount('5303', 'Miscellaneous Expense', AccountType::EXPENSE, $otherExpenses->id);
    }

    /**
     * Helper to create an account
     */
    protected function createAccount(
        string $code,
        string $name,
        AccountType $type,
        ?int $parentId = null,
        bool $isHeader = false,
        bool $isSystem = false
    ): Account {
        return Account::create([
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'parent_id' => $parentId,
            'is_header' => $isHeader,
            'is_system' => $isSystem,
            'is_active' => true,
            'balance' => 0,
        ]);
    }
}
