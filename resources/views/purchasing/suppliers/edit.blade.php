@extends('layouts.app')

@section('title', 'تعديل ' . $supplier->name . ' - Twinx ERP')
@section('page-title', 'تعديل بيانات المورد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">الموردين</a></li>
    <li class="breadcrumb-item"><a href="{{ route('suppliers.show', $supplier) }}">{{ $supplier->name }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>تعديل بيانات المورد</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Basic Info -->
                    <div class="col-md-6">
                        <label class="form-label">كود المورد</label>
                        <input type="text" class="form-control" value="{{ $supplier->code }}" readonly>
                        <small class="text-muted">الكود لا يمكن تغييره</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">اسم المورد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $supplier->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email', $supplier->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                            value="{{ old('phone', $supplier->phone) }}" dir="ltr">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">العنوان</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address"
                            rows="2">{{ old('address', $supplier->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الرقم الضريبي</label>
                        <input type="text" class="form-control @error('tax_number') is-invalid @enderror" name="tax_number"
                            value="{{ old('tax_number', $supplier->tax_number) }}">
                        @error('tax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">جهة الاتصال</label>
                        <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                            name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
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
                            name="payment_terms" value="{{ old('payment_terms', $supplier->payment_terms) }}" min="0">
                        @error('payment_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الحالة</label>
                        <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                            <option value="1" {{ old('is_active', $supplier->is_active) ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ !old('is_active', $supplier->is_active) ? 'selected' : '' }}>غير نشط
                            </option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes"
                            rows="3">{{ old('notes', $supplier->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">
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