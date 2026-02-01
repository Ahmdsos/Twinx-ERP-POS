<!-- Sidebar -->
<nav id="sidebar" class="sidebar bg-dark text-white">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none">
            <i class="bi bi-boxes fs-4 me-2"></i>
            <span class="fs-5 fw-bold">Twinx ERP</span>
        </a>
    </div>

    <ul class="nav flex-column p-2">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
                class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                لوحة التحكم
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('pos.index') }}"
                class="nav-link text-white {{ request()->routeIs('pos.*') ? 'active' : '' }}"
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; margin: 5px;">
                <i class="bi bi-cart-check me-2"></i>
                <strong>نقطة البيع (POS)</strong>
            </a>
        </li>

        <!-- Accounting Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">المحاسبة</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('accounts.index') }}"
                class="nav-link text-white {{ request()->routeIs('accounts.index') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i>
                دليل الحسابات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('accounts.tree') }}"
                class="nav-link text-white {{ request()->routeIs('accounts.tree') ? 'active' : '' }}">
                <i class="bi bi-diagram-3 me-2"></i>
                شجرة الحسابات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('journal-entries.index') }}"
                class="nav-link text-white {{ request()->routeIs('journal-entries.*') ? 'active' : '' }}">
                <i class="bi bi-book me-2"></i>
                القيود اليومية
            </a>
        </li>

        <!-- Inventory Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">المخزون</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('products.index') }}"
                class="nav-link text-white {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i>
                المنتجات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('warehouses.index') }}"
                class="nav-link text-white {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <i class="bi bi-building me-2"></i>
                المستودعات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('categories.index') }}"
                class="nav-link text-white {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags me-2"></i>
                التصنيفات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('units.index') }}"
                class="nav-link text-white {{ request()->routeIs('units.*') ? 'active' : '' }}">
                <i class="bi bi-rulers me-2"></i>
                وحدات القياس
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('stock.index') }}"
                class="nav-link text-white {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right me-2"></i>
                حركات المخزون
            </a>
        </li>

        <!-- Sales Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">المبيعات</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('customers.index') }}"
                class="nav-link text-white {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i>
                العملاء
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sales-orders.index') }}"
                class="nav-link text-white {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}">
                <i class="bi bi-cart me-2"></i>
                أوامر البيع
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('deliveries.index') }}"
                class="nav-link text-white {{ request()->routeIs('deliveries.*') ? 'active' : '' }}">
                <i class="bi bi-truck me-2"></i>
                أوامر التسليم
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('quotations.index') }}"
                class="nav-link text-white {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text me-2"></i>
                عروض الأسعار
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sales-invoices.index') }}"
                class="nav-link text-white {{ request()->routeIs('sales-invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt me-2"></i>
                فواتير البيع
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('customer-payments.index') }}"
                class="nav-link text-white {{ request()->routeIs('customer-payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin me-2"></i>
                المدفوعات
            </a>
        </li>

        <!-- Purchasing Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">المشتريات</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}"
                class="nav-link text-white {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck me-2"></i>
                الموردين
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('purchase-orders.index') }}"
                class="nav-link text-white {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                <i class="bi bi-bag me-2"></i>
                أوامر الشراء
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grns.index') }}"
                class="nav-link text-white {{ request()->routeIs('grns.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i>
                استلام البضاعة
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('purchase-invoices.index') }}"
                class="nav-link text-white {{ request()->routeIs('purchase-invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt me-2"></i>
                فواتير الشراء
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('supplier-payments.index') }}"
                class="nav-link text-white {{ request()->routeIs('supplier-payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash me-2"></i>
                مدفوعات الموردين
            </a>
        </li>

        <!-- Reports Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">التقارير</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.financial') }}"
                class="nav-link text-white {{ request()->routeIs('reports.financial') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                التقارير المالية
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.stock') }}"
                class="nav-link text-white {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data me-2"></i>
                تقارير المخزون
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.customer-sales') }}"
                class="nav-link text-white {{ request()->routeIs('reports.customer-sales') ? 'active' : '' }}">
                <i class="bi bi-graph-up me-2"></i>
                ملخص مبيعات العملاء
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.supplier-purchases') }}"
                class="nav-link text-white {{ request()->routeIs('reports.supplier-purchases') ? 'active' : '' }}">
                <i class="bi bi-graph-down me-2"></i>
                ملخص مشتريات الموردين
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.trial-balance') }}"
                class="nav-link text-white {{ request()->routeIs('reports.trial-balance') ? 'active' : '' }}">
                <i class="bi bi-calculator me-2"></i>
                ميزان المراجعة
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.profit-loss') }}"
                class="nav-link text-white {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                <i class="bi bi-cash-coin me-2"></i>
                قائمة الدخل
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.balance-sheet') }}"
                class="nav-link text-white {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
                <i class="bi bi-pie-chart me-2"></i>
                الميزانية العمومية
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.ar-aging') }}"
                class="nav-link text-white {{ request()->routeIs('reports.ar-aging') ? 'active' : '' }}">
                <i class="bi bi-hourglass-split me-2"></i>
                أعمار ديون العملاء
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.ap-aging') }}"
                class="nav-link text-white {{ request()->routeIs('reports.ap-aging') ? 'active' : '' }}">
                <i class="bi bi-hourglass me-2"></i>
                أعمار ديون الموردين
            </a>
        </li>

        <!-- Shipping Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">الشحن</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('couriers.index') }}"
                class="nav-link text-white {{ request()->routeIs('couriers.*') ? 'active' : '' }}">
                <i class="bi bi-truck me-2"></i>
                شركات الشحن
            </a>
        </li>

        <!-- Admin Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">الإدارة</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('activity-log.index') }}"
                class="nav-link text-white {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history me-2"></i>
                سجل النشاطات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('currencies.index') }}"
                class="nav-link text-white {{ request()->routeIs('currencies.*') ? 'active' : '' }}">
                <i class="bi bi-currency-exchange me-2"></i>
                العملات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('loyalty.index') }}"
                class="nav-link text-white {{ request()->routeIs('loyalty.*') ? 'active' : '' }}">
                <i class="bi bi-star me-2"></i>
                برنامج الولاء
            </a>
        </li>
        <li class="nav-item">

            <a href="{{ route('settings.index') }}"
                class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i>
                الإعدادات
            </a>
        </li>
    </ul>
</nav>