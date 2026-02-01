@extends('layouts.app')

@section('title', 'أوامر الشراء - Twinx ERP')
@section('page-title', 'أوامر الشراء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أوامر الشراء</li>
@endsection

@section('content')
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>أمر شراء جديد
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $purchaseOrders->total() }}</strong> أمر
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('purchase-orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم أمر الشراء">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المورد</label>
                    <select class="form-select" name="supplier_id">
                        <option value="">الكل</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
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
                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم أمر الشراء</th>
                            <th>المورد</th>
                            <th>تاريخ الطلب</th>
                            <th>موعد التسليم المتوقع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>نسبة الاستلام</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'pending' => 'warning',
                                    'approved' => 'info',
                                    'sent' => 'primary',
                                    'partial' => 'warning',
                                    'received' => 'success',
                                    'cancelled' => 'danger',
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="fw-bold text-decoration-none">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td>{{ $po->supplier?->name ?? '-' }}</td>
                                <td>{{ $po->order_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $po->expected_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="fw-bold">{{ number_format($po->total, 2) }} ج.م</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$po->status->value] ?? 'secondary' }}">
                                        {{ $po->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    @php $percentage = $po->getReceivedPercentage(); @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $percentage >= 100 ? 'bg-success' : 'bg-warning' }}"
                                            style="width: {{ $percentage }}%">
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($po->canEdit())
                                            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-outline-warning"
                                                title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($po->canReceive())
                                            <a href="{{ route('grns.create', ['purchase_order_id' => $po->id]) }}"
                                                class="btn btn-outline-success" title="استلام البضاعة">
                                                <i class="bi bi-box-seam"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-cart fs-1 d-block mb-2"></i>
                                    لا توجد أوامر شراء
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer">
                {{ $purchaseOrders->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection