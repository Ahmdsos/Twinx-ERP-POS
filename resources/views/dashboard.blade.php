@extends('layouts.app')

@section('title', 'لوحة التحكم - Twinx ERP')
@section('page-title', 'لوحة التحكم')

@section('content')
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Sales This Month -->
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card sales h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon text-white">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-value">{{ number_format($dashboard['sales']['sales_this_month'] ?? 0, 2) }}
                            </div>
                            <div class="stat-label">مبيعات الشهر</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases This Month -->
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card purchases h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon text-white">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-value">
                                {{ number_format($dashboard['purchasing']['purchases_this_month'] ?? 0, 2) }}
                            </div>
                            <div class="stat-label">مشتريات الشهر</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AR Outstanding -->
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card receivables h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon text-white">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-value">{{ number_format($dashboard['sales']['ar_outstanding'] ?? 0, 2) }}</div>
                            <div class="stat-label">مستحقات العملاء</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AP Outstanding -->
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card payables h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon text-white">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-value">{{ number_format($dashboard['purchasing']['ap_outstanding'] ?? 0, 2) }}
                            </div>
                            <div class="stat-label">مستحقات الموردين</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- P&L Summary -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>الإيرادات والمصروفات</h5>
                    <span class="badge bg-primary">هذا الشهر</span>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <h3 class="text-success mb-1">
                                {{ number_format($dashboard['financial']['revenue_this_month'] ?? 0, 2) }}
                            </h3>
                            <small class="text-muted">الإيرادات</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-danger mb-1">
                                {{ number_format($dashboard['financial']['expenses_this_month'] ?? 0, 2) }}
                            </h3>
                            <small class="text-muted">المصروفات</small>
                        </div>
                        <div class="col-4">
                            @php $netIncome = $dashboard['financial']['net_income_this_month'] ?? 0; @endphp
                            <h3 class="{{ $netIncome >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                                {{ number_format($netIncome, 2) }}
                            </h3>
                            <small class="text-muted">صافي الربح</small>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>إحصائيات سريعة</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-people text-primary me-2"></i>العملاء</span>
                            <span
                                class="badge bg-primary rounded-pill">{{ $dashboard['quick_stats']['total_customers'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-truck text-info me-2"></i>الموردين</span>
                            <span
                                class="badge bg-info rounded-pill">{{ $dashboard['quick_stats']['total_suppliers'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-box-seam text-success me-2"></i>المنتجات</span>
                            <span
                                class="badge bg-success rounded-pill">{{ $dashboard['quick_stats']['total_products'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>تنبيهات المخزون</span>
                            <span
                                class="badge bg-warning rounded-pill">{{ $dashboard['inventory']['low_stock_alerts'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-cart text-secondary me-2"></i>أوامر بيع معلقة</span>
                            <span
                                class="badge bg-secondary rounded-pill">{{ $dashboard['sales']['pending_orders'] ?? 0 }}</span>
                        </li>
                    </ul>

                    <div class="mt-4 pt-3 border-top">
                        <h6 class="text-muted mb-2">قيمة المخزون</h6>
                        <h3 class="text-primary">{{ number_format($dashboard['inventory']['total_stock_value'] ?? 0, 2) }}
                            <small class="text-muted fs-6">ج.م</small>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent -->
    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('sales-orders.create') }}" class="quick-action-btn">
                                <i class="bi bi-plus-circle text-success"></i>
                                <span>أمر بيع جديد</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('purchase-orders.create') }}" class="quick-action-btn">
                                <i class="bi bi-cart-plus text-primary"></i>
                                <span>أمر شراء جديد</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('customers.create') }}" class="quick-action-btn">
                                <i class="bi bi-person-plus text-info"></i>
                                <span>عميل جديد</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('journal-entries.create') }}" class="quick-action-btn">
                                <i class="bi bi-journal-plus text-warning"></i>
                                <span>قيد يومية</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>أفضل العملاء هذا الشهر</h5>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>العميل</th>
                                    <th>إجمالي المبيعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboard['sales']['top_customers'] ?? [] as $index => $customer)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <span class="badge bg-warning"><i class="bi bi-trophy"></i></span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>{{ $customer['customer_name'] }}</td>
                                        <td><strong class="money">{{ number_format($customer['total_sales'], 2) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد مبيعات هذا الشهر
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue vs Expenses Chart
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['الإيرادات', 'المصروفات', 'صافي الربح'],
                    datasets: [{
                        data: [
                            {{ $dashboard['financial']['revenue_this_month'] ?? 0 }},
                            {{ $dashboard['financial']['expenses_this_month'] ?? 0 }},
                            {{ $dashboard['financial']['net_income_this_month'] ?? 0 }}
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            '{{ ($dashboard['financial']['net_income_this_month'] ?? 0) >= 0 ? "rgba(59, 130, 246, 0.8)" : "rgba(239, 68, 68, 0.8)" }}'
                        ],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString('ar-EG');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush