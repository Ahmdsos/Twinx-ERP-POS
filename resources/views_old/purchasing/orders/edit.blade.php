@extends('layouts.app')

@section('title', 'تعديل أمر الشراء - Twinx ERP')
@section('page-title', 'تعديل أمر الشراء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">أوامر الشراء</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.show', $purchaseOrder) }}">{{ $purchaseOrder->po_number }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" id="po-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-9">
            <!-- Order Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cart me-2"></i>{{ $purchaseOrder->po_number }}</h5>
                    <span class="badge bg-secondary">{{ $purchaseOrder->status->label() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">المورد <span class="text-danger">*</span></label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                    name="supplier_id" required>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                            {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->code }} - {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">المستودع <span class="text-danger">*</span></label>
                            <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                    name="warehouse_id" required>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ old('warehouse_id', $purchaseOrder->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الطلب <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                   name="order_date" 
                                   value="{{ old('order_date', $purchaseOrder->order_date?->format('Y-m-d')) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاريخ التسليم المتوقع</label>
                            <input type="date" class="form-control" 
                                   name="expected_date" 
                                   value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">رقم المرجع</label>
                            <input type="text" class="form-control" 
                                   name="reference" value="{{ old('reference', $purchaseOrder->reference) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Lines -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>بنود أمر الشراء</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="add-line">
                        <i class="bi bi-plus me-1"></i>إضافة صنف
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="lines-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">المنتج</th>
                                    <th>الكمية</th>
                                    <th>الوحدة</th>
                                    <th>سعر الوحدة</th>
                                    <th>الخصم %</th>
                                    <th>الإجمالي</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                @foreach($purchaseOrder->lines as $index => $line)
                                    <tr class="line-row" data-index="{{ $index }}">
                                        <td>
                                            <select class="form-select form-select-sm product-select" 
                                                    name="lines[{{ $index }}][product_id]" required>
                                                <option value="">اختر المنتج...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                            data-price="{{ $product->cost_price }}"
                                                            data-unit="{{ $product->unit?->name }}"
                                                            {{ $line->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->sku }} - {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm quantity-input" 
                                                   name="lines[{{ $index }}][quantity]" step="0.01" min="0.01" 
                                                   value="{{ $line->quantity }}" required>
                                        </td>
                                        <td class="unit-cell">{{ $line->product?->unit?->name ?? '-' }}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm price-input" 
                                                   name="lines[{{ $index }}][unit_price]" step="0.01" min="0" 
                                                   value="{{ $line->unit_price }}" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm discount-input" 
                                                   name="lines[{{ $index }}][discount_percent]" step="0.01" min="0" max="100" 
                                                   value="{{ $line->discount_percent ?? 0 }}">
                                        </td>
                                        <td class="line-total fw-bold">{{ number_format($line->line_total, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-line">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-start"><strong>الإجمالي الفرعي</strong></td>
                                    <td colspan="2"><strong id="subtotal">{{ number_format($purchaseOrder->total, 2) }}</strong> ج.م</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات وشروط</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">شروط الدفع والتوريد</label>
                            <textarea class="form-control" name="terms" rows="3">{{ old('terms', $purchaseOrder->terms) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i>حفظ التعديلات
                        </button>
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
                            <i class="bi bi-x me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lineIndex = {{ $purchaseOrder->lines->count() }};
    const linesBody = document.getElementById('lines-body');
    
    // Template for new line
    function getLineTemplate(index) {
        return `
            <tr class="line-row" data-index="${index}">
                <td>
                    <select class="form-select form-select-sm product-select" 
                            name="lines[${index}][product_id]" required>
                        <option value="">اختر المنتج...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                    data-price="{{ $product->cost_price }}"
                                    data-unit="{{ $product->unit?->name }}">
                                {{ $product->sku }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm quantity-input" 
                           name="lines[${index}][quantity]" step="0.01" min="0.01" value="1" required>
                </td>
                <td class="unit-cell">-</td>
                <td>
                    <input type="number" class="form-control form-control-sm price-input" 
                           name="lines[${index}][unit_price]" step="0.01" min="0" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm discount-input" 
                           name="lines[${index}][discount_percent]" step="0.01" min="0" max="100" value="0">
                </td>
                <td class="line-total fw-bold">0.00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-line">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }
    
    // Add new line
    document.getElementById('add-line').addEventListener('click', function() {
        linesBody.insertAdjacentHTML('beforeend', getLineTemplate(lineIndex));
        lineIndex++;
    });
    
    // Remove line
    linesBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-line')) {
            const rows = linesBody.querySelectorAll('.line-row');
            if (rows.length > 1) {
                e.target.closest('.line-row').remove();
                calculateTotals();
            }
        }
    });
    
    // Product select change
    linesBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('.line-row');
            const selected = e.target.selectedOptions[0];
            if (selected) {
                row.querySelector('.price-input').value = selected.dataset.price || 0;
                row.querySelector('.unit-cell').textContent = selected.dataset.unit || '-';
                calculateLineTotal(row);
            }
        }
    });
    
    // Calculate on input change
    linesBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || 
            e.target.classList.contains('price-input') ||
            e.target.classList.contains('discount-input')) {
            calculateLineTotal(e.target.closest('.line-row'));
        }
    });
    
    // Calculate line total
    function calculateLineTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        
        const subtotal = qty * price;
        const discountAmount = subtotal * (discount / 100);
        const total = subtotal - discountAmount;
        
        row.querySelector('.line-total').textContent = total.toFixed(2);
        calculateTotals();
    }
    
    // Calculate all totals
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.line-total').forEach(cell => {
            subtotal += parseFloat(cell.textContent) || 0;
        });
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    }
    
    // Initial calculation
    calculateTotals();
});
</script>
@endpush
