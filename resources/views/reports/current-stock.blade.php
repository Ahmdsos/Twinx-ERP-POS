@extends('layouts.app')

@section('title', 'تقرير المخزون الحالي')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير المخزون الحالي</h1>
                <p class="text-muted mb-0">Current Stock Report</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>
                    طباعة
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المخزن</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">عدد المنتجات</h6>
                        <h3 class="text-primary">{{ $summary['total_products'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">إجمالي القيمة</h6>
                        <h3 class="text-success">{{ number_format($summary['total_value'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">منخفض المخزون</h6>
                        <h3 class="text-danger">{{ $summary['low_stock_count'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">قائمة المخزون</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-end">سعر التكلفة</th>
                            <th class="text-end">القيمة</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td><code>{{ $product['sku'] }}</code></td>
                                <td>{{ $product['name'] }}</td>
                                <td>{{ $product['category'] }}</td>
                                <td class="text-center">{{ $product['stock'] }} {{ $product['unit'] }}</td>
                                <td class="text-end">{{ number_format($product['cost'], 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($product['value'], 2) }}</td>
                                <td class="text-center">
                                    @if($product['status'] === 'low')
                                        <span class="badge bg-danger">منخفض</span>
                                    @else
                                        <span class="badge bg-success">جيد</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد منتجات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {

                .btn,
                form,
                .sidebar,
                .navbar {
                    display: none !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endpush
@endsection