@extends('layouts.app')

@section('title', 'أوامر البيع - Twinx ERP')
@section('page-title', 'أوامر البيع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أوامر البيع</li>
@endsection

@section('content')
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('sales-orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>أمر بيع جديد
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $orders->total() }}</strong> أمر
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('sales-orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم الأمر...">
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
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الأمر</th>
                            <th>التاريخ</th>
                            <th>العميل</th>
                            <th>المستودع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>التسليم</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('sales-orders.show', $order) }}" class="fw-bold text-decoration-none">
                                        {{ $order->so_number }}
                                    </a>
                                </td>
                                <td>{{ $order->order_date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('customers.show', $order->customer_id) }}">
                                        {{ $order->customer?->name ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $order->warehouse?->name ?? '-' }}</td>
                                <td class="fw-bold">{{ number_format($order->total, 2) }} ج.م</td>
                                <td>
                                    @php
                                        $statusClass = match ($order->status->value) {
                                            'draft' => 'secondary',
                                            'confirmed' => 'primary',
                                            'processing' => 'info',
                                            'partial' => 'warning',
                                            'delivered' => 'success',
                                            'invoiced' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->getDeliveredPercentage() > 0)
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success"
                                                style="width: {{ $order->getDeliveredPercentage() }}%">
                                                {{ $order->getDeliveredPercentage() }}%
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('sales-orders.show', $order) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($order->canEdit())
                                            <a href="{{ route('sales-orders.edit', $order) }}" class="btn btn-outline-warning"
                                                title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($order->status->value === 'draft')
                                            <form action="{{ route('sales-orders.confirm', $order) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="تأكيد">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد أوامر بيع
                                    <div class="mt-3">
                                        <a href="{{ route('sales-orders.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>إنشاء أول أمر بيع
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection