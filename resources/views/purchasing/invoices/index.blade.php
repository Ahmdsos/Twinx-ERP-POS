@extends('layouts.app')

@section('title', 'فواتير الشراء')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-cyan shadow-neon-cyan">
                    <i class="bi bi-receipt fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">فواتير الشراء</h2>
                    <p class="mb-0 text-gray-400 small">إدارة الفواتير والمستحقات للموردين</p>
                </div>
            </div>
            <a href="{{ route('purchase-invoices.create') }}"
                class="btn btn-action-cyan d-flex align-items-center gap-2 shadow-lg">
                <i class="bi bi-plus-lg"></i>
                <span class="fw-bold">فاتورة جديدة</span>
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-cyan-400 x-small fw-bold text-uppercase tracking-wide">إجمالي المستحقات</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['total_pending'], 2) }} <small
                                    class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-cyan-500 bg-opacity-10 text-cyan-400">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-red-400 x-small fw-bold text-uppercase tracking-wide">فواتير متأخرة</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['total_overdue'], 2) }} <small
                                    class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-red-500 bg-opacity-10 text-red-400">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-purple-400 x-small fw-bold text-uppercase tracking-wide">عدد المتأخرات</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ $stats['overdue_count'] }} <small
                                    class="fs-6 text-gray-400">فاتورة</small></h2>
                        </div>
                        <div class="icon-circle bg-purple-500 bg-opacity-10 text-purple-400">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-white-5 rounded-4 p-4 mb-5">
            <form action="{{ route('purchase-invoices.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">بحث</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-cyan"
                            value="{{ request('search') }}" placeholder="رقم الفاتورة...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">المورد</label>
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
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">الحالة</label>
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
                    <button type="submit" class="btn btn-cyan-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i> تصفية
                    </button>
                </div>
            </form>
        </div>

        <!-- Invoices Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-cyan">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">رقم الفاتورة</th>
                            <th>المورد</th>
                            <th>تاريخ الفاتورة</th>
                            <th>الاستحقاق</th>
                            <th>القيمة</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 font-monospace text-cyan-300">
                                    <a href="{{ route('purchase-invoices.show', $invoice->id) }}"
                                        class="text-decoration-none text-cyan-300 hover-text-white">
                                        {{ $invoice->invoice_number }}
                                        <div class="x-small text-gray-500">{{ $invoice->supplier_invoice_number }}</div>
                                    </a>
                                </td>
                                <td class="fw-bold text-white">{{ $invoice->supplier->name }}</td>
                                <td class="text-gray-400 x-small">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                <td class="text-gray-400 x-small {{ $invoice->isOverdue() ? 'text-red-400 fw-bold' : '' }}">
                                    {{ $invoice->due_date->format('Y-m-d') }}
                                </td>
                                <td class="fw-bold text-white">{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-gray-400">{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="fw-bold text-red-300">{{ number_format($invoice->balance_due, 2) }}</td>
                                <td>
                                    @php
                                        $color = match ($invoice->status->value) {
                                            'paid' => 'green',
                                            'partial' => 'orange',
                                            'pending' => 'red',
                                            'cancelled' => 'gray',
                                            default => 'blue'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}-500 bg-opacity-10 text-{{ $color }}-400">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('purchase-invoices.show', $invoice->id) }}" class="btn-icon-glass"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('supplier-payments.create', ['supplier_id' => $invoice->supplier_id, 'invoice_id' => $invoice->id, 'amount' => $invoice->balance_due]) }}"
                                            class="btn-icon-glass text-purple-400 hover-purple" title="سداد">
                                            <i class="bi bi-cash-stack"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-files fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">لا توجد فواتير</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-top border-white-5">
                    {{ $invoices->links() }}
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

        .bg-gradient-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .shadow-neon-cyan {
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .text-cyan-300 {
            color: #67e8f9 !important;
        }

        .bg-cyan-500 {
            background: #06b6d4 !important;
        }

        .border-top-gradient-cyan {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #06b6d4, #22d3ee) 1;
        }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(6, 182, 212, 0.4);
        }

        .btn-cyan-glass {
            background: rgba(6, 182, 212, 0.15);
            color: #22d3ee;
            border: 1px solid rgba(34, 211, 238, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-cyan-glass:hover {
            background: rgba(6, 182, 212, 0.25);
            color: white;
            border-color: #22d3ee;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .focus-ring-cyan:focus {
            border-color: #22d3ee !important;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.1) !important;
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