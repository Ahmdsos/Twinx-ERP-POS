@extends('layouts.app')

@section('title', 'تعديل بيانات العميل')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-white mb-0">تعديل بيانات العميل</h2>
                    <p class="text-gray-400 mb-0 x-small">تحديث بيانات: {{ $customer->name }}</p>
                </div>
            </div>
            <button type="submit" form="editForm"
                class="btn btn-action-indigo fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-save"></i> حفظ التعديلات
            </button>
        </div>

        <form action="{{ route('customers.update', $customer->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Main Info -->
                <div class="col-md-8">
                    <div class="glass-panel p-4 mb-4">
                        <div
                            class="d-flex justify-content-between align-items-center mb-4 border-bottom border-white-5 pb-2">
                            <h5 class="text-indigo-400 fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>البيانات الأساسية
                            </h5>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck"
                                    {{ $customer->is_active ? 'checked' : '' }}>
                                <label class="form-check-label text-white small" for="activeCheck">العميل نشط
                                    (Active)</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label text-gray-400 x-small fw-bold">اسم العميل <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('name', $customer->name) }}" required>
                                @error('name') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-gray-400 x-small fw-bold">المسؤول (Contact Person)</label>
                                <input type="text" name="contact_person"
                                    class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('contact_person', $customer->contact_person) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-400 x-small fw-bold">الرقم الضريبي</label>
                                <input type="text" name="tax_number"
                                    class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('tax_number', $customer->tax_number) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-gray-400 x-small fw-bold">نوع العميل</label>
                                <select name="type" class="form-select form-select-dark focus-ring-indigo">
                                    <option value="consumer" {{ old('type', $customer->type) == 'consumer' ? 'selected' : '' }}>فرد (Consumer)</option>
                                    <option value="company" {{ old('type', $customer->type) == 'company' ? 'selected' : '' }}>
                                        شركة (Company)</option>
                                    <option value="distributor" {{ old('type', $customer->type) == 'distributor' ? 'selected' : '' }}>موزع معتمد (Distributor)</option>
                                    <option value="wholesale" {{ old('type', $customer->type) == 'wholesale' ? 'selected' : '' }}>تاجر جملة (Wholesale)</option>
                                    <option value="half_wholesale" {{ old('type', $customer->type) == 'half_wholesale' ? 'selected' : '' }}>نص جملة (Half Wholesale)</option>
                                    <option value="quarter_wholesale" {{ old('type', $customer->type) == 'quarter_wholesale' ? 'selected' : '' }}>ربع جملة (Quarter Wholesale)</option>
                                    <option value="technician" {{ old('type', $customer->type) == 'technician' ? 'selected' : '' }}>فني / مقاول (Technician)</option>
                                    <option value="employee" {{ old('type', $customer->type) == 'employee' ? 'selected' : '' }}>موظف (Employee)</option>
                                    <option value="vip" {{ old('type', $customer->type) == 'vip' ? 'selected' : '' }}>عميل
                                        مميز (VIP)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-400 x-small fw-bold">الهاتف</label>
                                <input type="text" name="phone" class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('phone', $customer->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-400 x-small fw-bold">الموبايل</label>
                                <input type="text" name="mobile" class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('mobile', $customer->mobile) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-gray-400 x-small fw-bold">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('email', $customer->email) }}">
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel p-4">
                        <h5 class="text-indigo-400 fw-bold mb-4 border-bottom border-white-5 pb-2"><i
                                class="bi bi-geo-alt me-2"></i>العناوين</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-white small fw-bold mb-3">عنوان الفوترة (Billing)</h6>
                                <div class="mb-2">
                                    <label class="form-label text-gray-400 x-small">العنوان</label>
                                    <input type="text" name="billing_address"
                                        class="form-control form-control-dark focus-ring-indigo"
                                        value="{{ old('billing_address', $customer->billing_address) }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-gray-400 x-small">المدينة</label>
                                    <input type="text" name="billing_city"
                                        class="form-control form-control-dark focus-ring-indigo"
                                        value="{{ old('billing_city', $customer->billing_city) }}">
                                </div>
                            </div>
                            <div class="col-md-6 border-start border-white-5 ps-md-4">
                                <h6 class="text-white small fw-bold mb-3">عنوان الشحن (Shipping)</h6>
                                <div class="mb-2">
                                    <label class="form-label text-gray-400 x-small">العنوان</label>
                                    <input type="text" name="shipping_address"
                                        class="form-control form-control-dark focus-ring-indigo"
                                        value="{{ old('shipping_address', $customer->shipping_address) }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-gray-400 x-small">المدينة</label>
                                    <input type="text" name="shipping_city"
                                        class="form-control form-control-dark focus-ring-indigo"
                                        value="{{ old('shipping_city', $customer->shipping_city) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Info (Sidebar) -->
                <div class="col-md-4">
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="text-indigo-400 fw-bold mb-4 border-bottom border-white-5 pb-2"><i
                                class="bi bi-cash-stack me-2"></i>البيانات المالية</h5>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">حد الائتمان (Credit Limit)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="credit_limit"
                                    class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('credit_limit', $customer->credit_limit) }}">
                                <span class="input-group-text bg-dark-input border-start-0 text-gray-400">EGP</span>
                            </div>
                            <div class="form-text text-gray-500 x-small">أقصى مبلغ مسموح به كمديونية</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">شروط الدفع (Payment Terms)</label>
                            <div class="input-group">
                                <input type="number" name="payment_terms"
                                    class="form-control form-control-dark focus-ring-indigo"
                                    value="{{ old('payment_terms', $customer->payment_terms) }}">
                                <span class="input-group-text bg-dark-input border-start-0 text-gray-400">يوم</span>
                            </div>
                            <div class="form-text text-gray-500 x-small">عدد الأيام المسموح بها للسداد</div>
                        </div>
                    </div>

                    <div class="glass-panel p-4">
                        <h5 class="text-gray-400 fw-bold mb-3 border-bottom border-white-5 pb-2">ملاحظات</h5>
                        <textarea name="notes" class="form-control form-control-dark focus-ring-indigo"
                            rows="4">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            backdrop-filter: blur(12px);
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .btn-action-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-action-indigo:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .focus-ring-indigo:focus {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.1) !important;
        }

        .bg-dark-input {
            background: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
    </style>
@endsection