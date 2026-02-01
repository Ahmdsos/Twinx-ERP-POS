@extends('layouts.app')

@section('title', 'تعديل ' . $warehouse->name . ' - Twinx ERP')
@section('page-title', 'تعديل المستودع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">المستودعات</a></li>
    <li class="breadcrumb-item"><a href="{{ route('warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>تعديل المستودع</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">كود المستودع</label>
                        <input type="text" class="form-control" value="{{ $warehouse->code }}" readonly>
                        <small class="text-muted">الكود لا يمكن تغييره</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">اسم المستودع <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $warehouse->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" 
                               name="address" value="{{ old('address', $warehouse->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" value="{{ old('phone', $warehouse->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email', $warehouse->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                               value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">مستودع نشط</label>
                    </div>
                    
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" 
                               value="1" {{ old('is_default', $warehouse->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">المستودع الافتراضي</label>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-1"></i>إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
