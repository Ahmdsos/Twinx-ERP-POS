@extends('layouts.app')

@section('title', 'تسجيل دفعة للمورد - Twinx ERP')
@section('page-title', 'تسجيل دفعة للمورد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}">مدفوعات الموردين</a></li>
    <li class="breadcrumb-item active">دفعة جديدة</li>
@endsection

@section('content')
<form action="{{ route('supplier-payments.store') }}" method="POST" id="payment-form">
    @csrf
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cash me-2"></i>بيانات الدفعة</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">المورد <span class="text-danger">*</span></label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                    name="supplier_id" id="supplier-select" required>
                                <option value="">اختر المورد</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                            {{ (old('supplier_id') ?? $selectedInvoice?->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                       name="amount" id="payment-amount" 
                                       value="{{ old('amount', $selectedInvoice?->balance_due) }}" required>
                                <span class="input-group-text">ج.م</span>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" 
                                    name="payment_method" required>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>نقدي</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>شيك</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">حساب الدفع <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_account_id') is-invalid @enderror" 
                                    name="payment_account_id" required>
                                <option value="">اختر الحساب</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">رقم المرجع</label>
                            <input type="text" class="form-control" name="reference" 
                                   value="{{ old('reference') }}" placeholder="رقم الشيك / رقم التحويل">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">ملاحظات</label>
                            <input type="text" class="form-control" name="notes" 
                                   value="{{ old('notes') }}" placeholder="ملاحظات إضافية">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Allocation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>تخصيص على الفواتير</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="allocations-table">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>الإجمالي</th>
                                    <th>المتبقي</th>
                                    <th>قيمة التخصيص</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingInvoices->groupBy('supplier_id') as $supplierId => $invoices)
                                    @foreach($invoices as $idx => $invoice)
                                        <tr class="invoice-row" data-supplier="{{ $supplierId }}">
                                            <td>
                                                {{ $invoice->invoice_number }}
                                                <input type="hidden" name="allocations[{{ $idx }}][invoice_id]" 
                                                       value="{{ $invoice->id }}" disabled class="invoice-input">
                                            </td>
                                            <td>
                                                {{ $invoice->due_date?->format('Y-m-d') }}
                                                @if($invoice->isOverdue())
                                                    <span class="badge bg-danger">متأخر</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($invoice->total, 2) }}</td>
                                            <td class="fw-bold text-danger">{{ number_format($invoice->balance_due, 2) }}</td>
                                            <td>
                                                <input type="number" step="0.01" min="0" 
                                                       max="{{ $invoice->balance_due }}"
                                                       class="form-control form-control-sm allocation-amount" 
                                                       name="allocations[{{ $idx }}][amount]"
                                                       data-balance="{{ $invoice->balance_due }}"
                                                       value="{{ $selectedInvoice && $selectedInvoice->id == $invoice->id ? $invoice->balance_due : 0 }}"
                                                       disabled>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            لا توجد فواتير مستحقة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-start"><strong>إجمالي التخصيص</strong></td>
                                    <td><strong id="total-allocation">0.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-2"></i>تسجيل الدفعة
                        </button>
                        <a href="{{ route('supplier-payments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>

            <!-- Help -->
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-info"><i class="bi bi-info-circle me-1"></i>ملاحظة</h6>
                    <small class="text-muted">
                        <ul class="ps-3 mb-0">
                            <li>يمكنك تخصيص المبلغ على عدة فواتير</li>
                            <li>المبلغ غير المخصص يبقى رصيد دائن للمورد</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierSelect = document.getElementById('supplier-select');
    const allocationAmounts = document.querySelectorAll('.allocation-amount');
    
    // Filter invoices by supplier
    function filterInvoicesBySupplier() {
        const supplierId = supplierSelect.value;
        document.querySelectorAll('.invoice-row').forEach(row => {
            const invoiceInputs = row.querySelectorAll('.invoice-input, .allocation-amount');
            if (row.dataset.supplier == supplierId) {
                row.style.display = '';
                invoiceInputs.forEach(el => el.disabled = false);
            } else {
                row.style.display = 'none';
                invoiceInputs.forEach(el => el.disabled = true);
            }
        });
        calculateTotalAllocation();
    }
    
    // Calculate total allocation
    function calculateTotalAllocation() {
        let total = 0;
        document.querySelectorAll('.invoice-row:not([style*="display: none"]) .allocation-amount').forEach(input => {
            total += parseFloat(input.value || 0);
        });
        document.getElementById('total-allocation').textContent = total.toFixed(2);
    }
    
    supplierSelect.addEventListener('change', filterInvoicesBySupplier);
    allocationAmounts.forEach(input => {
        input.addEventListener('input', calculateTotalAllocation);
    });
    
    // Initial filter
    filterInvoicesBySupplier();
});
</script>
@endpush
