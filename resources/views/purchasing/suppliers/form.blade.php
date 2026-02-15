<div class="row g-4">
    <!-- Basic Info -->
    <div class="col-md-8">
        <div class="glass-panel p-4 mb-4">
            <h5 class="text-cyan-400 mb-4 fw-bold"><i class="bi bi-info-circle me-2"></i>{{ __('Basic Information') }}</h5>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-gray-400 small fw-bold">كود المورد <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="code" id="codeField" class="form-control form-control-dark font-monospace text-center border-end-0" 
                            value="{{ old('code', $supplier->code ?? '') }}" 
                            {{ isset($supplier) ? 'readonly' : 'placeholder=SUP-XXXX' }}>
                        @if(!isset($supplier))
                        <button type="button" onclick="generateSupplierCode()" class="btn btn-outline-cyan border-start-0" title="توليد كود تلقائي">
                            <i class="bi bi-magic"></i>
                        </button>
                        @endif
                    </div>
                    @error('code') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label text-gray-400 small fw-bold">{{ __('Supplier Name') }}<span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-dark focus-ring-cyan" 
                        value="{{ old('name', $supplier->name ?? '') }}" required placeholder="اسم الشركة أو المورد">
                    @error('name') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-gray-400 small fw-bold">جهة الاتصال (الشخص المسؤول)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-person"></i></span>
                        <input type="text" name="contact_person" class="form-control form-control-dark border-start-0 focus-ring-cyan" 
                            value="{{ old('contact_person', $supplier->contact_person ?? '') }}" placeholder="مدير المبيعات / الحسابات">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-gray-400 small fw-bold">{{ __('Tax Number') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-receipt"></i></span>
                        <input type="text" name="tax_number" class="form-control form-control-dark border-start-0 font-monospace focus-ring-cyan" 
                            value="{{ old('tax_number', $supplier->tax_number ?? '') }}" placeholder="###-###-###">
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel p-4">
            <h5 class="text-cyan-400 mb-4 fw-bold"><i class="bi bi-geo-alt me-2"></i>بيانات العنوان</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label text-gray-400 small fw-bold">العنوان التفصيلي</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" name="address" class="form-control form-control-dark border-start-0 focus-ring-cyan" 
                            value="{{ old('address', $supplier->address ?? '') }}" placeholder="الشارع، الحي، المبنى...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact & Status -->
    <div class="col-md-4">
        <div class="glass-panel p-4 mb-4">
            <h5 class="text-cyan-400 mb-4 fw-bold"><i class="bi bi-telephone me-2"></i>معلومات التواصل</h5>
            
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label text-gray-400 small fw-bold">{{ __('Phone') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="phone" class="form-control form-control-dark border-start-0 font-monospace focus-ring-cyan" 
                            value="{{ old('phone', $supplier->phone ?? '') }}" placeholder="01xxxxxxxxx">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label text-gray-400 small fw-bold">{{ __('Email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control form-control-dark border-start-0 focus-ring-cyan" 
                            value="{{ old('email', $supplier->email ?? '') }}" placeholder="email@company.com">
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel p-4 mb-4">
            <h5 class="text-cyan-400 mb-4 fw-bold"><i class="bi bi-gear me-2"></i>{{ __('Settings') }}</h5>
            
            <div class="mb-3">
                <label class="form-label text-gray-400 small fw-bold">شروط الدفع (أيام)</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i class="bi bi-calendar-event"></i></span>
                    <input type="number" name="payment_terms" class="form-control form-control-dark border-start-0 text-center font-monospace focus-ring-cyan" 
                        value="{{ old('payment_terms', $supplier->payment_terms ?? 30) }}" min="0">
                    <span class="input-group-text bg-dark-input border-start-0 text-gray-400 small">يوم</span>
                </div>
            </div>

            <div class="form-check form-switch custom-toggle">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" 
                    {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label text-body ms-2" for="isActive">مورد نشط</label>
            </div>
        </div>
        
        <div class="glass-panel p-4">
             <label class="form-label text-gray-400 small fw-bold mb-2">ملاحظات إضافية</label>
             <textarea name="notes" class="form-control form-control-dark focus-ring-cyan" rows="3" placeholder="أي تفاصيل أخرى...">{{ old('notes', $supplier->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

<style>
    /* Reuse consistent styles */
    .form-control-dark {
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid var(--btn-glass-border); !important;
        color: var(--text-primary); !important;
    }
    .form-control-dark:focus {
        border-color: #22d3ee !important;
        box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.1) !important;
    }
    .focus-ring-cyan:focus {
        border-color: #22d3ee !important;
        box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.1) !important;
    }
    .bg-dark-input {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid var(--btn-glass-border); !important;
    }
    .btn-outline-cyan {
        color: #22d3ee;
        border-color: rgba(255, 255, 255, 0.1);
        background: rgba(15, 23, 42, 0.6);
    }
    .btn-outline-cyan:hover {
        background: rgba(6, 182, 212, 0.2);
        color: var(--text-primary);
        border-color: #22d3ee;
    }
</style>

<script>
    function generateSupplierCode() {
        // Generate format: SUP-{TimestampLast6}-{Random3}
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substring(2, 5).toUpperCase();
        document.getElementById('codeField').value = `SUP-${timestamp}-${random}`;
    }
</script>
