@extends('layouts.app')

@section('title', 'تقرير النواقص - Low Stock Report')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-heading fw-bold"><i class="bi bi-exclamation-triangle me-2 text-warning"></i> تقرير النواقص</h2>
            <p class="text-body-50">المنتجات التي وصلت إلى حد الطلب أو أقل.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-light" title="طباعة عادية A4">
                <i class="bi bi-printer me-1"></i> طباعة عادية
            </button>
            <button onclick="thermalPrintLowStock()" class="btn btn-outline-warning" title="طباعة حرارية 80mm">
                <i class="bi bi-receipt me-1"></i> طباعة حرارية
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                    <i class="bi bi-box-seam text-warning fs-3"></i>
                </div>
                <div>
                    <h6 class="text-heading-50 mb-1">عدد المنتجات الناقصة</h6>
                    <h3 class="text-heading fw-bold m-0">{{ $lowStockItems->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="glass-card p-3 rounded mb-4 d-print-none">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="text-muted small mb-1">بحث سريع</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput"
                        class="form-control bg-transparent border-secondary border-opacity-25 text-body"
                        placeholder="اسم المنتج أو الكود (SKU)...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="text-muted small mb-1">تصفية بالتصنيف</label>
                <select id="categoryFilter"
                    class="form-select bg-transparent border-secondary border-opacity-25 text-body">
                    <option value="all" class="text-body">كل التصنيفات</option>
                    @foreach($lowStockItems->pluck('category_name')->unique() as $cat)
                        @if($cat)
                            <option value="{{ $cat }}" class="text-body">{{ $cat }}</option>
                        @endif
                    @endforeach
                    <option value="uncategorized" class="text-body">غير مصنف</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="text-muted small mb-1">تصفية بالماركة</label>
                <select id="brandFilter" class="form-select bg-transparent border-secondary border-opacity-25 text-body">
                    <option value="all" class="text-body">كل الماركات</option>
                    @foreach($lowStockItems->pluck('brand_name')->unique() as $brand)
                        @if($brand)
                            <option value="{{ $brand }}" class="text-body">{{ $brand }}</option>
                        @endif
                    @endforeach
                    <option value="no-brand" class="text-body">بدون ماركة</option>
                </select>
            </div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr class="text-muted border-bottom border-secondary">
                        <th class="py-3 ps-4">بيانات المنتج</th>
                        <th class="py-3">التصنيف / الماركة</th>
                        <th class="py-3">{{ __('Current Stock') }}</th>
                        <th class="py-3">العجز (عن 5)</th>
                        <th class="py-3 pe-4 text-end">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockItems as $item)
                        <tr class="product-row" data-name="{{ strtolower($item->name) }}"
                            data-sku="{{ strtolower($item->sku) }}"
                            data-category="{{ $item->category_name ?? 'uncategorized' }}"
                            data-brand="{{ $item->brand_name ?? 'no-brand' }}">

                            <td class="ps-4">
                                <div class="fw-bold text-body">{{ $item->name }}</div>
                                <div class="small text-muted font-monospace">{{ $item->sku }}</div>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $item->category_name ?? '-' }}</div>
                                <div class="small text-secondary">{{ $item->brand_name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger fs-6 px-3">
                                    {{ $item->current_stock + 0 }}
                                </span>
                            </td>
                            <td>
                                <span class="text-warning fw-bold">
                                    {{ (5 - $item->current_stock) + 0 }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                @if($item->current_stock <= 0)
                                    <span class="badge bg-danger">نفذت الكمية</span>
                                @else
                                    <span class="badge bg-warning text-body">منخفض جداً</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-1 d-block mb-3 text-success opacity-50"></i>
                                لا توجد نواقص حالياً، المخزون في وضع جيد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const brandFilter = document.getElementById('brandFilter');
            const rows = document.querySelectorAll('.product-row');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const category = categoryFilter.value;
                const brand = brandFilter.value;

                rows.forEach(row => {
                    const name = row.dataset.name;
                    const sku = row.dataset.sku;
                    const rowCat = row.dataset.category;
                    const rowBrand = row.dataset.brand;

                    const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);
                    const matchesCategory = category === 'all' || rowCat === category;
                    const matchesBrand = brand === 'all' || rowBrand === brand;

                    if (matchesSearch && matchesCategory && matchesBrand) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterTable);
            categoryFilter.addEventListener('change', filterTable);
            brandFilter.addEventListener('change', filterTable);
        });
    </script>

    <style>
        

        @media print {
            body {
                background: white !important;
                color: black !important;
            }

            

            .btn,
            .input-group,
            .form-select,
            header,
            nav,
            .sidebar,
            #sidebar-wrapper,
            .d-print-none {
                display: none !important;
            }

            .table {
                color: black !important;
                border: 1px solid #ddd !important;
            }

            .table th,
            .table td {
                color: black !important;
                border: 1px solid #ddd !important;
            }

            .table-dark {
                color: black !important;
                background-color: var(--text-primary); !important;
            }

            .badge {
                border: 1px solid #000 !important;
                color: black !important;
                background: transparent !important;
            }

            h2,
            h3,
            h6,
            .text-white,
            .text-muted,
            .fw-bold {
                color: black !important;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .text-warning {
                color: #886400 !important;
            }

            @page {
                margin: 0.5cm;
                size: A4 portrait;
            }
        }
    </style>

    <script src="{{ asset('js/thermal-print.js') }}"></script>
    <script>
        function thermalPrintLowStock() {
            const rows = [];
            @foreach($lowStockItems as $item)
                rows.push([
                    '{{ $item->name }}',
                    '{{ $item->current_stock + 0 }}',
                    '{{ $item->current_stock <= 0 ? "نفذت" : "منخفض" }}'
                ]);
            @endforeach

            printThermal({
                title: 'تقرير النواقص',
                subtitle: '{{ now()->format("Y-m-d") }}',
                summaryCards: [
                    { label: 'عدد الأصناف الناقصة', value: '{{ $lowStockItems->count() }}' },
                ],
                sections: [
                    {
                        title: 'أصناف تحتاج طلب',
                        headers: ['المنتج', 'المخزون', 'الحالة'],
                        rows: rows,
                    }
                ]
            });
        }
    </script>
@endsection