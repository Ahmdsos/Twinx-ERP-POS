@extends('layouts.app')

@section('title', 'تقرير المنتجات منخفضة المخزون')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير المنتجات منخفضة المخزون</h1>
                <p class="text-muted mb-0">Low Stock Alert Report</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>
                    طباعة
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center bg-warning bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-muted">منتجات منخفضة المخزون</h6>
                        <h3 class="text-warning">{{ $summary['total_products'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center bg-danger bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-muted">منتجات نفذت تماماً</h6>
                        <h3 class="text-danger">{{ $summary['critical_count'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">قيمة إعادة الطلب</h6>
                        <h3 class="text-primary">{{ number_format($summary['total_reorder_value'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>تنبيه: منتجات تحتاج إعادة طلب</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th class="text-center">المخزون الحالي</th>
                            <th class="text-center">الحد الأدنى</th>
                            <th class="text-center">النقص</th>
                            <th class="text-end">قيمة إعادة الطلب</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="{{ $product['current_stock'] == 0 ? 'table-danger' : '' }}">
                                <td><code>{{ $product['sku'] }}</code></td>
                                <td>{{ $product['name'] }}</td>
                                <td>{{ $product['category'] }}</td>
                                <td class="text-center fw-bold text-danger">{{ $product['current_stock'] }}</td>
                                <td class="text-center">{{ $product['min_stock'] }}</td>
                                <td class="text-center text-warning fw-bold">{{ $product['shortage'] }}</td>
                                <td class="text-end">{{ number_format($product['reorder_value'], 2) }}</td>
                                <td class="text-center">
                                    @if($product['current_stock'] == 0)
                                        <span class="badge bg-danger">نفذ</span>
                                    @else
                                        <span class="badge bg-warning text-dark">منخفض</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    جميع المنتجات بمخزون كافٍ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($products) > 0)
                        <tfoot class="table-secondary">
                            <tr class="fw-bold">
                                <td colspan="5">الإجمالي</td>
                                <td class="text-center">{{ $products->sum('shortage') }}</td>
                                <td class="text-end">{{ number_format($summary['total_reorder_value'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {

                .btn,
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