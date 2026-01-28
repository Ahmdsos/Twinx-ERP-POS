@extends('layouts.app')

@section('title', 'سندات استلام البضاعة - Twinx ERP')
@section('page-title', 'سندات استلام البضاعة (GRN)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سندات الاستلام</li>
@endsection

@section('content')
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('grns.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>سند استلام جديد
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $grns->total() }}</strong> سند
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('grns.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم السند">
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
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>بحث
                    </button>
                    <a href="{{ route('grns.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- GRNs Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم السند</th>
                            <th>أمر الشراء</th>
                            <th>المورد</th>
                            <th>المستودع</th>
                            <th>تاريخ الاستلام</th>
                            <th>القيمة</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grns as $grn)
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('grns.show', $grn) }}" class="fw-bold text-decoration-none">
                                        {{ $grn->grn_number }}
                                    </a>
                                </td>
                                <td>
                                    @if($grn->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}">
                                            {{ $grn->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $grn->supplier?->name ?? '-' }}</td>
                                <td>{{ $grn->warehouse?->name ?? '-' }}</td>
                                <td>{{ $grn->received_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="fw-bold">{{ number_format($grn->getTotalValue(), 2) }} ج.م</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$grn->status->value] ?? 'secondary' }}">
                                        {{ $grn->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('grns.show', $grn) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                    لا توجد سندات استلام
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($grns->hasPages())
            <div class="card-footer">
                {{ $grns->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection