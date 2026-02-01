@extends('layouts.app')

@section('title', 'تحليل هامش الربح - Twinx ERP')
@section('page-title', 'تحليل هامش الربح')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item">التقارير</li>
    <li class="breadcrumb-item active">تحليل هامش الربح</li>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>تحليل هامش الربح</h5>
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
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ number_format($totals['total_revenue'], 2) }}</h4>
                    <small>إجمالي المبيعات</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ number_format($totals['total_cost'], 2) }}</h4>
                    <small>إجمالي التكلفة</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ number_format($totals['total_profit'], 2) }}</h4>
                    <small>صافي الربح</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ number_format($totals['avg_margin'], 1) }}%</h4>
                    <small>متوسط هامش الربح</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-end">الإيرادات</th>
                            <th class="text-end">التكلفة</th>
                            <th class="text-end">الربح</th>
                            <th class="text-center">الهامش %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td class="text-monospace">{{ $row->sku }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ $row->category_name ?? '-' }}</td>
                                <td class="text-center">{{ number_format($row->total_qty) }}</td>
                                <td class="text-end">{{ number_format($row->total_revenue, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($row->total_cost, 2) }}</td>
                                <td class="text-end {{ $row->profit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ number_format($row->profit, 2) }}
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge {{ $row->margin_percent >= 20 ? 'bg-success' : ($row->margin_percent >= 10 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ number_format($row->margin_percent, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    لا توجد بيانات للفترة المحددة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">الإجمالي</td>
                            <td class="text-center">{{ number_format($totals['total_qty']) }}</td>
                            <td class="text-end">{{ number_format($totals['total_revenue'], 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($totals['total_cost'], 2) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['total_profit'], 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ number_format($totals['avg_margin'], 1) }}%</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection