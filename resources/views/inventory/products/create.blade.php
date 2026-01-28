@extends('layouts.app')

@section('title', 'إضافة منتج - Twinx ERP')
@section('page-title', 'إضافة منتج جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">المنتجات</a></li>
    <li class="breadcrumb-item active">إضافة جديد</li>
@endsection

@section('content')
<form action="{{ route('products.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box me-2"></i>معلومات المنتج</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">كود المنتج (SKU) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                   name="sku" value="{{ old('sku') }}" required placeholder="PRD-001">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">الباركود</label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                   name="barcode" value="{{ old('barcode') }}" dir="ltr">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">نوع المنتج <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                <option value="">اختر النوع...</option>
                                <option value="goods" {{ old('type') == 'goods' ? 'selected' : '' }}>بضاعة</option>
                                <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>خدمة</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">الوصف</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pricing -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>التسعير</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">سعر التكلفة</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('cost_price') is-invalid @enderror" 
                                       name="cost_price" value="{{ old('cost_price', 0) }}" step="0.01" min="0">
                                <span class="input-group-text">ج.م</span>
                            </div>
                            @error('cost_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('sale_price') is-invalid @enderror" 
                                       name="sale_price" value="{{ old('sale_price') }}" step="0.01" min="0" required>
                                <span class="input-group-text">ج.م</span>
                            </div>
                            @error('sale_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">نسبة الضريبة %</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" 
                                       name="tax_rate" value="{{ old('tax_rate', 14) }}" step="0.01" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stock Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>إعدادات المخزون</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">الحد الأدنى للمخزون</label>
                            <input type="number" class="form-control @error('min_stock_level') is-invalid @enderror" 
                                   name="min_stock_level" value="{{ old('min_stock_level', 0) }}" step="0.01" min="0">
                            @error('min_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">كمية إعادة الطلب</label>
                            <input type="number" class="form-control @error('reorder_quantity') is-invalid @enderror" 
                                   name="reorder_quantity" value="{{ old('reorder_quantity', 0) }}" step="0.01" min="0">
                            @error('reorder_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">الحد الأقصى للمخزون</label>
                            <input type="number" class="form-control @error('max_stock_level') is-invalid @enderror" 
                                   name="max_stock_level" value="{{ old('max_stock_level') }}" step="0.01" min="0">
                            @error('max_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Category & Unit -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-folder me-2"></i>التصنيف والوحدة</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">التصنيف</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                            <option value="">بدون تصنيف</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label">وحدة القياس <span class="text-danger">*</span></label>
                        <select class="form-select @error('unit_id') is-invalid @enderror" name="unit_id" required>
                            <option value="">اختر الوحدة...</option>
                            @foreach($units ?? [] as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->abbreviation }})
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>الحالة</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">منتج نشط</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_purchasable" id="is_purchasable" 
                               value="1" {{ old('is_purchasable', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_purchasable">قابل للشراء</label>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_sellable" id="is_sellable" 
                               value="1" {{ old('is_sellable', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_sellable">قابل للبيع</label>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-1"></i>حفظ المنتج
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-x me-1"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
