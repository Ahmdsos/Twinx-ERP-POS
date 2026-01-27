@extends('layouts.app')

@section('title', 'إضافة عميل - Twinx ERP')
@section('page-title', 'إضافة عميل جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item active">إضافة عميل</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>إضافة عميل جديد</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.store') }}" method="POST">
                        @csrf

                        <!-- Basic Info -->
                        <h6 class="text-muted mb-3"><i class="bi bi-person me-1"></i>المعلومات الأساسية</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">النوع</label>
                                <select class="form-select" name="type">
                                    <option value="company" {{ old('type') == 'company' ? 'selected' : '' }}>شركة</option>
                                    <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>فرد
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" name="tax_number" value="{{ old('tax_number') }}">
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <h6 class="text-muted mb-3 mt-4"><i class="bi bi-telephone me-1"></i>معلومات الاتصال</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الهاتف</label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الموبايل</label>
                                <input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">جهة الاتصال</label>
                            <input type="text" class="form-control" name="contact_person"
                                value="{{ old('contact_person') }}">
                        </div>

                        <!-- Addresses -->
                        <h6 class="text-muted mb-3 mt-4"><i class="bi bi-geo-alt me-1"></i>العناوين</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">عنوان الفاتورة</label>
                                    <textarea class="form-control" name="billing_address"
                                        rows="2">{{ old('billing_address') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المدينة (الفاتورة)</label>
                                    <input type="text" class="form-control" name="billing_city"
                                        value="{{ old('billing_city') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">عنوان الشحن</label>
                                    <textarea class="form-control" name="shipping_address"
                                        rows="2">{{ old('shipping_address') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المدينة (الشحن)</label>
                                    <input type="text" class="form-control" name="shipping_city"
                                        value="{{ old('shipping_city') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Financial Info -->
                        <h6 class="text-muted mb-3 mt-4"><i class="bi bi-wallet2 me-1"></i>المعلومات المالية</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">مدة السداد (أيام)</label>
                                <input type="number" class="form-control" name="payment_terms"
                                    value="{{ old('payment_terms', 30) }}" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">حد الائتمان</label>
                                <input type="number" class="form-control money-input" name="credit_limit"
                                    value="{{ old('credit_limit', 0) }}" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>حفظ
                            </button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection