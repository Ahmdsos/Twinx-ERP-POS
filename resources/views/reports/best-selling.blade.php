@extends('layouts.app')

@section('title', 'أفضل المنتجات مبيعاً')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">أفضل المنتجات مبيعاً</h1>
                <p class="text-muted mb-0">Best Selling Products</p>
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
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">عدد المنتجات</label>
                        <select name="limit" class="form-select">
                            <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
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

        <!-- Best Selling Products Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">أفضل {{ $limit }} منتج</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>SKU</th>
                            <th>المنتج</th>
                            <th class="text-center">الكمية المباعة</th>
                            <th class="text-end">إجمالي المبيعات</th>
                            <th class="text-center">الرتبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $maxSales = $data->max('total_sales'); @endphp
                        @forelse($data as $index => $product)
                            <tr>
                                <td>
                                    @if($index < 3)
                                        <span
                                            class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }}">
                                            {{ $index + 1 }}
                                        </span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>{{ $product->name }}</td>
                                <td class="text-center">{{ number_format($product->qty_sold) }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($product->total_sales, 2) }}</td>
                                <td class="text-center">
                                    @php $pct = $maxSales > 0 ? ($product->total_sales / $maxSales) * 100 : 0; @endphp
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">لا توجد مبيعات</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td colspan="3">الإجمالي</td>
                            <td class="text-center">{{ number_format($data->sum('qty_sold')) }}</td>
                            <td class="text-end">{{ number_format($data->sum('total_sales'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
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