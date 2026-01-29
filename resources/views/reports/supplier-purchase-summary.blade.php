@extends('layouts.app')

@section('title', 'تقرير مشتريات الموردين - Twinx ERP')
@section('page-title', 'تقرير مشتريات الموردين')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
    <li class="breadcrumb-item active">مشتريات الموردين</li>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('reports.purchases.suppliers') }}" method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" name="start_date"
                                value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter me-2"></i>عرض التقرير
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">إجمالي المشتريات</h6>
                    <h3 class="mb-0">{{ number_format($totals['purchases'], 2) }} ج.م</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">إجمالي المدفوعات</h6>
                    <h3 class="mb-0">{{ number_format($totals['payments'], 2) }} ج.م</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">عدد الفواتير</h6>
                    <h3 class="mb-0">{{ number_format($totals['invoices']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تفاصيل المشتريات حسب المورد</h5>
            <div class="text-muted small">
                الفترة: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>المورد</th>
                            <th>عدد الفواتير</th>
                            <th>إجمالي المشتريات</th>
                            <th>إجمالي المدفوعات</th>
                            <th>الرصيد المستحق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-decoration-none fw-bold">
                                        {{ $supplier->name }}
                                    </a>
                                    <div class="small text-muted">{{ $supplier->code }}</div>
                                </td>
                                <td>{{ number_format($supplier->invoices_count) }}</td>
                                <td class="fw-bold text-primary">{{ number_format($supplier->total_purchases, 2) }}</td>
                                <td class="text-success">{{ number_format($supplier->total_payments, 2) }}</td>
                                <td>
                                    @php
                                        $balance = $supplier->purchaseInvoices()->where('status', '!=', 'cancelled')->sum('balance_due');
                                    @endphp
                                    <span class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($balance, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                                    لا توجد بيانات في هذه الفترة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2">الإجمالي</td>
                            <td>{{ number_format($totals['invoices']) }}</td>
                            <td>{{ number_format($totals['purchases'], 2) }}</td>
                            <td>{{ number_format($totals['payments'], 2) }}</td>
                            <td>-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer text-center no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-2"></i>طباعة التقرير
            </button>
        </div>
    </div>
@endsection