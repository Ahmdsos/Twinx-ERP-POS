@extends('layouts.app')

@section('title', 'تقرير النواقص - Low Stock Report')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold"><i class="bi bi-exclamation-triangle me-2 text-warning"></i> تقرير النواقص</h2>
            <p class="text-white-50">المنتجات التي وصلت إلى حد الطلب أو أقل.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-light">
                <i class="bi bi-printer me-2"></i> طباعة
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
                    <h6 class="text-white-50 mb-1">عدد المنتجات الناقصة</h6>
                    <h3 class="text-white fw-bold m-0">{{ $lowStockItems->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <!-- Filters Section -->
    <div class="glass-card p-3 rounded mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="text-white-50 small mb-1">بحث سريع</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput"
                        class="form-control bg-transparent border-secondary border-opacity-25 text-white"
                        placeholder="اسم المنتج أو الكود (SKU)...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="text-white-50 small mb-1">تصفية بالتصنيف</label>
                <select id="categoryFilter"
                    class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                    <option value="all" class="text-dark">كل التصنيفات</option>
                    @foreach($lowStockItems->pluck('category_name')->unique() as $cat)
                        @if($cat)
                            <option value="{{ $cat }}" class="text-dark">{{ $cat }}</option>
                        @endif
                    @endforeach
                    <option value="uncategorized" class="text-dark">غير مصنف</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="text-white-50 small mb-1">تصفية بالماركة</label>
                <select id="brandFilter" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                    <option value="all" class="text-dark">كل الماركات</option>
                    @foreach($lowStockItems->pluck('brand_name')->unique() as $brand)
                        @if($brand)
                            <option value="{{ $brand }}" class="text-dark">{{ $brand }}</option>
                        @endif
                    @endforeach
                    <option value="no-brand" class="text-dark">بدون ماركة</option>
                </select>
            </div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th class="py-3 ps-4">بيانات المنتج</th>
                        <th class="py-3">التصنيف / الماركة</th>
                        <th class="py-3">المخزون الحالي</th>
                        <th class="py-3">العجز (عن 5)</th>
                        <th class="py-3 pe-4 text-end">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockItems as $item)
                        <tr class="product-row" data-name="{{ strtolower($item->name) }}"
                            data-sku="{{ strtolower($item->sku) }}"
                            data-category="{{ $item->category_name ?? 'uncategorized' }}"
                            data-brand="{{ $item->brand_name ?? 'no-brand' }}">

                            <td class="ps-4">
                                <div class="fw-bold text-white">{{ $item->name }}</div>
                                <div class="small text-white-50 font-monospace">{{ $item->sku }}</div>
                            </td>
                            <td>
                                <div class="small text-white-50">{{ $item->category_name ?? '-' }}</div>
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
                                    <span class="badge bg-warning text-dark">منخفض جداً</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-white-50">
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
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
            }

            .glass-card {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                color: black !important;
            }

            /* Hide UI Elements */
            .btn,
            .input-group,
            .form-select,
            header,
            nav,
            .sidebar,
            /* Assuming sidebar class */
            #sidebar-wrapper,
            /* Common ID */
            .d-print-none {
                display: none !important;
            }

            /* Force Table Styling for Print */
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
                background-color: white !important;
            }

            /* Ensure Badges are readable */
            .badge {
                border: 1px solid #000 !important;
                color: black !important;
                background: transparent !important;
            }

            /* Hide Filters Section entirely */
            .glass-card.p-3.rounded.mb-4 {
                display: none !important;
            }

            /* Page Layout */
            @page {
                margin: 0.5cm;
                size: A4 portrait;
            }

            /* Reveal header for print context */
            h2,
            h6,
            h3 {
                color: black !important;
            }
        }
    </style>
@endsection