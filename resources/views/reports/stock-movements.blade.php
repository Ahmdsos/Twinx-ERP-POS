@extends('layouts.app')

@section('title', 'تقرير حركة المخزون')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير حركة المخزون</h1>
                <p class="text-muted mb-0">Stock Movements Report</p>
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
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المنتج</label>
                        <select name="product_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">المخزن</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
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
                <div class="card border-0 shadow-sm text-center bg-success bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-muted">إجمالي الوارد</h6>
                        <h3 class="text-success">+ {{ number_format($summary['total_in']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center bg-danger bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-muted">إجمالي الصادر</h6>
                        <h3 class="text-danger">- {{ number_format($summary['total_out']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">عدد الحركات</h6>
                        <h3 class="text-primary">{{ $summary['total_movements'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movements Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">سجل الحركات</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>التاريخ</th>
                            <th>المنتج</th>
                            <th>المخزن</th>
                            <th class="text-center">النوع</th>
                            <th class="text-center">الكمية</th>
                            <th>المرجع</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $mv)
                            <tr>
                                <td>{{ $mv->movement_date }}</td>
                                <td>{{ $mv->product?->name ?? '-' }}</td>
                                <td>{{ $mv->warehouse?->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($mv->movement_type === 'in')
                                        <span class="badge bg-success">وارد</span>
                                    @else
                                        <span class="badge bg-danger">صادر</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold {{ $mv->movement_type === 'in' ? 'text-success' : 'text-danger' }}">
                                    {{ $mv->movement_type === 'in' ? '+' : '-' }}{{ $mv->quantity }}
                                </td>
                                <td><small>{{ $mv->reference_type }}</small></td>
                                <td><small class="text-muted">{{ Str::limit($mv->notes, 30) }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد حركات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movements->hasPages())
                <div class="card-footer">
                    {{ $movements->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                .btn, form, .sidebar, .navbar, .pagination { display: none !important; }
                .card { border: 1px solid #ddd !important; box-shadow: none !important; }
            }
        </style>
    @endpush
@endsection
