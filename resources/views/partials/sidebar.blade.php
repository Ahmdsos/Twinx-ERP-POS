<ul class="nav flex-column" id="sidebarNav" style="flex: 1; overflow-y: auto;">
    <!-- Navigation Items Only -->

    <!-- Dashboard -->
    <li class="nav-item mb-1">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active-gradient' : '' }}"
            href="{{ route('dashboard') }}">
            <i class="bi bi-grid-fill me-3 fs-5"></i>
            <span class="fw-medium">لوحة التحكم</span>
        </a>
    </li>

    <!-- Divider -->
    <div class="nav-group-label">العمليات</div>

    <!-- Sales Module -->
    <li class="nav-item mb-1">
        <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
            href="#salesMenu" role="button" aria-expanded="false">
            <div class="d-flex align-items-center">
                <i class="bi bi-cart3 me-3 fs-5"></i>
                <span class="fw-medium">المبيعات</span>
            </div>
            <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
        </a>
        <div class="collapse {{ request()->routeIs('pos.*', 'sales-invoices.*', 'customers.*', 'quotations.*', 'sales-orders.*', 'deliveries.*', 'customer-payments.*', 'reports.mission-control') ? 'show' : '' }}"
            id="salesMenu">
            <ul class="nav flex-column ms-3 mt-1 border-s border-secondary border-opacity-10 ps-3"
                style="border-right: 1px solid rgba(255,255,255,0.1);">
                @can('sales.create')
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('pos.index') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('pos.index') }}">
                            نقطة البيع (POS)
                        </a>
                    </li>
                @endcan

                @can('sales.manage')
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.mission-control') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('reports.mission-control') }}">
                            تحكم المهام اللوجستية 📡
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('sales-invoices.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('sales-invoices.index') }}">
                            فواتير المبيعات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('sales-orders.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('sales-orders.index') }}">
                            أوامر البيع
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('quotations.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('quotations.index') }}">
                            عروض الأسعار
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('sales-returns.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('sales-returns.index') }}">
                            مرتجع المبيعات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('deliveries.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('deliveries.index') }}">
                            أذونات الصرف
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('customer-payments.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('customer-payments.index') }}">
                            تحصيلات العملاء
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('customers.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('customers.index') }}">
                            العملاء
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>

    <!-- Purchasing Module -->
    @can('purchases.manage')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#purchaseMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-bag-check me-3 fs-5"></i>
                    <span class="fw-medium">المشتريات</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('purchase-orders.*', 'suppliers.*', 'purchase-invoices.*', 'grns.*', 'supplier-payments.*', 'purchase-returns.*') ? 'show' : '' }}"
                id="purchaseMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('purchase-orders.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('purchase-orders.index') }}">
                            أوامر الشراء
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('purchase-invoices.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('purchase-invoices.index') }}">
                            فواتير الشراء
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('grns.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('grns.index') }}">
                            أذونات الاستلام
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('purchase-returns.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('purchase-returns.index') }}">
                            مرتجع المشتريات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('supplier-payments.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('supplier-payments.index') }}">
                            دفعات الموردين
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('suppliers.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('suppliers.index') }}">
                            الموردين
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    <!-- Inventory Module -->
    @can('inventory.manage')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#inventoryMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-box-seam me-3 fs-5"></i>
                    <span class="fw-medium">المخزون</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('products.*', 'stock.*', 'warehouses.*', 'categories.*', 'units.*') ? 'show' : '' }}"
                id="inventoryMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('products.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('products.index') }}">
                            المنتجات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('stock.index') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('stock.index') }}">
                            حركات المخزون
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('stock.adjust') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('stock.adjust') }}">
                            تسوية مخزون
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('stock.transfer') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('stock.transfer') }}">
                            تحويل مخزني
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('warehouses.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('warehouses.index') }}">
                            المخازن
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('categories.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('categories.index') }}">
                            التصنيفات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('brands.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('brands.index') }}">
                            الماركات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('units.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('units.index') }}">
                            الوحدات
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    <!-- Divider -->
    <div class="nav-group-label">المالية والإدارة</div>

    <!-- Finance Module -->
    @can('finance.manage')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#financeMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cash-stack me-3 fs-5"></i>
                    <span class="fw-medium">الحسابات</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('accounts.*', 'journal-entries.*', 'treasury.*', 'expenses.*', 'expense-categories.*') ? 'show' : '' }}"
                id="financeMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <!-- Legacy link removed -->
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('journal-entries.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('journal-entries.index') }}">
                            القيود اليومية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('treasury.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('treasury.index') }}">
                            الخزينة والبنوك
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('expenses.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('expenses.index') }}">
                            المصروفات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('expense-categories.*') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('expense-categories.index') }}">
                            بنود المصروفات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('accounts.*', 'accounts-tree') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('accounts.tree') }}">
                            دليل الحسابات
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    <!-- HR Module -->
    @if(auth()->user()->canAny(['hr.view', 'hr.employees.view', 'hr.payroll.view', 'hr.leave.view', 'couriers.manage']))
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#hrMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-people me-3 fs-5"></i>
                    <span class="fw-medium">الموارد البشرية</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->is('hr*') ? 'show' : '' }}" id="hrMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    @can('hr.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.dashboard') ? 'text-white fw-bold' : '' }}"
                                href="{{ route('hr.dashboard') }}">
                                لوحة التحكم
                            </a>
                        </li>
                    @endcan
                    @can('hr.employees.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.employees.*') ? 'text-white fw-bold' : '' }}"
                                href="{{ route('hr.employees.index') }}">
                                إدارة الموظفين
                            </a>
                        </li>
                    @endcan
                    @can('hr.leave.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.leaves.*') ? 'text-white fw-bold' : '' }}"
                                href="{{ route('hr.leaves.index') }}">
                                إدارة الإجازات
                            </a>
                        </li>
                    @endcan
                    @can('hr.payroll.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.payroll.*') ? 'text-white fw-bold' : '' }}"
                                href="{{ route('hr.payroll.index') }}">
                                مسيرات الرواتب
                            </a>
                        </li>
                    @endcan
                    @can('couriers.manage')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.delivery.*') ? 'text-white fw-bold' : '' }}"
                                href="{{ route('hr.delivery.index') }}">
                                إدارة التوصيل
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
    @endif


    <!-- Divider -->
    <div class="nav-group-label">التقارير والتحليلات</div>

    <!-- Reports Module (Top Level) -->
    @can('reports.view')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#reportsMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up me-3 fs-5"></i>
                    <span class="fw-medium">التقارير الشاملة</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">

                    <!-- Financial -->
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.financial.pl') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.financial.pl', ['type' => 'pl']) }}">
                            أرباح وخسائر
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.inventory.valuation') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.inventory.valuation') }}">
                            تقييم المخزون
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.inventory.low-stock') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.inventory.low-stock') }}">
                            نواقص المخزون
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.sales.by-product') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.sales.by-product') }}">
                            مبيعات الأصناف
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.sales.by-customer') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.sales.by-customer') }}">
                            مبيعات العملاء
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.purchases.by-supplier') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.purchases.by-supplier') }}">
                            مشتريات الموردين
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.shifts') ? 'text-white fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.shifts') }}">
                            تقارير الورديات
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    <!-- Settings Module -->
    @can('settings.manage')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#settingsMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-gear me-3 fs-5"></i>
                    <span class="fw-medium">الإعدادات</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('settings.*', 'users.*') ? 'show' : '' }}" id="settingsMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('settings.index') ? 'text-white fw-bold' : '' }}"
                            href="{{ route('settings.index') }}">
                            إعدادات النظام
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('activity-log.*') ? 'text-white fw-bold' : 'text-secondary' }}"
                            href="{{ route('activity-log.index') }}">
                            سجل النشاطات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('settings.backup.*') ? 'text-white fw-bold' : 'text-secondary' }}"
                            href="{{ route('settings.backup.index') }}">
                            النسخ الاحتياطي
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('roles.*') ? 'text-white fw-bold' : 'text-secondary' }}"
                            href="{{ route('roles.index') }}">
                            إدارة الأدوار
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('users.*') ? 'text-white fw-bold' : 'text-secondary' }}"
                            href="{{ route('users.index') }}">
                            المستخدمين والصلاحيات
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan
</ul>

<style>
    /* Sidebar Custom Scrollbar */
    .nav-link.active-gradient {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.05) 100%);
        border-right: 3px solid #3b82f6;
    }

    .text-secondary-light {
        color: #94a3b8 !important;
    }

    .text-secondary-light:hover {
        color: #f1f5f9 !important;
        background-color: rgba(255, 255, 255, 0.03);
    }

    .transition-icon {
        transition: transform 0.2s;
    }

    [aria-expanded="true"] .transition-icon {
        transform: rotate(180deg);
    }
</style>