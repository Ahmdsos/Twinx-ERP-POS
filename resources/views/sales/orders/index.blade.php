@extends('layouts.app')

@section('title', 'أوامر البيع')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-blue-500/20"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-gray-400 mb-0">إجمالي الأوامر</h6>
                    <div class="icon-box bg-blue-500/20 text-blue-400 rounded-circle"><i class="bi bi-cart-check fs-4"></i></div>
                </div>
                <h3 class="text-white fw-bold mb-0">{{ $orders->total() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-yellow-500/20"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-gray-400 mb-0">قيد التنفيذ</h6>
                    <div class="icon-box bg-yellow-500/20 text-yellow-400 rounded-circle"><i class="bi bi-hourglass-split fs-4"></i></div>
                </div>
                <h3 class="text-white fw-bold mb-0">{{ \Modules\Sales\Models\SalesOrder::status(\Modules\Sales\Enums\SalesOrderStatus::PROCESSING)->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-green-500/20"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-gray-400 mb-0">تم التسليم</h6>
                    <div class="icon-box bg-green-500/20 text-green-400 rounded-circle"><i class="bi bi-check-all fs-4"></i></div>
                </div>
                <h3 class="text-white fw-bold mb-0">{{ \Modules\Sales\Models\SalesOrder::status(\Modules\Sales\Enums\SalesOrderStatus::DELIVERED)->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 w-100 h-100 position-absolute top-0 start-0 opacity-10"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-blue-200 mb-0">إنشاء جديد</h6>
                    <div class="icon-box bg-white/20 text-white rounded-circle"><i class="bi bi-plus-lg fs-4"></i></div>
                </div>
                <a href="{{ route('sales-orders.create') }}" class="btn btn-light w-100 fw-bold stretched-link shadow-lg">أمر بيع جديد</a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4 mb-4">
        <form action="{{ route('sales-orders.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" name="search" class="form-control glass-input" placeholder="بحث..." value="{{ request('search') }}">
                    <label>رقم الأمر / العميل</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <select name="status" class="form-select glass-input">
                        <option value="">الكل</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    <label>الحالة</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <select name="customer_id" class="form-select glass-input">
                        <option value="">الكل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    <label>العميل</label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 h-100 fw-bold">
                    <i class="bi bi-filter me-2"></i> تصفية
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-900 text-gray-400 text-uppercase small">
                    <tr>
                        <th class="ps-4 py-3">رقم الأمر</th>
                        <th class="py-3">العميل</th>
                        <th class="py-3">المخزن</th>
                        <th class="py-3">الحالة</th>
                        <th class="py-3">الإجمالي</th>
                        <th class="py-3">التاريخ</th>
                        <th class="text-end pe-4 py-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-white">
                    @forelse($orders as $order)
                        <tr class="border-bottom border-white/5 hover:bg-white/5 transition-colors">
                            <td class="ps-4 py-3 font-monospace fw-bold text-blue-300">
                                <a href="{{ route('sales-orders.show', $order->id) }}" class="text-decoration-none text-reset">
                                    {{ $order->so_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $order->customer->name }}</div>
                            </td>
                            <td><span class="badge bg-white/10 border border-white/10">{{ $order->warehouse->name ?? '-' }}</span></td>
                            <td>
                                <span class="badge {{ $order->status->badgeClass() }} border border-white/20 rounded-pill px-3">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ number_format($order->total, 2) }}</td>
                            <td class="text-gray-400 small">{{ $order->order_date->format('Y-m-d') }}</td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-icon-glass btn-sm rounded-circle" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border border-white/10">
                                        <li><a class="dropdown-item" href="{{ route('sales-orders.show', $order->id) }}"><i class="bi bi-eye me-2"></i> عرض</a></li>
                                        @if($order->canEdit())
                                            <li><a class="dropdown-item text-warning" href="{{ route('sales-orders.edit', $order->id) }}"><i class="bi bi-pencil me-2"></i> تعديل</a></li>
                                        @endif
                                        <li><a class="dropdown-item text-success" href="{{ route('sales-orders.print', $order->id) }}" target="_blank"><i class="bi bi-printer me-2"></i> طباعة</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-500">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                لا توجد أوامر بيع مطابقة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-4 border-top border-white/10">
                {{ $orders->links('partials.pagination') }}
            </div>
        @endif
    </div>
</div>

<style>
    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
    }
    .glass-input {
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
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
        color: white;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
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
