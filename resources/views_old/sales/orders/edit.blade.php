@extends('layouts.app')

@section('title', 'تعديل ' . $salesOrder->so_number . ' - Twinx ERP')
@section('page-title', 'تعديل أمر البيع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales-orders.index') }}">أوامر البيع</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales-orders.show', $salesOrder) }}">{{ $salesOrder->so_number }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<form action="{{ route('sales-orders.update', $salesOrder) }}" method="POST" id="sales-order-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Order Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        تعديل {{ $salesOrder->so_number }}
                        <span class="badge bg-secondary ms-2">مسودة</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">العميل <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    name="customer_id" id="customer_id" required>
                                <option value="">اختر العميل...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            data-balance="{{ $customer->balance ?? 0 }}"
                                            data-credit="{{ $customer->credit_limit }}"
                                            data-address="{{ $customer->billing_address }}"
                                            {{ old('customer_id', $salesOrder->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->code }} - {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">المستودع <span class="text-danger">*</span></label>
                            <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                    name="warehouse_id" id="warehouse_id" required>
                                <option value="">اختر المستودع...</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $salesOrder->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الأمر <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                   name="order_date" value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d')) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاريخ التسليم المتوقع</label>
                            <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                                   name="expected_date" value="{{ old('expected_date', $salesOrder->expected_date?->format('Y-m-d')) }}">
                            @error('expected_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">طريقة الشحن</label>
                            <input type="text" class="form-control" 
                                   name="shipping_method" value="{{ old('shipping_method', $salesOrder->shipping_method) }}" 
                                   placeholder="مثال: توصيل محلي">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Lines -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>أصناف الأمر</h5>
                    <button type="button" class="btn btn-success btn-sm" id="add-line">
                        <i class="bi bi-plus-lg me-1"></i>إضافة صنف
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="lines-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%;">المنتج</th>
                                    <th style="width: 12%;">الكمية</th>
                                    <th style="width: 15%;">سعر الوحدة</th>
                                    <th style="width: 10%;">خصم %</th>
                                    <th style="width: 18%;">الإجمالي</th>
                                    <th style="width: 10%;">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                <!-- Existing lines -->
                                @foreach($salesOrder->lines as $index => $line)
                                    <tr class="line-row">
                                        <td>
                                            <select class="form-select product-select" name="lines[{{ $index }}][product_id]" required>
                                                <option value="">اختر المنتج...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                            data-price="{{ $product->selling_price }}"
                                                            data-unit="{{ $product->unit?->abbreviation ?? '' }}"
                                                            {{ $line->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->sku }} - {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted product-info">{{ $line->product?->unit?->abbreviation ?? '' }}</small>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity-input" 
                                                   name="lines[{{ $index }}][quantity]" step="0.01" min="0.01" 
                                                   value="{{ $line->quantity }}" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control unit-price-input" 
                                                   name="lines[{{ $index }}][unit_price]" step="0.01" min="0" 
                                                   value="{{ $line->unit_price }}" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control discount-input" 
                                                   name="lines[{{ $index }}][discount_percent]" step="0.01" min="0" max="100" 
                                                   value="{{ $line->discount_percent }}">
                                        </td>
                                        <td>
                                            <strong class="line-total">{{ number_format($line->line_total, 2) }}</strong> ج.م
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-line">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-start"><strong>الإجمالي الفرعي</strong></td>
                                    <td colspan="2"><strong id="subtotal">{{ number_format($salesOrder->subtotal, 2) }}</strong> ج.م</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-start"><strong>الإجمالي النهائي</strong></td>
                                    <td colspan="2"><strong id="total" class="fs-5 text-primary">{{ number_format($salesOrder->total, 2) }}</strong> ج.م</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ملاحظات داخلية</label>
                            <textarea class="form-control" name="notes" rows="3">{{ old('notes', $salesOrder->notes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ملاحظات للعميل</label>
                            <textarea class="form-control" name="customer_notes" rows="3">{{ old('customer_notes', $salesOrder->customer_notes) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">عنوان الشحن</label>
                            <textarea class="form-control" name="shipping_address" rows="2" id="shipping_address">{{ old('shipping_address', $salesOrder->shipping_address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i>حفظ التغييرات
                        </button>
                        <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-secondary">
                            <i class="bi bi-x me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Product Line Template (Hidden) -->
<template id="line-template">
    <tr class="line-row">
        <td>
            <select class="form-select product-select" name="lines[INDEX][product_id]" required>
                <option value="">اختر المنتج...</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" 
                            data-price="{{ $product->selling_price }}"
                            data-unit="{{ $product->unit?->abbreviation ?? '' }}">
                        {{ $product->sku }} - {{ $product->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted product-info"></small>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" 
                   name="lines[INDEX][quantity]" step="0.01" min="0.01" value="1" required>
        </td>
        <td>
            <input type="number" class="form-control unit-price-input" 
                   name="lines[INDEX][unit_price]" step="0.01" min="0" required>
        </td>
        <td>
            <input type="number" class="form-control discount-input" 
                   name="lines[INDEX][discount_percent]" step="0.01" min="0" max="100" value="0">
        </td>
        <td>
            <strong class="line-total">0.00</strong> ج.م
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-line">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lineIndex = {{ $salesOrder->lines->count() }};
    const linesBody = document.getElementById('lines-body');
    const lineTemplate = document.getElementById('line-template');
    
    // Initialize existing lines
    document.querySelectorAll('.line-row').forEach(initLineRow);
    calculateTotals();
    
    // Add line button
    document.getElementById('add-line').addEventListener('click', addLine);
    
    function initLineRow(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const discountInput = row.querySelector('.discount-input');
        const removeBtn = row.querySelector('.remove-line');
        
        productSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (this.value) {
                unitPriceInput.value = selected.dataset.price || 0;
                row.querySelector('.product-info').textContent = selected.dataset.unit || '';
                calculateLineTotal(row);
            }
        });
        
        quantityInput.addEventListener('input', () => calculateLineTotal(row));
        unitPriceInput.addEventListener('input', () => calculateLineTotal(row));
        discountInput.addEventListener('input', () => calculateLineTotal(row));
        
        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.line-row').length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('يجب أن يحتوي الأمر على صنف واحد على الأقل');
            }
        });
    }
    
    function addLine() {
        const template = lineTemplate.content.cloneNode(true);
        const row = template.querySelector('tr');
        
        row.innerHTML = row.innerHTML.replace(/INDEX/g, lineIndex);
        initLineRow(row);
        
        linesBody.appendChild(row);
        lineIndex++;
    }
    
    function calculateLineTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        
        const subtotal = qty * price;
        const discountAmount = subtotal * (discount / 100);
        const total = subtotal - discountAmount;
        
        row.querySelector('.line-total').textContent = total.toFixed(2);
        calculateTotals();
    }
    
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.line-total').forEach(el => {
            subtotal += parseFloat(el.textContent) || 0;
        });
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total').textContent = subtotal.toFixed(2);
    }
});
</script>
@endpush
