@extends('layouts.app')

@section('title', 'فاتورة شراء: ' . $purchaseInvoice->invoice_number)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">فاتورة شراء</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-gray-400 font-monospace">{{ $purchaseInvoice->invoice_number }}</span>
                        @php
                            $color = match($purchaseInvoice->status->value) {
                                'paid' => 'green',
                                'partial' => 'orange',
                                'pending' => 'red',
                                'cancelled' => 'gray',
                                default => 'blue'
                            };
                        @endphp
                        <span class="badge bg-{{ $color }}-500 bg-opacity-10 text-{{ $color }}-400 border border-{{ $color }}-500 border-opacity-20 rounded-pill px-2">
                            {{ $purchaseInvoice->status->label() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('purchase-invoices.print', $purchaseInvoice->id) }}" target="_blank" class="btn btn-outline-light d-flex align-items-center gap-2">
                    <i class="bi bi-printer"></i>{{ __('Print') }}</a>
                @if($purchaseInvoice->balance_due > 0)
                <a href="{{ route('supplier-payments.create', ['supplier_id' => $purchaseInvoice->supplier_id, 'invoice_id' => $purchaseInvoice->id, 'amount' => $purchaseInvoice->balance_due]) }}" class="btn btn-action-purple">
                    <i class="bi bi-cash-stack me-2"></i> سداد المستحق
                </a>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Invoice Details -->
            <div class="col-md-9">
                <div class="glass-panel p-0 overflow-hidden mb-4">
                    <div class="p-4 border-bottom border-secondary border-opacity-10-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المورد</label>
                                <a href="{{ route('suppliers.show', $purchaseInvoice->supplier_id) }}" class="d-flex align-items-center gap-2 text-decoration-none group">
                                    <div class="avatar-xs bg-cyan-500 rounded-circle text-body d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($purchaseInvoice->supplier->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 class="text-heading fw-bold mb-0 group-hover-text-cyan transition-all">{{ $purchaseInvoice->supplier->name }}</h6>
                                        <span class="text-gray-500 x-small">{{ $purchaseInvoice->supplier->phone }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">{{ __('Invoice Date') }}</label>
                                <p class="text-body fw-bold mb-0">{{ $purchaseInvoice->invoice_date->format('Y-m-d') }}</p>
                                <span class="text-gray-500 x-small">استحقاق: {{ $purchaseInvoice->due_date->format('Y-m-d') }}</span>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">مرجع المورد</label>
                                <p class="text-body font-monospace mb-0">{{ $purchaseInvoice->supplier_invoice_number }}</p>
                            </div>
                            <div class="col-md-2">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">سند الاستلام</label>
                                @if($purchaseInvoice->grn)
                                    <a href="#" class="btn btn-sm btn-outline-light w-100 font-monospace">{{ $purchaseInvoice->grn->grn_number }}</a>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">المتتج</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="text-center">{{ __('Unit') }}</th>
                                    <th class="text-end">{{ __('Unit Price') }}</th>
                                    <th class="text-end pe-4">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseInvoice->lines as $line)
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="text-heading mb-0">{{ $line->description }}</h6>
                                            <span class="text-gray-500 x-small code-font">{{ $line->product->sku ?? '' }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-cyan-300">{{ $line->quantity }}</td>
                                        <td class="text-center text-gray-400 x-small">{{ $line->product->unit->name ?? '-' }}</td>
                                        <td class="text-end text-gray-300">{{ number_format($line->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold text-body pe-4">{{ number_format($line->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-surface bg-opacity-5 border-top border-secondary border-opacity-10-10">
                                <tr>
                                    <td colspan="4" class="text-end text-gray-400 py-3">{{ __('Total') }}</td>
                                    <td class="text-end fw-bold text-body fs-5 py-3 pe-4">{{ number_format($purchaseInvoice->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end text-gray-400 border-0 pb-3"> + الضريبة</td>
                                    <td class="text-end text-gray-300 border-0 pb-3 pe-4">{{ number_format($purchaseInvoice->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end text-cyan-400 fw-bold border-0 h5 mb-0">صافي الفاتورة</td>
                                    <td class="text-end text-cyan-400 fw-bold border-0 h5 mb-0 pe-4">{{ number_format($purchaseInvoice->total, 2) }} <small class="fs-6">EGP</small></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Payments History -->
                @if($purchaseInvoice->paymentAllocations->count() > 0)
                <h6 class="text-heading fw-bold mb-3 mt-4"><i class="bi bi-clock-history me-2"></i>سجل السداد</h6>
                <div class="glass-panel p-0 overflow-hidden">
                    <table class="table table-dark-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">رقم السند</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th class="text-end pe-4">المبلغ المخصوم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseInvoice->paymentAllocations as $allocation)
                                <tr>
                                    <td class="ps-4 font-monospace">
                                        <a href="{{ route('supplier-payments.show', $allocation->supplier_payment_id) }}" class="text-purple-300 text-decoration-none">
                                            {{ $allocation->payment->payment_number }}
                                        </a>
                                    </td>
                                    <td class="text-gray-400">{{ $allocation->payment->payment_date->format('Y-m-d') }}</td>
                                    <td>{{ $allocation->payment->payment_method }}</td>
                                    <td class="text-end text-body fw-bold pe-4">{{ number_format($allocation->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-3">
                <div class="glass-panel p-4 mb-4">
                    <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-3">ملخص الموقف المالي</h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-gray-400">قيمة الفاتورة</span>
                        <span class="text-body fw-bold">{{ number_format($purchaseInvoice->total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-green-400">تم سداد</span>
                        <span class="text-green-400 fw-bold">-{{ number_format($purchaseInvoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="border-top border-secondary border-opacity-10-10 pt-2 mt-2">
                        <div class="d-flex justify-content-between aligns-items-center">
                            <span class="text-red-400 fw-bold">{{ __('Balance Due') }}</span>
                            <span class="text-red-400 fw-bold fs-5">{{ number_format($purchaseInvoice->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($purchaseInvoice->notes)
                <div class="glass-panel p-4">
                    <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-2">{{ __('Notes') }}</h6>
                    <p class="text-gray-300 small mb-0">{{ $purchaseInvoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .glass-panel { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; backdrop-filter: blur(12px); }
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-border-color: rgba(255, 255, 255, 0.05); }
        .table-dark-custom th { background: var(--btn-glass-bg); color: var(--text-secondary); font-weight: 600; padding: 1rem; }
        .btn-action-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border: none; color: var(--text-primary); padding: 8px 16px; border-radius: 8px; transition: all 0.3s;
        }
        .btn-action-purple:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4); }
    </style>
@endsection
