@extends('layouts.app')

@section('title', 'سند استلام: ' . $grn->grn_number)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('grns.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-white mb-0">سند استلام بضاعة</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-gray-400 font-monospace">{{ $grn->grn_number }}</span>
                        <span
                            class="badge bg-green-500 bg-opacity-10 text-green-400 border border-green-500 border-opacity-20 rounded-pill px-2">
                            {{ $grn->status->label() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if(!$grn->invoice)
                    <a href="{{ route('purchase-invoices.create', ['grn_id' => $grn->id]) }}" class="btn btn-action-cyan">
                        <i class="bi bi-receipt me-2"></i> تحويل لفاتورة شراء
                    </a>
                @else
                    <a href="{{ route('purchase-invoices.show', $grn->invoice->id) }}" class="btn btn-outline-cyan">
                        <i class="bi bi-eye me-2"></i> عرض الفاتورة
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Details -->
            <div class="col-md-9">
                <div class="glass-panel p-0 overflow-hidden mb-4">
                    <div class="p-4 border-bottom border-white-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المورد</label>
                                <h6 class="text-white fw-bold mb-0">{{ $grn->supplier->name }}</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">تاريخ الاستلام</label>
                                <p class="text-white fw-bold mb-0">{{ $grn->received_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المخزن</label>
                                <p class="text-white mb-0">{{ $grn->warehouse->name }}</p>
                            </div>
                            <div class="col-md-2">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">أمر الشراء</label>
                                <a href="{{ route('purchase-orders.show', $grn->purchase_order_id) }}"
                                    class="text-blue-400 text-decoration-none font-monospace d-block">
                                    {{ $grn->purchaseOrder->po_number }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">المنتج</th>
                                    <th class="text-center">الكمية المستلمة</th>
                                    <th class="text-center">الوحدة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grn->lines as $line)
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="text-white mb-0">{{ $line->product->name }}</h6>
                                            <span class="text-gray-500 x-small code-font">{{ $line->product->sku }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-green-300 fs-5">{{ $line->quantity }}</td>
                                        <td class="text-center text-gray-400 x-small">{{ $line->product->unit->name ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-3">
                @if($grn->notes)
                    <div class="glass-panel p-4 mb-4">
                        <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-2">ملاحظات</h6>
                        <p class="text-gray-300 small mb-0">{{ $grn->notes }}</p>
                    </div>
                @endif

                @if($grn->supplier_delivery_note)
                    <div class="glass-panel p-4">
                        <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-2">إذن تسليم المورد</h6>
                        <p class="text-white font-monospace mb-0">{{ $grn->supplier_delivery_note }}</p>
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
            color: #94a3b8;
            font-weight: 600;
            padding: 1rem;
        }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
        }
    </style>
@endsection