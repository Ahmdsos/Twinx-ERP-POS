@extends('layouts.app')

@section('title', 'تقييم المخزون - Stock Valuation')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1">
                    <i class="bi bi-boxes me-2 text-success"></i>
                    تقييم المخزون (Stock Valuation)
                </h4>
                <div class="text-white-50 small">تحليل قيمة البضاعة (تطلفة vs بيع)</div>
            </div>

            <div class="d-flex gap-3">
                <div class="glass-card p-2 rounded px-4 text-center">
                    <div class="text-white-50 small fw-bold">قيمة التكلفة (Cost)</div>
                    <div class="fw-bold text-info fs-5 text-shadow">{{ number_format($totalCostValue, 2) }} ج.م</div>
                </div>
                <div class="glass-card p-2 rounded px-4 text-center">
                    <div class="text-white-50 small fw-bold">قيمة البيع (Retail)</div>
                    <div class="fw-bold text-success fs-5 text-shadow">{{ number_format($totalRetailValue, 2) }} ج.م</div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="glass-card p-3 rounded mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="text-white-50 small mb-1">بحث سريع</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-white-50">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control bg-transparent border-secondary border-opacity-25 text-white" 
                               placeholder="اسم المنتج أو الكود (SKU)...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-white-50 small mb-1">تصفية بالتصنيف</label>
                    <select id="categoryFilter" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="all" class="text-dark">كل التصنيفات</option>
                        @foreach($stockValue->pluck('category_name')->unique()->filter() as $cat)
                            <option value="{{ $cat }}" class="text-dark">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="text-white-50 small mb-1">تصفية بالماركة</label>
                    <select id="brandFilter" class="form-select bg-transparent border-secondary border-opacity-25 text-white">
                        <option value="all" class="text-dark">كل الماركات</option>
                        @foreach($stockValue->pluck('brand_name')->unique()->filter() as $brand)
                            <option value="{{ $brand }}" class="text-dark">{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button onclick="window.print()" class="btn btn-outline-light w-100">
                        <i class="bi bi-printer me-2"></i> طباعة
                    </button>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0" id="valuationTable">
                        <thead>
                            <tr class="text-white-50 small text-uppercase">
                                <th class="py-3 ps-4">كود الصنف (SKU)</th>
                                <th class="py-3">اسم المنتج</th>
                                <th class="py-3">التصنيف / الماركة</th>
                                <th class="py-3 text-center">الكمية</th>
                                <th class="py-3 text-center">متوسط التكلفة</th>
                                <th class="py-3 text-center">سعر البيع</th>
                                <th class="py-3 text-end pe-4">قيمة التكلفة</th>
                                <th class="py-3 text-end pe-4">قيمة البيع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockValue as $item)
                                <tr class="product-row" 
                                    data-name="{{ strtolower($item->name) }}" 
                                    data-sku="{{ strtolower($item->sku) }}"
                                    data-category="{{ $item->category_name }}"
                                    data-brand="{{ $item->brand_name }}">
                                    
                                    <td class="ps-4 font-monospace text-white-50">{{ $item->sku }}</td>
                                    <td class="fw-bold text-white">{{ $item->name }}</td>
                                    <td>
                                        <div class="small text-white-50">{{ $item->category_name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $item->brand_name ?? '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-white bg-opacity-10 text-white border border-secondary border-opacity-25">{{ number_format($item->stock_quantity, 0) }}</span>
                                    </td>
                                    <td class="text-center text-info text-opacity-75">{{ number_format($item->cost_price, 2) }}</td>
                                    <td class="text-center text-success text-opacity-75">{{ number_format($item->selling_price, 2) }}</td>
                                    <td class="text-end pe-4 fw-bold text-info">{{ number_format($item->total_cost_value, 2) }}</td>
                                    <td class="text-end pe-4 fw-bold text-success">{{ number_format($item->total_retail_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-white-50">المخزون فارغ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const brandFilter = document.getElementById('brandFilter');
            const rows = document.querySelectorAll('.product-row');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
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
            background: rgba(30, 30, 40, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }

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
            #sidebar-wrapper, 
            .d-print-none {
                display: none !important;
            }

            /* Hide Filters Section entirely */
            .glass-card.p-3.rounded.mb-4 {
                display: none !important;
            }

            /* Force Table Styling for Print */
            .table {
                color: black !important;
                border: 1px solid #ddd !important;
                width: 100% !important;
            }
            .table th, .table td {
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
            
            /* Text Visibility */
            h4, .text-white-50, .text-white {
                color: black !important;
            }
            
            @page {
                margin: 0.5cm;
                size: A4 portrait;
            }
        }
    </style>
@endsection