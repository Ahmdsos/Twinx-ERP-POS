@extends('layouts.app')

@section('title', 'أوامر التسليم - Twinx ERP')
@section('page-title', 'أوامر التسليم')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أوامر التسليم</li>
@endsection

@section('content')
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('deliveries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>أمر تسليم جديد
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $deliveries->total() }}</strong> أمر
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('deliveries.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> بحث
                    </button>
                    <a href="{{ route('deliveries.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم التسليم</th>
                            <th>رقم أمر البيع</th>
                            <th>العميل</th>
                            <th>المستودع</th>
                            <th>تاريخ التسليم</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td>
                                    <a href="{{ route('deliveries.show', $delivery) }}" class="fw-bold text-decoration-none">
                                        {{ $delivery->do_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('sales-orders.show', $delivery->sales_order_id) }}">
                                        {{ $delivery->salesOrder?->so_number ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $delivery->customer?->name ?? '-' }}</td>
                                <td>{{ $delivery->warehouse?->name ?? '-' }}</td>
                                <td>{{ $delivery->delivery_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($delivery->status->value) {
                                            'draft' => 'secondary',
                                            'ready' => 'info',
                                            'shipped' => 'warning',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $delivery->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('deliveries.show', $delivery) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($delivery->status->value === 'ready')
                                            <form action="{{ route('deliveries.ship', $delivery) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="شحن">
                                                    <i class="bi bi-truck"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if(in_array($delivery->status->value, ['ready', 'shipped']))
                                            <form action="{{ route('deliveries.complete', $delivery) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="إتمام التسليم">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                    لا توجد أوامر تسليم
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($deliveries->hasPages())
            <div class="card-footer">
                {{ $deliveries->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection