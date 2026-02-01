@extends('layouts.app')

@section('title', 'أمر شراء: ' . $purchaseOrder->po_number)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-white mb-0">تفاصيل أمر الشراء</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-gray-400 font-monospace">{{ $purchaseOrder->po_number }}</span>
                        <span
                            class="badge bg-blue-500 bg-opacity-10 text-blue-400 border border-blue-500 border-opacity-20 rounded-pill px-2">
                            {{ $purchaseOrder->status->label() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if($purchaseOrder->status->value === 'draft')
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-outline-light">
                        <i class="bi bi-pencil"></i> تعديل
                    </a>
                    <form action="{{ route('purchase-orders.approve', $purchaseOrder->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-action-blue">
                            <i class="bi bi-check-lg me-2"></i> اعتماد الأمر
                        </button>
                    </form>
                @elseif($purchaseOrder->status->value === 'approved')
                    <a href="{{ route('grns.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                        class="btn btn-action-green">
                        <i class="bi bi-box-seam me-2"></i> استلام بضاعة (GRN)
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Order Details -->
            <div class="col-md-9">
                <div class="glass-panel p-0 overflow-hidden mb-4">
                    <div class="p-4 border-bottom border-white-5">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المورد</label>
                                <h6 class="text-white fw-bold mb-0">{{ $purchaseOrder->supplier->name }}</h6>
                                <span class="text-gray-500 x-small">{{ $purchaseOrder->supplier->phone }}</span>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">تاريخ الطلب</label>
                                <p class="text-white fw-bold mb-0">{{ $purchaseOrder->order_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المخزن المستلم</label>
                                <p class="text-white mb-0">{{ $purchaseOrder->warehouse->name }}</p>
                            </div>
                            <div class="col-md-2">
                                <label class="text-gray-500 x-small fw-bold text-uppercase mb-1">المرجع</label>
                                <p class="text-white font-monospace mb-0">{{ $purchaseOrder->reference ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">المنتج</th>
                                    <th class="text-center">الكمية</th>
                                    <th class="text-end">سعر الوحدة</th>
                                    <th class="text-end pe-4">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->lines as $line)
                                    <tr>
                                        <td class="ps-4">
                                            <h6 class="text-white mb-0">{{ $line->product->name }}</h6>
                                            <span class="text-gray-500 x-small code-font">{{ $line->product->sku }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-blue-300">{{ $line->quantity }}</td>
                                        <td class="text-end text-gray-300">{{ number_format($line->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold text-white pe-4">{{ number_format($line->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-black bg-opacity-20 border-top border-white-10">
                                <tr>
                                    <td colspan="3" class="text-end text-gray-400 py-3">الإجمالي</td>
                                    <td class="text-end fw-bold text-white fs-5 py-3 pe-4">
                                        {{ number_format($purchaseOrder->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-3">
                @if($purchaseOrder->notes)
                    <div class="glass-panel p-4 mb-4">
                        <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-2">ملاحظات</h6>
                        <p class="text-gray-300 small mb-0">{{ $purchaseOrder->notes }}</p>
                    </div>
                @endif

                @if($purchaseOrder->approver)
                    <div class="glass-panel p-4">
                        <h6 class="text-gray-400 x-small fw-bold text-uppercase mb-2">معلومات الاعتماد</h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-xs bg-green-500 rounded-circle text-white d-flex align-items-center justify-content-center"
                                style="width: 24px; height: 24px;">
                                <i class="bi bi-check small"></i>
                            </div>
                            <span class="text-green-400 small">تم الاعتماد بواسطة {{ $purchaseOrder->approver->name }}</span>
                        </div>
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

        .btn-action-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .btn-action-green {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
        }
    </style>
@endsection