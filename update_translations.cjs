const fs = require('fs');
const path = require('path');

const arPath = path.join(__dirname, 'resources/lang/ar.json');
const enPath = path.join(__dirname, 'resources/lang/en.json');

// New keys to add
const newKeys = {
    // Accounting
    "Chart of Accounts": "دليل الحسابات",
    "View accounts list": "عرض قائمة الحسابات",
    "View Tree": "عرض الشجرة",
    "New Account": "حساب جديد",
    "Account Number": "رقم الحساب",
    "Account Name": "اسم الحساب",
    "Account Type": "نوع الحساب",
    "Account Nature": "طبيعة الحساب",
    "Current Balance": "الرصيد الحالي",
    "Active": "نشط",
    "Inactive": "موقف",
    "Account Statement": "كشف حساب",
    "Edit": "تعديل",
    "No accounts found": "لا توجد حسابات",
    "Add New Account": "إضافة حساب جديد",
    "Under": "يندرج تحت",
    "Debit": "مدين",
    "Credit": "دائن",
    "Create Account": "إضافة حساب جديد",
    "Cancel": "إلغاء",
    "Save Account": "حفظ الحساب",
    "Select Account Type": "اختر نوع الحساب",
    "Parent Account": "الحساب الرئيسي",
    "Root Account": "حساب رئيسي",
    "Leave empty if root account": "اتركه فارغاً إذا كان حساب رئيسي",
    "Description": "الوصف",
    "Active Account": "حساب نشط",
    "Edit Account": "تعديل الحساب",
    "Save Changes": "حفظ التعديلات",
    "Account Tree": "شجرة الحسابات",
    "Tree View": "عرض الشجرة",
    "List View": "عرض القائمة",
    "Expand All": "توسيع الكل",
    "Collapse All": "طي الكل",
    "Account Details": "تفاصيل الحساب",
    "Journal Entries": "القيود اليومية",
    "New Journal Entry": "قيد يومي جديد",
    "Entry Number": "رقم القيد",
    "Entry Date": "تاريخ القيد",
    "Total Debit": "إجمالي المدين",
    "Total Credit": "إجمالي الدائن",
    "Reference": "المرجع",
    "Notes": "ملاحظات",
    "Save Entry": "حفظ القيد",
    "Add Row": "إضافة صف",
    "Delete Row": "حذف صف",
    "Balance": "الرصيد",
    "No entries found": "لا توجد قيود",

    // Sales & Common
    "Sales Invoices": "فواتير المبيعات",
    "Sales management and records": "إدارة وسجل المبيعات",
    "New invoice": "فاتورة جديدة",
    "Search": "بحث",
    "Date From": "التاريخ من",
    "To": "إلى",
    "All": "الكل",
    "Fully Paid": "خالصة",
    "Partially Paid": "جزئي",
    "Refunded": "مرتجع",
    "Apply": "تطبيق",
    "Reset": "إعادة تعيين",
    "Invoice #": "رقم الفاتورة",
    "Total": "الإجمالي",
    "Remaining": "المتبقي",
    "View": "عرض",
    "Print": "طباعة",
    "Delete": "حذف",
    "No invoices found": "لا توجد فواتير",
    "Create Invoice": "إنشاء فاتورة",
    "Invoice Details": "تفاصيل الفاتورة",
    "Products": "المنتجات",
    "Product": "المنتج",
    "Quantity": "الكمية",
    "Unit Price": "سعر الوحدة",
    "Subtotal": "المجموع الفرعي",
    "Discount": "الخصم",
    "Tax": "الضريبة",
    "Grand Total": "الإجمالي الكلي",
    "Payment Method": "طريقة الدفع",
    "Cash": "نقدي",
    "Payment Status": "حالة الدفع",
    "Add Product": "إضافة منتج",
    "Save Invoice": "حفظ الفاتورة",
    "Customers": "العملاء",
    "Manage customers": "إدارة العملاء",
    "New Customer": "عميل جديد",
    "Customer Name": "اسم العميل",
    "Phone": "الهاتف",
    "Mobile": "المحمول",
    "Email": "البريد الإلكتروني",
    "Address": "العنوان",
    "City": "المدينة",
    "Customer Type": "نوع العميل",
    "retail": "تجزئة",
    "wholesale": "جملة",
    "distributor": "موزع",
    "Credit Limit": "حد الائتمان",
    "Create Customer": "إضافة عميل",
    "Edit Customer": "تعديل العميل",
    "Customer Details": "تفاصيل العميل",
    "Save Customer": "حفظ العميل",
    "No customers found": "لا يوجد عملاء",
    "Basic Information": "البيانات الأساسية",
    "Contact Information": "بيانات التواصل",
    "Financial Information": "البيانات المالية",
    "Customer Statement": "كشف حساب العميل",
    "Import Customers": "استيراد العملاء",
    "Sales Orders": "أوامر البيع",
    "Manage orders": "إدارة الأوامر",
    "New Order": "أمر جديد",
    "Create Order": "إنشاء أمر بيع",
    "Edit Order": "تعديل الأمر",
    "Order Details": "تفاصيل الأمر",
    "Order Number": "رقم الأمر",
    "No orders found": "لا توجد أوامر",
    "Quotations": "عروض الأسعار",
    "Manage quotations": "إدارة عروض الأسعار",
    "New Quotation": "عرض سعر جديد",
    "Create Quotation": "إنشاء عرض سعر",
    "Edit Quotation": "تعديل عرض السعر",
    "Quotation Details": "تفاصيل عرض السعر",
    "Quotation Number": "رقم العرض",
    "No quotations found": "لا توجد عروض أسعار",
    "Convert to Invoice": "تحويل لفاتورة",
    "Validity Period": "فترة الصلاحية",
    "Deliveries": "التوصيل",
    "Manage deliveries": "إدارة التوصيل",
    "New Delivery": "توصيل جديد",
    "Delivery Details": "تفاصيل التوصيل",
    "Delivery Status": "حالة التوصيل",
    "No deliveries found": "لا توجد عمليات توصيل",

    // Inventory
    "Inventory": "المخزون",
    "Product Name": "اسم المنتج",
    "SKU": "كود المنتج",
    "Category": "التصنيف",
    "Brand": "الماركة",
    "Stock": "المخزون",
    "Cost Price": "سعر التكلفة",
    "Selling Price": "سعر البيع",
    "Minimum Stock": "الحد الأدنى للمخزون",
    "New Product": "منتج جديد",
    "Create Product": "إضافة منتج جديد",
    "Edit Product": "تعديل المنتج",
    "Product Details": "تفاصيل المنتج",
    "No products found": "لا توجد منتجات",
    "Categories": "التصنيفات",
    "New Category": "تصنيف جديد",
    "Brands": "الماركات",
    "New Brand": "ماركة جديدة",
    "Units": "الوحدات",
    "New Unit": "وحدة جديدة",
    "Warehouses": "المستودعات",
    "New Warehouse": "مستودع جديد",
    "Stock Movement": "حركة المخزون",
    "Transfer Stock": "نقل مخزون",

    // Purchases
    "Purchases": "المشتريات",
    "Purchase Invoices": "فواتير المشتريات",
    "New Purchase": "مشتريات جديدة",
    "Suppliers": "الموردين",
    "New Supplier": "مورد جديد",
    "Supplier Name": "اسم المورد",
    "No suppliers found": "لا يوجد موردين",
    "Purchase Returns": "مرتجعات المشتريات",

    // HR & Finance
    "Expenses": "المصروفات",
    "New Expense": "مصروف جديد",
    "Expense Category": "تصنيف المصروف",
    "No expenses found": "لا توجد مصروفات",
    "Employees": "الموظفين",
    "New Employee": "موظف جديد",
    "Department": "القسم",
    "Position": "المنصب",
    "Salary": "الراتب",
    "Attendance": "الحضور والانصراف",
    "Leave Request": "طلب إجازة",
    "Payroll": "كشف المرتبات",

    // Reports
    "Reports": "التقارير",
    "Financial Reports": "التقارير المالية",
    "Sales Reports": "تقارير المبيعات",
    "Inventory Reports": "تقارير المخزون",
    "Profit and Loss": "الأرباح والخسائر",
    "Balance Sheet": "الميزانية العمومية",
    "Trial Balance": "ميزان المراجعة",
    "Cash Flow": "التدفق النقدي",

    // Settings
    "Settings": "الإعدادات",
    "General Settings": "الإعدادات العامة",
    "Company Settings": "إعدادات الشركة",
    "Users": "المستخدمين",
    "Roles": "الأدوار",
    "Permissions": "الصلاحيات",

    // Actions & Common
    "Confirm": "تأكيد",
    "Are you sure?": "هل أنت متأكد؟",
    "Yes, delete it": "نعم، احذفه",
    "This action cannot be undone": "هذا الإجراء لا يمكن التراجع عنه",
    "Success": "تم بنجاح",
    "Error": "خطأ",
    "Warning": "تحذير",
    "Info": "معلومة",
    "Loading...": "جاري التحميل...",
    "No results found": "لا توجد نتائج",
    "Select...": "اختر...",
    "Select All": "تحديد الكل",
    "Deselect All": "إلغاء تحديد الكل",
    "Export": "تصدير",
    "Import": "استيراد",
    "Download": "تحميل",
    "Upload": "رفع",
    "Refresh": "تحديث",
    "Close": "إغلاق",
    "Save": "حفظ",
    "Submit": "إرسال",
    "Back": "رجوع",
    "Next": "التالي",
    "Previous": "السابق",
    "First": "الأول",
    "Last": "الأخير",
    "Name": "الاسم",
    "Type": "النوع",
    "Created At": "تاريخ الإنشاء",
    "Updated At": "تاريخ التعديل",
    "Created By": "أنشئ بواسطة",
    "From": "من",
    "Order Summary": "ملخص الأمر",
    "Sales Invoice": "فاتورة مبيعات",
    "Payment": "دفعة",
    "Opening Balance": "رصيد افتتاحي"
};

const enKeys = {};
for (const [key, val] of Object.entries(newKeys)) {
    enKeys[key] = key;
}

function updateFile(filePath, newEntries) {
    let data = {};
    if (fs.existsSync(filePath)) {
        try {
            data = JSON.parse(fs.readFileSync(filePath, 'utf8'));
        } catch (e) {
            console.error(`Error reading ${filePath}:`, e);
            return;
        }
    }

    let addedCount = 0;
    for (const [key, value] of Object.entries(newEntries)) {
        if (!data[key]) {
            data[key] = value;
            addedCount++;
        }
    }

    if (addedCount > 0) {
        fs.writeFileSync(filePath, JSON.stringify(data, null, 4), 'utf8');
        console.log(`Updated ${filePath} with ${addedCount} new keys.`);
    } else {
        console.log(`No new keys to add to ${filePath}.`);
    }
}

updateFile(arPath, newKeys);
updateFile(enPath, enKeys);
