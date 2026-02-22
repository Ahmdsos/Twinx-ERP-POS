@extends('layouts.app')

@section('title', 'تقييم المخزون - Stock Valuation')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    <i class="bi bi-boxes me-2 text-success"></i>
                    تقييم المخزون (Stock Valuation)
                </h4>
                <div class="text-muted small">تحليل قيمة البضاعة (تكلفة vs بيع)</div>
            </div>

            <div class="d-flex gap-3 align-items-center">
                <div class="glass-card p-2 rounded px-4 text-center">
                    <div class="text-muted small fw-bold">قيمة التكلفة (Cost)</div>
                    <div class="fw-bold text-info fs-5 text-shadow">{{ number_format($totalCostValue, 2) }} ج.م</div>
                </div>
                <div class="glass-card p-2 rounded px-4 text-center">
                    <div class="text-muted small fw-bold">قيمة البيع (Retail)</div>
                    <div class="fw-bold text-success fs-5 text-shadow">{{ number_format($totalRetailValue, 2) }} ج.م</div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="glass-card p-3 rounded mb-4 d-print-none">
            {{-- Warehouse Filter (server-side) --}}
            <form method="GET" action="{{ route('reports.inventory.valuation') }}" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="text-muted small mb-1">تصفية بالمخزن</label>
                    <select name="warehouse_id"
                        class="form-select bg-transparent border-secondary border-opacity-25 text-body"
                        onchange="this.form.submit()">
                        <option value="" class="text-body">كل المخازن</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" class="text-body" {{ $selectedWarehouse == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- Client-side filters --}}
            <div class="row g-3">
                <div class="col-md-3">
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
                        @foreach($stockValue->pluck('category_name')->unique()->filter() as $cat)
                            <option value="{{ $cat }}" class="text-body">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small mb-1">تصفية بالماركة</label>
                    <select id="brandFilter"
                        class="form-select bg-transparent border-secondary border-opacity-25 text-body">
                        <option value="all" class="text-body">كل الماركات</option>
                        @foreach($stockValue->pluck('brand_name')->unique()->filter() as $brand)
                            <option value="{{ $brand }}" class="text-body">{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button onclick="window.print()" class="btn btn-outline-light w-100" title="طباعة عادية A4">
                        <i class="bi bi-printer me-1"></i> طباعة عادية
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button onclick="thermalPrintValuation()" class="btn btn-outline-warning w-100"
                        title="طباعة حرارية 80mm">
                        <i class="bi bi-receipt me-1"></i> طباعة حرارية
                    </button>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0" id="valuationTable">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="py-3 ps-4">كود الصنف (SKU)</th>
                                <th class="py-3">{{ __('Product Name') }}</th>
                                <th class="py-3">التصنيف / الماركة</th>
                                <th class="py-3 text-center">{{ __('Quantity') }}</th>
                                <th class="py-3 text-center">متوسط التكلفة</th>
                                <th class="py-3 text-center">{{ __('Selling Price') }}</th>
                                <th class="py-3 text-end pe-4">قيمة التكلفة</th>
                                <th class="py-3 text-end pe-4">قيمة البيع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockValue as $item)
                                <tr class="product-row" data-name="{{ strtolower($item->name) }}"
                                    data-sku="{{ strtolower($item->sku) }}" data-category="{{ $item->category_name }}"
                                    data-brand="{{ $item->brand_name }}">

                                    <td class="ps-4 font-monospace text-muted">{{ $item->sku }}</td>
                                    <td class="fw-bold text-body">{{ $item->name }}</td>
                                    <td>
                                        <div class="small text-muted">{{ $item->category_name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $item->brand_name ?? '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-surface bg-opacity-10 text-body border border-secondary border-opacity-25">{{ number_format($item->stock_quantity, 0) }}</span>
                                    </td>
                                    <td class="text-center text-info text-opacity-75">{{ number_format($item->cost_price, 2) }}
                                    </td>
                                    <td class="text-center text-success text-opacity-75">
                                        {{ number_format($item->selling_price, 2) }}
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-info">{{ number_format($item->total_cost_value, 2) }}
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-success">
                                        {{ number_format($item->total_retail_value, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">المخزون فارغ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>     document.addEventListener('DOMContentLoaded', function () {         const searchInput = document.getElementById('searchInput');         const categoryFilter = document.getElementById('categoryFilter');         const brandFilter = document.getElementById('brandFilter');         const rows = document.querySelectorAll('.product-row');
             function filterTable() {             const searchTerm = searchInput.value.toLowerCase();             const category = categoryFilter.value;             const brand = brandFilter.value;
                 rows.forEach(row => {                 const name = row.dataset.name;                 const sku = row.dataset.sku;                 const rowCat = row.dataset.category;                 const rowBrand = row.dataset.brand;
                     const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);                 const matchesCategory = category === 'all' || rowCat === category;                 const matchesBrand = brand === 'all' || rowBrand === brand;
                     if (matchesSearch && matchesCategory && matchesBrand) {                     row.style.display = '';                 } else {                     row.style.display = 'none';                 }             });         }
             searchInput.addEventListener('input', filterTable);         categoryFilter.addEventListener('change', filterTable);         brandFilter.addEventListener('change', filterTable);     });
    </script>

    <style>
        .table-transparent {
            --bs-table-bg: transparent;
            --bs-table-color: #e0e0e0;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03);
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

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
                width: 100% !important;
            }

            .table th,
            .table td {
                color: black !important;
                border: 1px solid #ddd !important;
            }

            .table-dark {
                color: black !important;
                background-color: var(--text-primary);
                !important;
            }

            .badge {
                border: 1px solid #000 !important;
                color: black !important;
                background: transparent !important;
            }

            h4,
            .text-muted,
            .text-white,
            .fw-bold {
                color: black !important;
            }

            .text-info {
                color: #0a7c9e !important;
            }

            .text-success {
                color: #198754 !important;
            }

            .text-shadow {
                text-shadow: none !important;
            }

            @page {
                margin: 0.5cm;
                size: A4 landscape;
            }
        }
    </style>

    <script src="{{ asset('js/thermal-print.js') }}"></script>
    <script>     function thermalPrintValuation() {         const rows = [];         @foreach($stockValue as $item)         rows.push([             '{{ $item->name }}',             '{{ number_format($item->stock_quantity, 0) }}',             '{{ number_format($item->total_cost_value, 2) }}'         ]);         @endforeach
             printThermal({             title: 'تقييم المخزون',             subtitle: '{{ now()->format("Y-m-d") }}',             summaryCards: [                 { label: 'قيمة التكلفة', value: '{{ number_format($totalCostValue, 2) }}' },                 { label: 'قيمة البيع', value: '{{ number_format($totalRetailValue, 2) }}' },                 { label: 'عدد الأصناف', value: '{{ $stockValue->count() }}' },             ],             sections: [                 {                     title: 'الأصناف',                     headers: ['المنتج', 'كمية', 'قيمة التكلفة'],                     rows: rows,                     footer: { label: 'إجمالي التكلفة', value: '{{ number_format($totalCostValue, 2) }}' }                 }             ]         });     }
    </script>
@endsection