@extends('layouts.app')

@section('title', 'سجل الائتمان - ' . $customer->name . ' - Twinx ERP')
@section('page-title', 'سجل الائتمان')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></li>
    <li class="breadcrumb-item active">سجل الائتمان</li>
@endsection

@section('content')
    <div class="row mb-4">
        <!-- Customer Info Card -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-3">{{ $customer->name }}</h5>
                    <p class="mb-1 text-muted"><i class="bi bi-hash me-1"></i>{{ $customer->code }}</p>
                    <p class="mb-1"><i class="bi bi-telephone me-1"></i>{{ $customer->phone ?? '-' }}</p>
                    <p class="mb-0"><i class="bi bi-credit-card me-1"></i>حد الائتمان:
                        {{ number_format($customer->credit_limit, 2) }} ج.م</p>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ number_format($stats->total_amount, 2) }}</h4>
                            <small>إجمالي الفواتير</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ number_format($stats->total_paid, 2) }}</h4>
                            <small>إجمالي المسدد</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ number_format($stats->total_balance, 2) }}</h4>
                            <small>الرصيد المستحق</small>
                        </div>
                    </div>
                </div>
            </div>
            @if($stats->overdue_count > 0)
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>{{ $stats->overdue_count }}</strong> فواتير متأخرة بقيمة
                    <strong>{{ number_format($stats->overdue_amount, 2) }} ج.م</strong>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>تفاصيل الفواتير</h5>
            <span class="badge bg-secondary">{{ $stats->total_invoices }} فاتورة</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم الفاتورة</th>
                            <th>الاستحقاق</th>
                            <th class="text-end">المبلغ</th>
                            <th class="text-end">المسدد</th>
                            <th class="text-end">المتبقي</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr class="{{ $inv->is_overdue ? 'table-warning' : '' }}">
                                <td>{{ $inv->date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('sales-invoices.show', $inv->id) }}">{{ $inv->invoice_number }}</a>
                                </td>
                                <td>
                                    @if($inv->due_date)
                                        {{ $inv->due_date->format('Y-m-d') }}
                                        @if($inv->is_overdue)
                                            <span class="badge bg-danger">متأخر</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($inv->total, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($inv->paid, 2) }}</td>
                                <td class="text-end {{ $inv->balance > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ number_format($inv->balance, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($inv->balance <= 0)
                                        <span class="badge bg-success">مسدد</span>
                                    @elseif($inv->paid > 0)
                                        <span class="badge bg-warning">جزئي</span>
                                    @else
                                        <span class="badge bg-secondary">غير مسدد</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    لا توجد فواتير
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">الإجمالي</td>
                            <td class="text-end">{{ number_format($stats->total_amount, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($stats->total_paid, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($stats->total_balance, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i>العودة للعميل
        </a>
        <a href="{{ route('customers.statement', $customer) }}" class="btn btn-outline-primary">
            <i class="bi bi-file-text me-1"></i>كشف الحساب
        </a>
    </div>
@endsection