@extends('layouts.app')

@section('title', 'تقرير المبيعات حسب المنتج - Twinx ERP')
@section('page-title', 'تقرير المبيعات حسب المنتج')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item">التقارير</li>
    <li class="breadcrumb-item active">المبيعات حسب المنتج</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>المبيعات حسب المنتج</h5>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="row g-2 justify-content-end">
                        <div class="col-auto">
                            <input type="date" name="start_date" class="form-control form-control-sm"
                                value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-auto">
                            <input type="date" name="end_date" class="form-control form-control-sm"
                                value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-filter"></i> تصفية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>SKU</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-end">المبيعات</th>
                            <th class="text-end">الضريبة</th>
                            <th class="text-end">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-monospace">{{ $row->sku }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ $row->category_name ?? '-' }}</td>
                                <td class="text-center">{{ number_format($row->total_qty, 2) }}</td>
                                <td class="text-end">{{ number_format($row->total_subtotal, 2) }}</td>
                                <td class="text-end">{{ number_format($row->total_tax, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_sales, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد بيانات للفترة المحددة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4">الإجمالي</td>
                            <td class="text-center">{{ number_format($totals['total_qty'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total_subtotal'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total_tax'], 2) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['total_sales'], 2) }} ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection