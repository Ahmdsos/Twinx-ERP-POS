@extends('layouts.app')

@section('title', 'استلام بضاعة (GRN)')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('grns.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-white mb-0">استلام بضاعة (GRN)</h2>
                    <p class="text-gray-400 mb-0 x-small">استلام منتجات من أمر شراء معتمد</p>
                </div>
            </div>
            <button type="submit" form="grnForm"
                class="btn btn-action-green fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-box-seam"></i> تأكيد الاستلام
            </button>
        </div>

        <form action="{{ route('grns.store') }}" method="POST" id="grnForm">
            @csrf

            <div class="row g-4">
                <!-- Selector -->
                <div class="col-md-4">
                    <div class="glass-panel p-4 mb-4">
                        <label class="form-label text-gray-400 small fw-bold">اختر أمر الشراء (المعتمد)</label>
                        <select name="purchase_order_id" class="form-select form-select-dark focus-ring-green"
                            onchange="window.location.href='{{ route('grns.create') }}?purchase_order_id=' + this.value">
                            <option value="">-- اختر أمر شراء --</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" {{ request('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                    {{ $po->po_number }} - {{ $po->supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($purchaseOrder)
                        <div class="glass-panel p-4">
                            <h6 class="text-white fw-bold mb-3 border-bottom border-white-5 pb-2">بيانات الاستلام</h6>

                            <div class="mb-3">
                                <label class="form-label text-gray-400 x-small fw-bold">المخزن المستلم <span
                                        class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select form-select-dark focus-ring-green" required>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $purchaseOrder->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-gray-400 x-small fw-bold">تاريخ الاستلام <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="received_date" class="form-control form-control-dark focus-ring-green"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-gray-400 x-small fw-bold">رقم إذن تسليم المورد (Delivery
                                    Note)</label>
                                <input type="text" name="supplier_delivery_note"
                                    class="form-control form-control-dark focus-ring-green">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-gray-400 x-small fw-bold">ملاحظات</label>
                                <textarea name="notes" class="form-control form-control-dark focus-ring-green"
                                    rows="3"></textarea>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Items Table -->
                <div class="col-md-8">
                    @if($purchaseOrder)
                        <div class="glass-panel p-4 h-100">
                            <h5 class="text-white fw-bold mb-4"><i class="bi bi-list-check me-2"></i>قائمة المراجعة والاستلام
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-dark-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>المنتج</th>
                                            <th class="text-center">الوحدة</th>
                                            <th class="text-center">المطلوب</th>
                                            <th class="text-center" style="width: 150px;">المستلم (الآن)</th>
                                            <th class="text-center">حالة الصنف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrder->lines as $index => $line)
                                            <tr>
                                                <td>
                                                    <h6 class="text-white mb-0">{{ $line->product->name }}</h6>
                                                    <input type="hidden" name="lines[{{ $index }}][purchase_order_line_id]"
                                                        value="{{ $line->id }}">
                                                </td>
                                                <td class="text-center text-gray-400 x-small">
                                                    {{ $line->product->unit->name ?? '-' }}</td>
                                                <td class="text-center fw-bold text-gray-300">{{ $line->quantity }}</td>
                                                <td>
                                                    <input type="number" step="any" name="lines[{{ $index }}][quantity]"
                                                        class="form-control form-control-dark text-center fw-bold text-green-300"
                                                        max="{{ $line->quantity }}" value="{{ $line->quantity }}" required>
                                                </td>
                                                <td class="text-center">
                                                    <i class="bi bi-check-circle-fill text-green-500 opacity-50"></i>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div
                            class="h-100 d-flex flex-column align-items-center justify-content-center opacity-50 border border-dashed border-gray-700 rounded-4">
                            <i class="bi bi-cart-check fs-1 text-gray-500 mb-3"></i>
                            <h5 class="text-gray-400">الرجاء اختيار أمر شراء للبدء في الاستلام</h5>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <style>
        .btn-action-green {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .focus-ring-green:focus {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }
    </style>
@endsection