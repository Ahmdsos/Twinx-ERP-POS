@extends('layouts.app')

@section('title', 'ملخص مشتريات الموردين')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">ملخص مشتريات الموردين</h1>
                <p class="text-muted mb-0">تقرير المشتريات حسب المورد</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>
                طباعة
            </button>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">المورد</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">جميع الموردين</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->code }} - {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-danger text-white">
                    <div class="card-body">
                        <h6 class="opacity-75 mb-1">إجمالي المشتريات</h6>
                        <h3 class="mb-0">{{ number_format($totals['total_purchases'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                        <h6 class="opacity-75 mb-1">المدفوع</h6>
                        <h3 class="mb-0">{{ number_format($totals['total_paid'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="opacity-75 mb-1">المستحق للموردين</h6>
                        <h3 class="mb-0">{{ number_format($totals['total_due'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body">
                        <h6 class="opacity-75 mb-1">عدد الفواتير</h6>
                        <h3 class="mb-0">{{ $totals['invoice_count'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    التفاصيل
                    <small class="text-muted">({{ $startDate->format('Y/m/d') }} - {{ $endDate->format('Y/m/d') }})</small>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>كود المورد</th>
                            <th>اسم المورد</th>
                            <th class="text-center">عدد الفواتير</th>
                            <th class="text-end">إجمالي قبل الضريبة</th>
                            <th class="text-end">الضريبة</th>
                            <th class="text-end">الإجمالي</th>
                            <th class="text-end">المدفوع</th>
                            <th class="text-end">المستحق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('suppliers.show', $row->supplier_id) }}">
                                        {{ $row->supplier_code }}
                                    </a>
                                </td>
                                <td>{{ $row->supplier_name }}</td>
                                <td class="text-center">{{ $row->invoice_count }}</td>
                                <td class="text-end">{{ number_format($row->total_subtotal, 2) }}</td>
                                <td class="text-end">{{ number_format($row->total_tax, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_purchases, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($row->total_paid, 2) }}</td>
                                <td class="text-end text-warning">{{ number_format($row->total_due, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    لا توجد بيانات للفترة المحددة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($data->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">الإجمالي</td>
                                <td class="text-center">{{ $totals['invoice_count'] }}</td>
                                <td class="text-end">{{ number_format($totals['total_subtotal'], 2) }}</td>
                                <td class="text-end">{{ number_format($totals['total_tax'], 2) }}</td>
                                <td class="text-end">{{ number_format($totals['total_purchases'], 2) }}</td>
                                <td class="text-end text-success">{{ number_format($totals['total_paid'], 2) }}</td>
                                <td class="text-end text-warning">{{ number_format($totals['total_due'], 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection