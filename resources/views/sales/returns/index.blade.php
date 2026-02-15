@extends('layouts.app')

@section('title', __('Sales Returns'))

@section('content')
    <div class="row g-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bg-info bg-gradient text-white">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="bg-surface bg-opacity-25 rounded-3 p-2">
                            <i class="bi bi-arrow-return-left fs-4 text-body"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ $returns->total() }}</h2>
                    <div class="small opacity-75">إجمالي المرتجعات</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm mt-4 glass-card">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-heading">مرتجعات المبيعات</h5>
                <small class="text-muted">إدارة ومتابعة طلبات الإرجاع</small>
            </div>
            <div>
                <a href="{{ route('sales-returns.create') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> تسجيل مرتجع جديد
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-body">
                    <thead class="bg-surface bg-opacity-10 text-secondary-50">
                        <tr>
                            <th class="px-4 py-3">{{ __('Return Number') }}</th>
                            <th class="py-3">{{ __('Customer') }}</th>
                            <th class="py-3">المخزن</th>
                            <th class="py-3">{{ __('Date') }}</th>
                            <th class="py-3">{{ __('Value') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td class="px-4">
                                    <span class="font-monospace text-info fw-bold">{{ $return->return_number }}</span>
                                    @if($return->sales_invoice_id)
                                        <div class="small text-muted">فاتورة: {{ $return->salesInvoice->invoice_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $return->customer->name }}</div>
                                    <div class="small text-muted">{{ $return->customer->phone }}</div>
                                </td>
                                <td>{{ $return->warehouse->name }}</td>
                                <td>{{ $return->return_date->format('Y-m-d') }}</td>
                                <td class="fw-bold">{{ number_format($return->total_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $return->status->color() }} bg-opacity-10 text-{{ $return->status->color() }} px-3 py-2 rounded-pill">
                                        {{ $return->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon-glass text-white" type="button"
                                            data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('sales-returns.show', $return->id) }}"><i
                                                        class="bi bi-eye me-2"></i>{{ __('View Details') }}</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                    لا توجد مرتجعات مسجلة حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top-0 py-3">
            {{ $returns->links('partials.pagination') }}
        </div>
    </div>

    <style>
        

        .btn-icon-glass {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
    </style>
@endsection