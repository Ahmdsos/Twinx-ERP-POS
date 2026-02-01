@extends('layouts.app')

@section('title', 'عرض سعر جديد - Twinx ERP')
@section('page-title', 'عرض سعر جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}">عروض الأسعار</a></li>
    <li class="breadcrumb-item active">عرض جديد</li>
@endsection

@section('content')
<form action="{{ route('quotations.store') }}" method="POST" id="quotation-form">
    @csrf
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Customer Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>بيانات العميل</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">العميل <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    name="customer_id" id="customer-select" required>
                                <option value="">اختر العميل</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ (old('customer_id') ?? $selectedCustomer?->id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->code }} - {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">تاريخ العرض <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('quotation_date') is-invalid @enderror" 
                                   name="quotation_date" value="{{ old('quotation_date', date('Y-m-d')) }}" required>
                            @error('quotation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">صالح حتى <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('valid_until') is-invalid @enderror" 
                                   name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lines Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>الأصناف</h5>
                    <button type="button" class="btn btn-sm btn-success" id="add-line">
                        <i class="bi bi-plus-lg me-1"></i>إضافة صنف
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="lines-table">
                            <thead class="table-light">
                                <tr>
                                    <th>الصنف</th>
                                    <th style="width: 100px;">الكمية</th>
                                    <th style="width: 120px;">السعر</th>
                                    <th style="width: 80px;">خصم %</th>
                                    <th style="width: 120px;">الإجمالي</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                <!-- Dynamic lines will be added here -->
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-start"><strong>الإجمالي</strong></td>
                                    <td><strong id="grand-total">0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes & Terms -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">ملاحظات</h6>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="ملاحظات للعميل">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">الشروط والأحكام</h6>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" name="terms" rows="3" 
                                      placeholder="شروط الدفع والتسليم">{{ old('terms') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-2"></i>حفظ العرض
                        </button>
                        <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Products data for JS -->
<script>
    const productsData = @json($products->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->sku,
        'price' => $p->sale_price,
        'unit' => $p->unit?->name ?? '',
    ]));
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lineIndex = 0;
    const linesBody = document.getElementById('lines-body');
    
    function addLine(productId = '', quantity = 1, unitPrice = 0, discountPercent = 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select class="form-select form-select-sm product-select" 
                        name="lines[${lineIndex}][product_id]" required>
                    <option value="">اختر صنف</option>
                    ${productsData.map(p => `
                        <option value="${p.id}" 
                                data-price="${p.price}" 
                                data-unit="${p.unit}"
                                ${productId == p.id ? 'selected' : ''}>
                            ${p.sku} - ${p.name}
                        </option>
                    `).join('')}
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" 
                       class="form-control form-control-sm quantity-input" 
                       name="lines[${lineIndex}][quantity]" 
                       value="${quantity}" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" 
                       class="form-control form-control-sm price-input" 
                       name="lines[${lineIndex}][unit_price]" 
                       value="${unitPrice}" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" max="100" 
                       class="form-control form-control-sm discount-input" 
                       name="lines[${lineIndex}][discount_percent]" 
                       value="${discountPercent}">
            </td>
            <td class="line-total">0.00</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-line">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        linesBody.appendChild(row);
        lineIndex++;
        
        // Bind events
        row.querySelector('.product-select').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                row.querySelector('.price-input').value = option.dataset.price || 0;
                calculateLineTotal(row);
            }
        });
        
        row.querySelectorAll('.quantity-input, .price-input, .discount-input').forEach(input => {
            input.addEventListener('input', () => calculateLineTotal(row));
        });
        
        row.querySelector('.remove-line').addEventListener('click', () => {
            row.remove();
            calculateGrandTotal();
        });
        
        calculateLineTotal(row);
    }
    
    function calculateLineTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        
        const gross = qty * price;
        const discountAmount = gross * (discount / 100);
        const total = gross - discountAmount;
        
        row.querySelector('.line-total').textContent = total.toFixed(2);
        calculateGrandTotal();
    }
    
    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.line-total').forEach(td => {
            total += parseFloat(td.textContent) || 0;
        });
        document.getElementById('grand-total').textContent = total.toFixed(2);
    }
    
    document.getElementById('add-line').addEventListener('click', () => addLine());
    
    // Add first line automatically
    addLine();
});
</script>
@endpush
