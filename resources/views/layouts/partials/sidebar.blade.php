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

        <!-- Accounting Section -->
        <li class="nav-item mt-3">
            <span class="nav-section-title text-uppercase text-secondary px-3 small">المحاسبة</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('accounts.index') }}"
                class="nav-link text-white {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i>
                دليل الحسابات
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('journals.index') }}"
                class="nav-link text-white {{ request()->routeIs('journals.*') ? 'active' : '' }}">
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
            <a href="{{ route('sales-invoices.index') }}"
                class="nav-link text-white {{ request()->routeIs('sales-invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt me-2"></i>
                فواتير البيع
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
    </ul>
</nav>