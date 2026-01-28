@extends('layouts.app')

@section('title', 'تعديل ' . $customer->name . ' - Twinx ERP')
@section('page-title', 'تعديل بيانات العميل')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>تعديل بيانات العميل</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Basic Info -->
                    <div class="col-md-6">
                        <label class="form-label">كود العميل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code', $customer->code) }}" readonly>
                        <small class="text-muted">الكود لا يمكن تغييره</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $customer->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email', $customer->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                            value="{{ old('phone', $customer->phone) }}" dir="ltr">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">العنوان</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address"
                            rows="2">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الرقم الضريبي</label>
                        <input type="text" class="form-control @error('tax_number') is-invalid @enderror" name="tax_number"
                            value="{{ old('tax_number', $customer->tax_number) }}">
                        @error('tax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Financial Info -->
                    <div class="col-12">
                        <hr>
                        <h6 class="text-muted mb-3">المعلومات المالية</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">شروط الدفع (بالأيام)</label>
                        <input type="number" class="form-control @error('payment_terms') is-invalid @enderror"
                            name="payment_terms" value="{{ old('payment_terms', $customer->payment_terms) }}" min="0">
                        @error('payment_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">حد الائتمان</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('credit_limit') is-invalid @enderror"
                                name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" step="0.01"
                                min="0">
                            <span class="input-group-text">ج.م</span>
                        </div>
                        @error('credit_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">الحالة</label>
                        <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                            <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>غير نشط
                            </option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes"
                            rows="3">{{ old('notes', $customer->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-1"></i>إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection