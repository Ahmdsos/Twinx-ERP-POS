@extends('layouts.app')

@section('title', 'استلام مخزون - Twinx ERP')
@section('page-title', 'استلام مخزون جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.index') }}">حركات المخزون</a></li>
    <li class="breadcrumb-item active">استلام مخزون</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>استلام مخزون جديد</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('stock.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Product Selection -->
                        <div class="col-md-6">
                            <label class="form-label">المنتج <span class="text-danger">*</span></label>
                            <select class="form-select @error('product_id') is-invalid @enderror" 
                                    name="product_id" id="product_id" required>
                                <option value="">اختر المنتج...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-cost="{{ $product->cost_price }}"
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                            <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                    name="warehouse_id" id="warehouse_id" required>
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

                        <!-- Current Stock Info (AJAX) -->
                        <div class="col-12" id="stock-info" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                المخزون الحالي: <strong id="current-qty">0</strong> | 
                                متوسط التكلفة: <strong id="avg-cost">0.00</strong> ج.م
                            </div>
                        </div>

                        <!-- Movement Type -->
                        <div class="col-md-6">
                            <label class="form-label">نوع الاستلام <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                <option value="initial" {{ old('type') == 'initial' ? 'selected' : '' }}>رصيد افتتاحي</option>
                                <option value="purchase" {{ old('type', 'purchase') == 'purchase' ? 'selected' : '' }}>شراء</option>
                                <option value="adjustment_in" {{ old('type') == 'adjustment_in' ? 'selected' : '' }}>تسوية (زيادة)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Reference -->
                        <div class="col-md-6">
                            <label class="form-label">المرجع</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                   name="reference" value="{{ old('reference') }}" 
                                   placeholder="رقم أمر الشراء / الفاتورة">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-4">
                            <label class="form-label">الكمية <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                   name="quantity" id="quantity" value="{{ old('quantity') }}" 
                                   step="0.01" min="0.01" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit Cost -->
                        <div class="col-md-4">
                            <label class="form-label">سعر الوحدة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('unit_cost') is-invalid @enderror" 
                                       name="unit_cost" id="unit_cost" value="{{ old('unit_cost') }}" 
                                       step="0.01" min="0" required>
                                <span class="input-group-text">ج.م</span>
                            </div>
                            @error('unit_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Total (Auto-calculated) -->
                        <div class="col-md-4">
                            <label class="form-label">الإجمالي</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="total" readonly value="0.00">
                                <span class="input-group-text">ج.م</span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x me-1"></i>إلغاء
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i>تأكيد الاستلام
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
    // Auto-calculate total
    function calculateTotal() {
        const qty = parseFloat(document.getElementById('quantity').value) || 0;
        const cost = parseFloat(document.getElementById('unit_cost').value) || 0;
        document.getElementById('total').value = (qty * cost).toFixed(2);
    }

    document.getElementById('quantity').addEventListener('input', calculateTotal);
    document.getElementById('unit_cost').addEventListener('input', calculateTotal);

    // Auto-fill cost from product
    document.getElementById('product_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.dataset.cost) {
            document.getElementById('unit_cost').value = selected.dataset.cost;
            calculateTotal();
        }
        fetchStock();
    });

    document.getElementById('warehouse_id').addEventListener('change', fetchStock);

    // Fetch current stock
    function fetchStock() {
        const productId = document.getElementById('product_id').value;
        const warehouseId = document.getElementById('warehouse_id').value;
        
        if (productId && warehouseId) {
            fetch(`/stock/get-stock?product_id=${productId}&warehouse_id=${warehouseId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('current-qty').textContent = data.quantity.toFixed(2);
                    document.getElementById('avg-cost').textContent = data.average_cost.toFixed(2);
                    document.getElementById('stock-info').style.display = 'block';
                });
        } else {
            document.getElementById('stock-info').style.display = 'none';
        }
    }
</script>
@endpush
