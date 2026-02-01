@extends('layouts.app')

@section('title', 'فواتير البيع - Twinx ERP')
@section('page-title', 'فواتير البيع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">فواتير البيع</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalPending, 2) }} ج.م</h3>
                        <small>إجمالي المعلق</small>
                    </div>
                    <i class="bi bi-clock fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalOverdue, 2) }} ج.م</h3>
                        <small>فواتير متأخرة</small>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('sales-invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>فاتورة جديدة
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $invoices->total() }}</strong> فاتورة
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('sales-invoices.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم الفاتورة">
                </div>
                <div class="col-md-2">
                    <label class="form-label">العميل</label>
                    <select class="form-select" name="customer_id">
                        <option value="">الكل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" name="status">
                        <option value="">الكل</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('sales-invoices.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>تاريخ الفاتورة</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="{{ $invoice->isOverdue() ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('sales-invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    @if($invoice->isOverdue())
                                        <br>
                                        <small class="text-danger">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            متأخرة {{ $invoice->getDaysOverdue() }} يوم
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $invoice->customer?->name ?? '-' }}</td>
                                <td>{{ $invoice->invoice_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-danger">{{ number_format($invoice->balance_due, 2) }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($invoice->status->value) {
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'partial' => 'info',
                                            'paid' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('sales-invoices.show', $invoice) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('sales-invoices.print', $invoice) }}"
                                            class="btn btn-outline-secondary" title="طباعة / PDF" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        @if($invoice->status->canReceivePayment())
                                            <a href="{{ route('customer-payments.create', ['invoice_id' => $invoice->id]) }}"
                                                class="btn btn-outline-success" title="تسجيل دفعة">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    لا توجد فواتير
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection