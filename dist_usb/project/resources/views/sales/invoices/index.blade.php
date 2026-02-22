@extends('layouts.app')

@section('title', __('Sales Invoices'))

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-cyan shadow-neon-cyan">
                    <i class="bi bi-receipt fs-3 text-body"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-1 tracking-wide">{{ __('Sales Invoices') }}</h2>
                    <p class="mb-0 text-gray-400 small">{{ __('Invoice management and sales record') }}</p>
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('pos.index') }}" class="btn btn-action-success d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-basket"></i>
                    <span class="fw-bold">{{ __('POS') }}</span>
                </a>
                <a href="{{ route('sales-invoices.create') }}" class="btn btn-action-cyan d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-plus-lg"></i>
                    <span class="fw-bold">{{ __('New Invoice') }}</span>
                </a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-slate-900 bg-opacity-50 border border-secondary border-opacity-10-5 rounded-4 p-4 mb-5">
            <form action="{{ route('sales-invoices.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('Search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-dark border-start-0 ps-0 text-body" 
                               value="{{ request('search') }}" placeholder="{{ __('Invoice number, customer name...') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('From Date') }}</label>
                    <input type="date" name="from_date" class="form-control form-control-dark text-body" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('To Date') }}</label>
                    <input type="date" name="to_date" class="form-control form-control-dark text-body" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('Status') }}</label>
                    <select name="status" class="form-select form-select-dark text-body">
                        <option value="">-- الكل --</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Settled') }}</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-cyan-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i>{{ __('Filter') }}</button>
                </div>
            </form>
        </div>

        <!-- Invoices Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-cyan">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">{{ __('Invoice Number') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Paid Amount') }}</th>
                            <th>{{ __('Balance Due') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="pe-4 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 font-monospace fw-bold text-cyan-300">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs bg-slate-700 rounded-circle text-body d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                            {{ strtoupper(substr($invoice->customer?->name ?? 'G', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="d-block text-body small fw-bold">{{ Str::limit($invoice->customer?->name ?? 'عابر (Walk-in)', 20) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-400 small">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d H:i') }}</td>
                                <td class="fw-bold text-body">{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-danger fw-bold">{{ number_format($invoice->balance_due, 2) }}</td>
                                <td>
                                    @switch($invoice->status->value ?? $invoice->status)
                                        @case('paid')
                                            <span class="badge bg-green-500 bg-opacity-10 text-green-400">{{ __('Settled') }}</span>
                                            @break
                                        @case('partially_paid')
                                        @case('partial')
                                            <span class="badge bg-orange-500 bg-opacity-10 text-orange-400">{{ __('Partial') }}</span>
                                            @break
                                        @case('unpaid')
                                            <span class="badge bg-red-500 bg-opacity-10 text-red-400">{{ __('Unpaid') }}</span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-gray-500 bg-opacity-10 text-gray-400">{{ __('Refunded') }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-slate-700 text-gray-400">{{ $invoice->status }}</span>
                                    @endswitch
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-50 group-hover-visible transition-all">
                                        <a href="{{ route('sales-invoices.show', $invoice->id) }}" class="btn-icon-glass" title="{{ __('View Details') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pos.receipt', $invoice->id) }}" target="_blank" class="btn-icon-glass" title="{{ __('Print') }}">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        @if($invoice->balance_due > 0)
                                        <a href="{{ route('customer-payments.create', ['invoice_id' => $invoice->id]) }}" class="btn-icon-glass text-warning hover-warning" title="{{ __('Pay Installment') }}">
                                            <i class="bi bi-wallet2"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-receipt fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">{{ __('No invoices') }}</h5>
                                        <p class="text-gray-600 small">{{ __('No sales recorded yet') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($invoices->hasPages())
            <div class="p-4 border-top border-secondary border-opacity-10-5">
                {{ $invoices->links('partials.pagination') }}
            </div>
            @endif
        </div>
    </div>

    <style>
        /* Cyan Theme */
        .text-cyan-300 { color: #67e8f9 !important; }
        .text-cyan-400 { color: #22d3ee !important; }
        .bg-gradient-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .shadow-neon-cyan { box-shadow: 0 0 20px rgba(6, 182, 212, 0.4); }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(6, 182, 212, 0.4);
        }

        .btn-action-success {
             background: linear-gradient(135deg, #10b981 0%, #059669 100%);
             border: none; color: var(--text-primary); padding: 10px 24px; border-radius: 10px; transition: all 0.3s;
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
            color: var(--text-primary);
            border-color: #22d3ee;
        }

        .border-top-gradient-cyan {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #06b6d4, #22d3ee) 1;
        }
    </style>
@endsection
