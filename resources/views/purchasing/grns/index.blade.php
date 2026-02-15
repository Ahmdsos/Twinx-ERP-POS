@extends('layouts.app')

@section('title', 'سندات استلام البضاعة (GRN)')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-green shadow-neon-green">
                    <i class="bi bi-box-seam fs-3 text-body"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-1 tracking-wide">استلام بضاعة (GRN)</h2>
                    <p class="mb-0 text-gray-400 small">إثبات استلام البضائع من الموردين ودخولها المخازن</p>
                </div>
            </div>
            <a href="{{ route('grns.create') }}" class="btn btn-action-green d-flex align-items-center gap-2 shadow-lg">
                <i class="bi bi-plus-lg"></i>
                <span class="fw-bold">استلام جديد</span>
            </a>
        </div>

        <!-- Filters (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-secondary border-opacity-10-5 rounded-4 p-4 mb-5">
            <form action="{{ route('grns.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-green-400 x-small fw-bold text-uppercase ps-1">{{ __('Search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-green"
                            value="{{ request('search') }}" placeholder="رقم السند / أمر الشراء...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-green-400 x-small fw-bold text-uppercase ps-1">{{ __('Status') }}</label>
                    <select name="status" class="form-select form-select-dark text-body cursor-pointer hover:bg-surface-5">
                        <option value="">-- الكل --</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-green-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i>{{ __('Filter') }}</button>
                </div>
            </form>
        </div>

        <!-- GRNs Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-green">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">رقم السند</th>
                            <th>المورد</th>
                            <th>أمر الشراء</th>
                            <th>تاريخ الاستلام</th>
                            <th>{{ __('Recipient') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="pe-4 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grns as $grn)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 font-monospace text-green-300">
                                    <a href="{{ route('grns.show', $grn->id) }}"
                                        class="text-decoration-none text-green-300 hover-text-body">
                                        {{ $grn->grn_number }}
                                    </a>
                                </td>
                                <td class="fw-bold text-body">{{ $grn->supplier->name }}</td>
                                <td class="font-monospace text-gray-400">
                                    <a href="{{ route('purchase-orders.show', $grn->purchase_order_id) }}"
                                        class="text-gray-400 text-decoration-none hover-text-body">
                                        {{ $grn->purchaseOrder->po_number }}
                                    </a>
                                </td>
                                <td class="text-gray-400 x-small">{{ $grn->received_date->format('Y-m-d') }}</td>
                                <td class="text-gray-400">{{ $grn->receiver->name ?? '-' }}</td>
                                <td>
                                    @php
                                        $color = match ($grn->status->value) {
                                            'completed' => 'green',
                                            'draft' => 'gray',
                                            'cancelled' => 'red',
                                            default => 'blue'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}-500 bg-opacity-10 text-{{ $color }}-400">
                                        {{ $grn->status->label() }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('grns.show', $grn->id) }}" class="btn-icon-glass" title="{{ __('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($grn->status->value == 'completed' && !$grn->invoice)
                                            <a href="{{ route('purchase-invoices.create', ['grn_id' => $grn->id]) }}"
                                                class="btn-icon-glass text-cyan-400 hover-cyan" title="{{ __('Create Invoice') }}">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-box-seam fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">لا توجد سندات استلام</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($grns->hasPages())
                <div class="p-4 border-top border-secondary border-opacity-10-5">
                    {{ $grns->links() }}
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

        .bg-gradient-green {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .shadow-neon-green {
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
        }

        .text-green-400 {
            color: #4ade80 !important;
        }

        .text-green-300 {
            color: #86efac !important;
        }

        .bg-green-500 {
            background: #22c55e !important;
        }

        .border-top-gradient-green {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #22c55e, #4ade80) 1;
        }

        .btn-action-green {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .btn-green-glass {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-green-glass:hover {
            background: rgba(34, 197, 94, 0.25);
            color: var(--text-primary);
            border-color: #4ade80;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
        }

        .focus-ring-green:focus {
            border-color: #4ade80 !important;
            box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.1) !important;
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid var(--btn-glass-border); !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-secondary);
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
            background: var(--btn-glass-bg);
            color: #cbd5e1;
            transition: 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
    </style>
@endsection