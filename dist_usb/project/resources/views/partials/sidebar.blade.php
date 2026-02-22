<ul class="nav flex-column" id="sidebarNav" style="flex: 1; overflow-y: auto;">
    <!-- Navigation Items Only -->

    <!-- Dashboard -->
    <li class="nav-item mb-1">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active-gradient' : '' }}"
            href="{{ route('dashboard') }}">
            <i class="bi bi-grid-fill me-3 fs-5"></i>
            <span class="fw-medium">{{ __('Dashboard') }}</span>
        </a>
    </li>



    <!-- Divider -->
    <div class="nav-group-label">{{ __('Operations') }}</div>

    <!-- Sales Module -->
    <li class="nav-item mb-1">
        <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
            href="#salesMenu" role="button" aria-expanded="false">
            <div class="d-flex align-items-center">
                <i class="bi bi-cart3 me-3 fs-5"></i>
                <span class="fw-medium">{{ __('Sales') }}</span>
            </div>
            <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
        </a>
        <div class="collapse {{ request()->routeIs('pos.*', 'sales-invoices.*', 'customers.*', 'quotations.*', 'sales-orders.*', 'deliveries.*', 'customer-payments.*', 'mission.control') ? 'show' : '' }}"
            id="salesMenu">
            <ul class="nav flex-column ms-3 mt-1 border-start border-secondary border-opacity-10 ps-3"
                style="border-inline-start: 1px solid rgba(255,255,255,0.1);">
                @can('sales.create')
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('pos.index') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('pos.index') }}">
                            {{ __('POS (Point of Sale)') }}
                        </a>
                    </li>
                @endcan

                @can('sales.manage')
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('mission.control') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('mission.control') }}">
                            {{ __('Logistics Control') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('sales-invoices.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('sales-invoices.index') }}">
                            {{ __('Sales Invoices') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('sales-orders.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('sales-orders.index') }}">
                            {{ __('Sales Orders') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('quotations.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('quotations.index') }}">
                            {{ __('Quotations') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('sales-returns.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('sales-returns.index') }}">
                            {{ __('Sales Returns') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('deliveries.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('deliveries.index') }}">
                            {{ __('Delivery Notes') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('customer-payments.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('customer-payments.index') }}">
                            {{ __('Customer Payments') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('customers.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('customers.index') }}">
                            {{ __('Customers') }}
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
                    <span class="fw-medium">{{ __('Purchases') }}</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('purchase-orders.*', 'suppliers.*', 'purchase-invoices.*', 'grns.*', 'supplier-payments.*', 'purchase-returns.*') ? 'show' : '' }}"
                id="purchaseMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('purchase-orders.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('purchase-orders.index') }}">
                            {{ __('Purchase Orders') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('purchase-invoices.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('purchase-invoices.index') }}">
                            {{ __('Purchase Invoices') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('grns.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('grns.index') }}">
                            {{ __('GRNs') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('purchase-returns.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('purchase-returns.index') }}">
                            {{ __('Purchase Returns') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('supplier-payments.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('supplier-payments.index') }}">
                            {{ __('Supplier Payments') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('suppliers.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('suppliers.index') }}">
                            {{ __('Suppliers') }}
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
                    <span class="fw-medium">{{ __('Inventory') }}</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('products.*', 'stock.*', 'warehouses.*', 'categories.*', 'units.*') ? 'show' : '' }}"
                id="inventoryMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('products.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('products.index') }}">
                            {{ __('Products') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('stock.index') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('stock.index') }}">
                            {{ __('Stock Movements') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('stock.adjust') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('stock.adjust') }}">
                            {{ __('Stock Adjustment') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('stock.transfer') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('stock.transfer') }}">
                            {{ __('Stock Transfer') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('warehouses.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('warehouses.index') }}">
                            {{ __('Warehouses') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('categories.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('categories.index') }}">
                            {{ __('Categories') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('brands.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('brands.index') }}">
                            {{ __('Brands') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('units.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('units.index') }}">
                            {{ __('Units') }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    <!-- Divider -->
    <div class="nav-group-label">{{ __('Finance & Admin') }}</div>

    <!-- Finance Module -->
    @can('finance.manage')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#financeMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cash-stack me-3 fs-5"></i>
                    <span class="fw-medium">{{ __('Accounts') }}</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('accounts.*', 'journal-entries.*', 'treasury.*', 'expenses.*', 'expense-categories.*') ? 'show' : '' }}"
                id="financeMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <!-- Legacy link removed -->
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('journal-entries.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('journal-entries.index') }}">
                            {{ __('Journal Entries') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('treasury.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('treasury.index') }}">
                            {{ __('Treasury & Banks') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('expenses.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('expenses.index') }}">
                            {{ __('Expenses') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('expense-categories.*') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('expense-categories.index') }}">
                            {{ __('Expense Categories') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('accounts.*', 'accounts-tree') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('accounts.tree') }}">
                            {{ __('Chart of Accounts') }}
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
                    <span class="fw-medium">{{ __('Human Resources') }}</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->is('hr*') ? 'show' : '' }}" id="hrMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    @can('hr.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.dashboard') ? 'text-body fw-bold' : '' }}"
                                href="{{ route('hr.dashboard') }}">
                                {{ __('Dashboard') }}
                            </a>
                        </li>
                    @endcan
                    @can('hr.employees.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.employees.*') ? 'text-body fw-bold' : '' }}"
                                href="{{ route('hr.employees.index') }}">
                                {{ __('Employees') }}
                            </a>
                        </li>
                    @endcan
                    @can('hr.leave.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.leaves.*') ? 'text-body fw-bold' : '' }}"
                                href="{{ route('hr.leaves.index') }}">
                                {{ __('Leaves') }}
                            </a>
                        </li>
                    @endcan
                    @can('hr.payroll.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.payroll.*') ? 'text-body fw-bold' : '' }}"
                                href="{{ route('hr.payroll.index') }}">
                                {{ __('Payroll') }}
                            </a>
                        </li>
                    @endcan
                    @can('hr.advances.view')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.advances.*') ? 'text-body fw-bold' : '' }}"
                                href="{{ route('hr.advances.index') }}">
                                {{ __('Advances') }}
                            </a>
                        </li>
                    @endcan
                    @can('couriers.manage')
                        <li class="nav-item">
                            <a class="nav-link py-2 fs-6 {{ request()->routeIs('hr.delivery.*') ? 'text-body fw-bold' : '' }}"
                                href="{{ route('hr.delivery.index') }}">
                                {{ __('Delivery Management') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
    @endif


    <!-- Divider -->
    <div class="nav-group-label">{{ __('Reports & Analytics') }}</div>

    <!-- Reports Module (Top Level) -->
    @can('reports.view')
        <li class="nav-item mb-1">
            <a class="nav-link d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse"
                href="#reportsMenu" role="button" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up me-3 fs-5"></i>
                    <span class="fw-medium">{{ __('Comprehensive Reports') }}</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">

                    <!-- Financial -->
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.financial.pl') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.financial.pl', ['type' => 'pl']) }}">
                            {{ __('Profit & Loss') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.financial.bs') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.financial.bs', ['type' => 'bs']) }}">
                            {{ __('Balance Sheet') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.inventory.valuation') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.inventory.valuation') }}">
                            {{ __('Inventory Valuation') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.inventory.low-stock') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.inventory.low-stock') }}">
                            {{ __('Low Stock') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.sales.by-product') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.sales.by-product') }}">
                            {{ __('Product Sales') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.sales.by-customer') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.sales.by-customer') }}">
                            {{ __('Customer Sales') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.purchases.by-supplier') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.purchases.by-supplier') }}">
                            {{ __('Supplier Purchases') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('reports.shifts') ? 'text-body fw-bold' : 'text-secondary-light' }}"
                            href="{{ route('reports.shifts') }}">
                            {{ __('Shift Reports') }}
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
                    <span class="fw-medium">{{ __('Settings') }}</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon opacity-50"></i>
            </a>
            <div class="collapse {{ request()->routeIs('settings.*', 'users.*') ? 'show' : '' }}" id="settingsMenu">
                <ul class="nav flex-column ms-3 mt-1 border-secondary border-opacity-10 ps-3"
                    style="border-right: 1px solid rgba(255,255,255,0.1);">
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('settings.index') ? 'text-body fw-bold' : '' }}"
                            href="{{ route('settings.index') }}">
                            {{ __('System Settings') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->is('activate*') ? 'text-body fw-bold' : '' }}"
                            href="{{ url('/activate') }}">
                            {{ __('License Registration') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('activity-log.*') ? 'text-body fw-bold' : 'text-secondary' }}"
                            href="{{ route('activity-log.index') }}">
                            {{ __('Activity Log') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('settings.backup.*') ? 'text-body fw-bold' : 'text-secondary' }}"
                            href="{{ route('settings.backup.index') }}">
                            {{ __('Backup') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('roles.*') ? 'text-body fw-bold' : 'text-secondary' }}"
                            href="{{ route('roles.index') }}">
                            {{ __('Role Management') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 fs-6 {{ request()->routeIs('users.*') ? 'text-body fw-bold' : 'text-secondary' }}"
                            href="{{ route('users.index') }}">
                            {{ __('Users & Permissions') }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan
    <!-- Documentation -->
    <li class="nav-item mb-1">
        <a class="nav-link" href="{{ asset('manual/index.html') }}" target="_blank">
            <i class="bi bi-book-half me-3 fs-5 text-white"></i>
            <span class="fw-medium">{{ __('System Manual') }}</span>
        </a>
    </li>
</ul>

<style>
    /* Sidebar Custom Scrollbar */
    .nav-link.active-gradient {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.05) 100%);
        border-inline-start: 3px solid #3b82f6;
    }

    .text-secondary-light {
        color: var(--text-secondary);
        !important;
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