@extends('layouts.app')

@section('title', 'أوامر الشراء')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-blue shadow-neon-blue">
                    <i class="bi bi-cart3 fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">أوامر الشراء</h2>
                    <p class="mb-0 text-gray-400 small">إدارة طلبات الشراء من الموردين</p>
                </div>
            </div>
            <a href="{{ route('purchase-orders.create') }}"
                class="btn btn-action-blue d-flex align-items-center gap-2 shadow-lg">
                <i class="bi bi-plus-lg"></i>
                <span class="fw-bold">أمر شراء جديد</span>
            </a>
        </div>

        <!-- Filters (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-white-5 rounded-4 p-4 mb-5">
            <form action="{{ route('purchase-orders.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-blue-400 x-small fw-bold text-uppercase ps-1">بحث</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-blue"
                            value="{{ request('search') }}" placeholder="رقم الطلب...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-blue-400 x-small fw-bold text-uppercase ps-1">المورد</label>
                    <select name="supplier_id"
                        class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-blue-400 x-small fw-bold text-uppercase ps-1">الحالة</label>
                    <select name="status" class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-blue-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i> تصفية
                    </button>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-blue">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">رقم الطلب</th>
                            <th>المورد</th>
                            <th>تاريخ الطلب</th>
                            <th>تاريخ التوقع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $order)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 font-monospace text-blue-300">
                                    <a href="{{ route('purchase-orders.show', $order->id) }}"
                                        class="text-decoration-none text-blue-300 hover-text-white">
                                        {{ $order->po_number }}
                                    </a>
                                </td>
                                <td class="fw-bold text-white">{{ $order->supplier->name }}</td>
                                <td class="text-gray-400 x-small">{{ $order->order_date->format('Y-m-d') }}</td>
                                <td class="text-gray-400 x-small">
                                    {{ $order->expected_date ? $order->expected_date->format('Y-m-d') : '-' }}</td>
                                <td class="fw-bold text-white">{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @php
                                        $color = match ($order->status->value) {
                                            'received' => 'green',
                                            'approved' => 'blue',
                                            'draft' => 'gray',
                                            'cancelled' => 'red',
                                            default => 'purple'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}-500 bg-opacity-10 text-{{ $color }}-400">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn-icon-glass"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($order->status->value == 'approved')
                                            <a href="{{ route('grns.create', ['purchase_order_id' => $order->id]) }}"
                                                class="btn-icon-glass text-green-400 hover-green" title="استلام بضاعة">
                                                <i class="bi bi-box-seam"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-cart-x fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">لا توجد أوامر شراء</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseOrders->hasPages())
                <div class="p-4 border-top border-white-5">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .shadow-neon-blue {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }

        .text-blue-400 {
            color: #60a5fa !important;
        }

        .text-blue-300 {
            color: #93c5fd !important;
        }

        .bg-blue-500 {
            background: #3b82f6 !important;
        }

        .border-top-gradient-blue {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #3b82f6, #60a5fa) 1;
        }

        .btn-action-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-blue-glass {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(96, 165, 250, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-blue-glass:hover {
            background: rgba(59, 130, 246, 0.25);
            color: white;
            border-color: #60a5fa;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .focus-ring-blue:focus {
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.1) !important;
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: #94a3b8;
            font-weight: 600;
            padding: 1rem;
        }

        .table-dark-custom td {
            padding: 1rem;
        }

        .group-hover-actions:hover .group-hover-visible {
            opacity: 1 !important;
        }

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            transition: 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
    </style>
@endsection