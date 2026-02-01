@extends('layouts.app')

@section('title', 'تسوية مخزون - Twinx ERP')
@section('page-title', 'تسوية المخزون')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.index') }}">حركات المخزون</a></li>
    <li class="breadcrumb-item active">تسوية مخزون</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>تسوية المخزون</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>تنبيه:</strong> تسوية المخزون ستؤثر على قيمة المخزون والحسابات المالية.
                    </div>

                    <form action="{{ route('stock.adjust.process') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Product Selection -->
                            <div class="col-md-6">
                                <label class="form-label">المنتج <span class="text-danger">*</span></label>
                                <select class="form-select @error('product_id') is-invalid @enderror" name="product_id"
                                    id="product_id" required>
                                    <option value="">اختر المنتج...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->sku }} - {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Warehouse Selection -->
                            <div class="col-md-6">
                                <label class="form-label">المستودع <span class="text-danger">*</span></label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror" name="warehouse_id"
                                    id="warehouse_id" required>
                                    <option value="">اختر المستودع...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Current Stock Info -->
                            <div class="col-12" id="stock-info" style="display: none;">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <h6 class="text-muted mb-1">الكمية الحالية</h6>
                                                <h4 class="text-primary" id="current-qty">0</h4>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-muted mb-1">المحجوز</h6>
                                                <h4 class="text-warning" id="reserved-qty">0</h4>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-muted mb-1">متوسط التكلفة</h6>
                                                <h4 class="text-success" id="avg-cost">0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- New Quantity -->
                            <div class="col-md-6">
                                <label class="form-label">الكمية الجديدة <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('new_quantity') is-invalid @enderror"
                                    name="new_quantity" id="new_quantity" value="{{ old('new_quantity') }}" step="0.01"
                                    min="0" required>
                                @error('new_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" id="qty-diff"></small>
                            </div>

                            <!-- New Unit Cost (Optional) -->
                            <div class="col-md-6">
                                <label class="form-label">سعر الوحدة الجديد (اختياري)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('new_unit_cost') is-invalid @enderror"
                                        name="new_unit_cost" value="{{ old('new_unit_cost') }}" step="0.01" min="0"
                                        placeholder="اتركه فارغاً للإبقاء على السعر الحالي">
                                    <span class="input-group-text">ج.م</span>
                                </div>
                                @error('new_unit_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Reason -->
                            <div class="col-12">
                                <label class="form-label">سبب التسوية <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" rows="2"
                                    required placeholder="اذكر سبب تسوية المخزون...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-lg me-1"></i>تأكيد التسوية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentQty = 0;

        document.getElementById('product_id').addEventListener('change', fetchStock);
        document.getElementById('warehouse_id').addEventListener('change', fetchStock);

        document.getElementById('new_quantity').addEventListener('input', function () {
            const newQty = parseFloat(this.value) || 0;
            const diff = newQty - currentQty;
            const diffText = document.getElementById('qty-diff');

            if (diff > 0) {
                diffText.innerHTML = `<span class="text-success">+${diff.toFixed(2)} (زيادة)</span>`;
            } else if (diff < 0) {
                diffText.innerHTML = `<span class="text-danger">${diff.toFixed(2)} (نقص)</span>`;
            } else {
                diffText.innerHTML = '<span class="text-muted">لا تغيير</span>';
            }
        });

        function fetchStock() {
            const productId = document.getElementById('product_id').value;
            const warehouseId = document.getElementById('warehouse_id').value;

            if (productId && warehouseId) {
                fetch(`/stock/get-stock?product_id=${productId}&warehouse_id=${warehouseId}`)
                    .then(res => res.json())
                    .then(data => {
                        currentQty = data.quantity;
                        document.getElementById('current-qty').textContent = data.quantity.toFixed(2);
                        document.getElementById('reserved-qty').textContent = data.reserved.toFixed(2);
                        document.getElementById('avg-cost').textContent = data.average_cost.toFixed(2);
                        document.getElementById('stock-info').style.display = 'block';

                        // Pre-fill current quantity
                        document.getElementById('new_quantity').value = data.quantity.toFixed(2);
                    });
            } else {
                document.getElementById('stock-info').style.display = 'none';
            }
        }
    </script>
@endpush