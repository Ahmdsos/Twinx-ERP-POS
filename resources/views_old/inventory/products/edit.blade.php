@extends('layouts.app')

@section('title', 'تعديل ' . $product->name . ' - Twinx ERP')
@section('page-title', 'تعديل المنتج')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">المنتجات</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
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
                            <label class="form-label">كود المنتج (SKU)</label>
                            <input type="text" class="form-control" value="{{ $product->sku }}" readonly>
                            <small class="text-muted">لا يمكن تغيير الكود</small>
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">الباركود</label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                   name="barcode" value="{{ old('barcode', $product->barcode) }}" dir="ltr">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">نوع المنتج</label>
                            <input type="text" class="form-control" value="{{ $product->type == 'goods' ? 'بضاعة' : 'خدمة' }}" readonly>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">الوصف</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" rows="3">{{ old('description', $product->description) }}</textarea>
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
                                       name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0">
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
                                       name="sale_price" value="{{ old('sale_price', $product->selling_price) }}" step="0.01" min="0" required>
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
                                       name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}" step="0.01" min="0" max="100">
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
                                   name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock) }}" step="0.01" min="0">
                            @error('min_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">كمية إعادة الطلب</label>
                            <input type="number" class="form-control @error('reorder_quantity') is-invalid @enderror" 
                                   name="reorder_quantity" value="{{ old('reorder_quantity', $product->reorder_quantity) }}" step="0.01" min="0">
                            @error('reorder_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">الحد الأقصى للمخزون</label>
                            <input type="number" class="form-control @error('max_stock_level') is-invalid @enderror" 
                                   name="max_stock_level" value="{{ old('max_stock_level', $product->max_stock) }}" step="0.01" min="0">
                            @error('max_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Product Images Disabled (as requested) --}}
            {{--
            <!-- Product Images -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-images me-2"></i>صور المنتج</h5>
                </div>
                <div class="card-body">
                    <!-- Existing Images -->
                    @if($product->images && $product->images->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">الصور الحالية</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->images as $image)
                                    <div class="position-relative" style="width: 100px;">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                             class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                        @if($image->is_primary)
                                            <span class="badge bg-primary position-absolute top-0 start-0">رئيسية</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Upload New Images -->
                    <div class="mb-3">
                        <label class="form-label">رفع صور جديدة</label>
                        <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                               name="images[]" multiple accept="image/*">
                        <small class="text-muted">يمكنك رفع حتى 5 صور (JPG, PNG, WEBP)</small>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            --}}
            
            <!-- Extended Product Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>معلومات إضافية</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Brand -->
                        <div class="col-md-6">
                            <label class="form-label">العلامة التجارية</label>
                            <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                   name="brand" value="{{ old('brand', $product->brand) }}">
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Warranty -->
                        <div class="col-md-6">
                            <label class="form-label">مدة الضمان (شهور)</label>
                            <input type="number" class="form-control @error('warranty_months') is-invalid @enderror" 
                                   name="warranty_months" value="{{ old('warranty_months', $product->warranty_months ?? 0) }}" min="0">
                            @error('warranty_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Weight -->
                        <div class="col-md-4">
                            <label class="form-label">الوزن</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                       name="weight" value="{{ old('weight', $product->weight) }}" step="0.0001" min="0">
                                <select class="form-select" name="weight_unit" style="max-width: 80px;">
                                    <option value="kg" {{ old('weight_unit', $product->weight_unit ?? 'kg') == 'kg' ? 'selected' : '' }}>كجم</option>
                                    <option value="g" {{ old('weight_unit', $product->weight_unit) == 'g' ? 'selected' : '' }}>جم</option>
                                </select>
                            </div>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Dimensions -->
                        <div class="col-md-8">
                            <label class="form-label">الأبعاد (الطول × العرض × الارتفاع)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="length" 
                                       value="{{ old('length', $product->length) }}" placeholder="الطول" step="0.01" min="0">
                                <span class="input-group-text">×</span>
                                <input type="number" class="form-control" name="width" 
                                       value="{{ old('width', $product->width) }}" placeholder="العرض" step="0.01" min="0">
                                <span class="input-group-text">×</span>
                                <input type="number" class="form-control" name="height" 
                                       value="{{ old('height', $product->height) }}" placeholder="الارتفاع" step="0.01" min="0">
                                <select class="form-select" name="dimension_unit" style="max-width: 80px;">
                                    <option value="cm" {{ old('dimension_unit', $product->dimension_unit ?? 'cm') == 'cm' ? 'selected' : '' }}>سم</option>
                                    <option value="m" {{ old('dimension_unit', $product->dimension_unit) == 'm' ? 'selected' : '' }}>م</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Expiry Date -->
                        <div class="col-md-6">
                            <label class="form-label">تاريخ الانتهاء</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   name="expiry_date" value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Country of Origin -->
                        <div class="col-md-6">
                            <label class="form-label">بلد المنشأ</label>
                            <input type="text" class="form-control @error('country_of_origin') is-invalid @enderror" 
                                   name="country_of_origin" value="{{ old('country_of_origin', $product->country_of_origin) }}">
                            @error('country_of_origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Tracking Options -->
                        <div class="col-12">
                            <hr>
                            <label class="form-label">خيارات التتبع</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="track_batches" 
                                               id="track_batches" value="1" {{ old('track_batches', $product->track_batches) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="track_batches">تتبع الدفعات (Batch)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="track_serials" 
                                               id="track_serials" value="1" {{ old('track_serials', $product->track_serials) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="track_serials">تتبع الأرقام التسلسلية (Serial)</label>
                                    </div>
                                </div>
                            </div>
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
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label">وحدة القياس</label>
                        <select class="form-select @error('unit_id') is-invalid @enderror" name="unit_id">
                            @foreach($units ?? [] as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
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
                               value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">منتج نشط</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_purchasable" id="is_purchasable" 
                               value="1" {{ old('is_purchasable', $product->is_purchasable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_purchasable">قابل للشراء</label>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_sellable" id="is_sellable" 
                               value="1" {{ old('is_sellable', $product->is_sellable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_sellable">قابل للبيع</label>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-1"></i>حفظ التغييرات
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
