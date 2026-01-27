@extends('layouts.app')

@section('title', 'تعديل حساب - Twinx ERP')
@section('page-title', 'تعديل حساب')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('accounts.index') }}">دليل الحسابات</a></li>
    <li class="breadcrumb-item active">تعديل {{ $account->code }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>تعديل حساب: {{ $account->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('accounts.update', $account) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">كود الحساب <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   name="code" value="{{ old('code', $account->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-8 mb-3">
                            <label class="form-label">اسم الحساب <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $account->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع الحساب <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->value }}" {{ old('type', $account->type->value) == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الحساب الأب</label>
                            <select class="form-select" name="parent_id">
                                <option value="">بدون (حساب رئيسي)</option>
                                @foreach($parentAccounts as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->code }} - {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $account->description) }}</textarea>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">حساب نشط</label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>تحديث
                        </button>
                        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
