const fs = require('fs');
const path = require('path');

const arJsonPath = path.join(__dirname, 'resources', 'lang', 'ar.json');
const arJson = JSON.parse(fs.readFileSync(arJsonPath, 'utf8'));

const accounts = {
    "Current Assets": "الأصول المتداولة",
    "Cash and Bank": "النقدية والبنوك",
    "Cash on Hand": "نقدية بالصندوق",
    "Petty Cash": "عهدة نقدية",
    "Bank - Main Account": "البنك - الحساب الرئيسي",
    "Bank - USD Account": "البنك - حساب دولاري",
    "Receivables": "المدينون والذمم",
    "Accounts Receivable": "العملاء / المدينون",
    "Employee Advances": "سلف موظفين",
    "Other Receivables": "أرصدة مدينة أخرى",
    "Inventory": "المخزون",
    "Merchandise Inventory": "مخزون بضاعة",
    "Inventory in Transit": "مخزون بالطريق",
    "Fixed Assets": "الأصول الثابتة",
    "Property, Plant & Equipment": "العقارات والآلات والمعدات",
    "Furniture & Fixtures": "أثاث وتجهيزات",
    "Computer Equipment": "أجهزة كمبيوتر",
    "Vehicles": "وسائل نقل وانتقال",
    "Accumulated Depreciation": "مجمع الإهلاك",
    "Accum. Depr. - Furniture": "مجمع إهلاك أثاث",
    "Accum. Depr. - Computers": "مجمع إهلاك كمبيوتر",
    "Accum. Depr. - Vehicles": "مجمع إهلاك سيارات",
    "Current Liabilities": "الالتزامات المتداولة",
    "Payables": "الموردون والدائنون",
    "Accounts Payable": "الموردون",
    "Accrued Expenses": "مصروفات مستحقة",
    "Taxes Payable": "ضرائب مستحقة",
    "VAT Payable": "ضريبة القيمة المضافة - مخرجات",
    "VAT Receivable": "ضريبة القيمة المضافة - مدخلات",
    "Income Tax Payable": "ضريبة الدخل مستحقة",
    "Payroll Taxes Payable": "ضرائب كسب عمل مستحقة",
    "Customer Deposits": "تأمينات للغير",
    "Salaries Payable": "رواتب مستحقة",
    "Long-term Liabilities": "الالتزامات طويلة الأجل",
    "Bank Loans": "قروض بنكية",
    "Owner's Equity": "حقوق الملكية",
    "Paid-in Capital": "رأس المال المدفوع",
    "Retained Earnings": "أرباح مبقاة",
    "Owner's Drawings": "مسحوبات الشركاء",
    "Current Year Earnings": "صافي أرباح العام الحالي",
    "Revenue": "الإيرادات",
    "Sales Revenue": "إيرادات المبيعات",
    "Product Sales": "مبيعات بضاعة",
    "Service Revenue": "إيرادات خدمات",
    "Sales Returns": "مرتجعات مبيعات",
    "Sales Discounts": "خصومات مبيعات",
    "Other Income": "إيرادات أخرى",
    "Interest Income": "إيرادات فوائد",
    "Foreign Exchange Gain": "أرباح فروق عملة",
    "Miscellaneous Income": "إيرادات متنوعة",
    "Expenses": "المصروفات",
    "Cost of Sales": "تكلفة المبيعات",
    "Cost of Goods Sold": "تكلفة البضاعة المباعة",
    "Purchase Returns": "مرتجعات مشتريات",
    "Purchase Discounts": "خصومات مشتريات",
    "Freight In": "شحن ومصاريف نقل للداخل",
    "Operating Expenses": "مصروفات تشغيلية",
    "Salaries & Benefits": "الرواتب والمزايا",
    "Salaries Expense": "مصروف الرواتب",
    "Commissions Expense": "مصروف عمولات",
    "Benefits Expense": "مصروف مزايا موظفين",
    "Payroll Tax Expense": "مصروف ضرائب الرواتب",
    "Office & Administrative": "مصروفات إدارية وعمومية",
    "Rent Expense": "مصروف الإيجار",
    "Utilities Expense": "مصروف مرافق (كهرباء ومياه)",
    "Office Supplies": "أدوات مكتبية",
    "Telephone & Internet": "هاتف وإنترنت",
    "Insurance Expense": "مصروف تأمين",
    "Marketing & Sales": "مصروفات بيعية وتسويقية",
    "Advertising Expense": "مصروف إعلان",
    "Marketing Expense": "مصروف تسويق",
    "Travel Expense": "مصروفات سفر وانتقال",
    "Depreciation Expense": "مصروف الإهلاك",
    "Bank Charges": "مصاريف بنكية",
    "Professional Fees": "أتعاب مهنية",
    "Delivery Expense": "مصاريف شحن وتوصيل",
    "Maintenance & Repairs": "صيانة وإصلاحات",
    "Other Expenses": "مصروفات أخرى",
    "Interest Expense": "مصروف فوائد",
    "Foreign Exchange Loss": "خسائر فروق عملة",
    "Miscellaneous Expense": "مصروفات متنوعة"
};

let addedCount = 0;
for (const [key, value] of Object.entries(accounts)) {
    if (!arJson[key]) {
        arJson[key] = value;
        addedCount++;
    }
}

fs.writeFileSync(arJsonPath, JSON.stringify(arJson, null, 4), 'utf8');
console.log(`Added ${addedCount} account translations to ar.json`);
