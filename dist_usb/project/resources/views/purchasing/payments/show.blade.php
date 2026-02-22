@extends('layouts.app')

@section('title', 'سند صرف: ' . $supplierPayment->payment_number)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('supplier-payments.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">سند صرف نقدية / بنك</h2>
                    <p class="text-gray-400 mb-0 x-small font-monospace">{{ $supplierPayment->payment_number }}</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('supplier-payments.print', $supplierPayment->id) }}" target="_blank" class="btn btn-outline-cyan d-flex align-items-center gap-2">
                    <i class="bi bi-printer"></i>{{ __('Print') }}</a>
                <a href="{{ route('supplier-payments.create') }}" class="btn btn-action-purple">
                    <i class="bi bi-plus-lg"></i> دفعة جديدة
                </a>
            </div>
        </div>

        <!-- Receipt Card -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="glass-panel p-0 overflow-hidden border-top-4 border-purple-500 position-relative">
                    <!-- Receipt Header -->
                    <div class="bg-surface-5 p-4 border-bottom border-secondary border-opacity-10-5 text-center">
                        <div class="mb-3">
                            <i class="bi bi-check-circle-fill text-green-400 fs-1"></i>
                        </div>
                        <h3 class="text-heading fw-bold mb-1">تم الدفع بنجاح</h3>
                        <p class="text-gray-400 mb-0">{{ $supplierPayment->payment_date->format('d F Y, h:i A') }}</p>
                    </div>

                    <!-- Amount -->
                    <div class="p-5 text-center border-bottom border-secondary border-opacity-10-5">
                        <span class="text-purple-300 small text-uppercase tracking-wide fw-bold">المبلغ المدفوع</span>
                        <h1 class="display-4 fw-bold text-heading mb-0 mt-2">{{ number_format($supplierPayment->amount, 2) }} <span class="fs-4 text-gray-500">EGP</span></h1>
                        <div class="mt-3">
                            @if($supplierPayment->payment_method == 'cash')
                                <span class="badge bg-green-500 bg-opacity-10 text-green-400 border border-green-500 border-opacity-20 px-3 py-2 rounded-pill">
                                    <i class="bi bi-cash me-2"></i>{{ __('Cash') }}</span>
                            @elseif($supplierPayment->payment_method == 'bank_transfer')
                                <span class="badge bg-blue-500 bg-opacity-10 text-blue-400 border border-blue-500 border-opacity-20 px-3 py-2 rounded-pill">
                                    <i class="bi bi-bank me-2"></i>{{ __('Bank Transfer') }}</span>
                            @else
                                <span class="badge bg-orange-500 bg-opacity-10 text-orange-400 border border-orange-500 border-opacity-20 px-3 py-2 rounded-pill">
                                    <i class="bi bi-card-checklist me-2"></i>{{ __('Check') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="p-4 bg-slate-900 bg-opacity-30">
                        <div class="row g-4">
                            <div class="col-6">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المورد المستفيد</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs bg-purple-500 rounded-circle text-body d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 10px;">
                                        {{ strtoupper(substr($supplierPayment->supplier->name, 0, 1)) }}
                                    </div>
                                    <a href="{{ route('suppliers.show', $supplierPayment->supplier_id) }}" class="text-body fw-bold text-decoration-none">
                                        {{ $supplierPayment->supplier->name }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">الخزينة / البنك</label>
                                <p class="text-body fw-bold mb-0">{{ $supplierPayment->paymentAccount->name ?? '-' }}</p>
                            </div>
                            <div class="col-6">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">رقم المرجع</label>
                                <p class="text-body font-monospace mb-0">{{ $supplierPayment->reference ?? '-' }}</p>
                            </div>
                            <div class="col-6">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">{{ __('User') }}</label>
                                <p class="text-body mb-0">{{ $supplierPayment->creator->name ?? 'System' }}</p>
                            </div>
                            @if($supplierPayment->notes)
                            <div class="col-12 border-top border-secondary border-opacity-10-5 pt-3 mt-2">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">{{ __('Notes') }}</label>
                                <p class="text-gray-300 small mb-0">{{ $supplierPayment->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Allocations -->
                    @if($supplierPayment->allocations->count() > 0)
                    <div class="p-4 border-top border-secondary border-opacity-10-5">
                        <h6 class="text-heading fw-bold mb-3 small opacity-75">سداد مقابل الفواتير:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-dark-custom mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-gray-500 x-small">{{ __('Invoice Number') }}</th>
                                        <th class="text-gray-500 x-small text-end">المبلغ المخصوم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplierPayment->allocations as $allocation)
                                        <tr>
                                            <td class="font-monospace text-gray-300">{{ $allocation->invoice->invoice_number ?? '---' }}</td>
                                            <td class="text-end fw-bold text-body">{{ number_format($allocation->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-action-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border: none; color: var(--text-primary); padding: 8px 20px; border-radius: 8px; transition: all 0.3s;
        }
        .btn-action-purple:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4); }

        .btn-outline-cyan { color: #22d3ee; border-color: rgba(34, 211, 238, 0.3); }
        .btn-outline-cyan:hover { background: rgba(34, 211, 238, 0.1); color: #22d3ee; border-color: #22d3ee; }

        .border-top-4 { border-top-width: 4px !important; }
        
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-border-color: rgba(255, 255, 255, 0.05); }
        .table-dark-custom td { padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .table-dark-custom th { padding-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
    </style>
@endsection
