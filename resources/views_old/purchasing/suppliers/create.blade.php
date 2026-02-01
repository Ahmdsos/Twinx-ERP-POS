@extends('layouts.app')

@section('title', 'إضافة مورد - Twinx ERP')
@section('page-title', 'إضافة مورد جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">الموردين</a></li>
    <li class="breadcrumb-item active">إضافة جديد</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة مورد جديد</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <!-- Basic Info -->
                    <div class="col-md-6">
                        <label class="form-label">كود المورد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code') }}" placeholder="SUP-001" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">اسم المورد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                            value="{{ old('phone') }}" dir="ltr">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">العنوان</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address"
                            rows="2">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الرقم الضريبي</label>
                        <input type="text" class="form-control @error('tax_number') is-invalid @enderror" name="tax_number"
                            value="{{ old('tax_number') }}">
                        @error('tax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">جهة الاتصال</label>
                        <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                            name="contact_person" value="{{ old('contact_person') }}">
                        @error('contact_person')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Financial Info -->
                    <div class="col-12">
                        <hr>
                        <h6 class="text-muted mb-3">المعلومات المالية</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">شروط الدفع (بالأيام)</label>
                        <input type="number" class="form-control @error('payment_terms') is-invalid @enderror"
                            name="payment_terms" value="{{ old('payment_terms', 30) }}" min="0">
                        @error('payment_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الحالة</label>
                        <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                            <option value="1" {{ old('is_active', 1) ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ !old('is_active', 1) ? 'selected' : '' }}>غير نشط</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes"
                            rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-1"></i>إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>حفظ المورد
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection