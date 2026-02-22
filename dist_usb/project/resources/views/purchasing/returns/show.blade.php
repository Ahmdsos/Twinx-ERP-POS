@extends('layouts.app')

@section('title', 'مرتجع مشتريات: ' . $purchaseReturn->return_number)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase-returns.index') }}"
                    class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i
                        class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">إشعار مرتجع (Debit Note)</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-gray-400 font-monospace">{{ $purchaseReturn->return_number }}</span>
                        <span
                            class="badge bg-green-500 bg-opacity-10 text-green-400 border border-green-500 border-opacity-20 rounded-pill px-2">
                            معتمد
                        </span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light d-flex align-items-center gap-2" onclick="window.print()">
                    <i class="bi bi-printer"></i>{{ __('Print') }}</button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Details -->
            <div class="col-md-9">
                <div class="glass-panel p-0 overflow-hidden mb-4">
                    <div class="p-4 border-bottom border-secondary border-opacity-10-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المورد</label>
                                <h6 class="text-heading fw-bold mb-0">{{ $purchaseReturn->supplier->name }}</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">{{ __('Return Date') }}</label>
                                <p class="text-body fw-bold mb-0">{{ $purchaseReturn->return_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">الفاتورة الأصلية</label>
                                @if($purchaseReturn->invoice)
                                    <a href="{{ route('purchase-invoices.show', $purchaseReturn->purchase_invoice_id) }}"
                                        class="text-cyan-400 text-decoration-none font-monospace d-block">
                                        {{ $purchaseReturn->invoice->invoice_number }}
                                    </a>
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
                                    <th class="ps-4">{{ __('Product') }}</th>
                                    <th class="text-center">الكمية المرتجعة</th>
                                    <th class="text-end">{{ __('Unit Price') }}</th>
                                    <th class="text-end pe-4">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseReturn->lines as $line)
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="text-heading mb-0">{{ $line->product->name }}</h6>
                                        </td>
                                        <td class="text-center fw-bold text-orange-300 fs-5">{{ $line->quantity }}</td>
                                        <td class="text-end text-gray-300">{{ number_format($line->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold text-body pe-4">{{ number_format($line->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-black bg-opacity-20 border-top border-secondary border-opacity-10-10">
                                <tr>
                                    <td colspan="3" class="text-end text-gray-400 py-3">إجمالي المرتجع</td>
                                    <td class="text-end fw-bold text-orange-400 fs-5 py-3 pe-4">
                                        {{ number_format($purchaseReturn->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-3">
                @if($purchaseReturn->notes)
                    <div class="glass-panel p-4 mb-4">
                        <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-2">ملاحظات / سبب الإرجاع</h6>
                        <p class="text-gray-300 small mb-0">{{ $purchaseReturn->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            backdrop-filter: blur(12px);
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 1rem;
        }
    </style>
@endsection