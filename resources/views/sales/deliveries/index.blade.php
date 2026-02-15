@extends('layouts.app')

@section('title', __('Delivery Notes'))

@section('content')
<div class="container-fluid p-0">
    <!-- Header Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
             <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-blue-500/20"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-gray-400 mb-0">إجمالي الأذونات</h6>
                    <div class="icon-box bg-blue-500/20 text-blue-400 rounded-circle"><i class="bi bi-box-seam fs-4"></i></div>
                </div>
                <h3 class="text-heading fw-bold mb-0">{{ $deliveries->total() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
             <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-yellow-500/20"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-gray-400 mb-0">جاهز للشحن</h6>
                    <div class="icon-box bg-yellow-500/20 text-yellow-400 rounded-circle"><i class="bi bi-box-seam fs-4"></i></div>
                </div>
                <h3 class="text-heading fw-bold mb-0">{{ \Modules\Sales\Models\DeliveryOrder::where('status', \Modules\Sales\Enums\DeliveryStatus::READY)->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
             <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-purple-500/20"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-gray-400 mb-0">{{ __('Shipped') }}</h6>
                    <div class="icon-box bg-purple-500/20 text-purple-400 rounded-circle"><i class="bi bi-truck fs-4"></i></div>
                </div>
                <h3 class="text-heading fw-bold mb-0">{{ \Modules\Sales\Models\DeliveryOrder::where('status', \Modules\Sales\Enums\DeliveryStatus::SHIPPED)->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 w-100 h-100 position-absolute top-0 start-0 opacity-10"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-indigo-200 mb-0">تسليم جديد</h6>
                    <div class="icon-box bg-surface/20 text-body rounded-circle"><i class="bi bi-plus-lg fs-4"></i></div>
                </div>
                <a href="{{ route('deliveries.create') }}" class="btn btn-light w-100 fw-bold stretched-link shadow-lg">إنشاء إذن صرف</a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4 mb-4">
        <form action="{{ route('deliveries.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" name="search" class="form-control glass-input" placeholder="بحث..." value="{{ request('search') }}">
                    <label>رقم الإذن / العميل</label>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="form-floating">
                    <select name="status" class="form-select glass-input">
                        <option value="">{{ __('All') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    <label>{{ __('Status') }}</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="date" name="from_date" class="form-control glass-input" value="{{ request('from_date') }}">
                    <label>من تاريخ</label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 h-100 fw-bold">
                    <i class="bi bi-filter me-2"></i>{{ __('Filter') }}</button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-900 text-gray-400 text-uppercase small">
                    <tr>
                        <th class="ps-4 py-3">رقم الإذن</th>
                        <th class="py-3">مرجع الطلب</th>
                        <th class="py-3">{{ __('Customer') }}</th>
                        <th class="py-3">المخزن</th>
                        <th class="py-3">{{ __('Status') }}</th>
                        <th class="py-3">{{ __('Date') }}</th>
                        <th class="text-end pe-4 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-body">
                    @forelse($deliveries as $delivery)
                        <tr class="border-bottom border-secondary border-opacity-10/5 hover:bg-surface/5 transition-colors">
                            <td class="ps-4 py-3 font-monospace fw-bold text-info">
                                <a href="{{ route('deliveries.show', $delivery->id) }}" class="text-decoration-none text-reset">
                                    {{ $delivery->do_number }}
                                </a>
                            </td>
                            <td class="font-monospace text-gray-300">
                                @if($delivery->salesOrder)
                                    <a href="{{ route('sales-orders.show', $delivery->salesOrder->id) }}" class="text-gray-400 hover:text-body text-decoration-none">
                                        {{ $delivery->salesOrder->so_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td><div class="fw-bold">{{ $delivery->customer->name ?? '-' }}</div></td>
                            <td><span class="badge bg-surface/10 border border-secondary border-opacity-10/10">{{ $delivery->warehouse->name ?? '-' }}</span></td>
                            <td>
                                <span class="badge bg-gray-700 border border-secondary border-opacity-10/20 rounded-pill px-3">
                                    {{ $delivery->status->label() }}
                                </span>
                            </td>
                            <td class="text-gray-400 small">{{ $delivery->delivery_date->format('Y-m-d') }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('deliveries.show', $delivery->id) }}" class="btn btn-icon-glass btn-sm rounded-circle" title="{{ __('View Details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-500">
                                <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                                لا توجد أذونات صرف مطابقة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
            <div class="p-4 border-top border-secondary border-opacity-10/10">
                {{ $deliveries->links('partials.pagination') }}
            </div>
        @endif
    </div>
</div>

<style>
    
    .glass-input {
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid var(--btn-glass-border); !important;
        color: var(--text-primary); !important;
    }
    .glass-input:focus {
        background: rgba(15, 23, 42, 0.9) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .icon-box {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-icon-glass {
        color: var(--text-primary);
        background: var(--btn-glass-bg);
        border: 1px solid var(--btn-glass-border);
    }
    .btn-icon-glass:hover {
        background: rgba(255,255,255,0.15);
    }
     .absolute-glow {
        position: absolute;
        width: 150px; height: 150px;
        filter: blur(40px);
        pointer-events: none;
    }
</style>
@endsection
