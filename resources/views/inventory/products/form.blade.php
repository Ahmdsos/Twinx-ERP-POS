@php
    $isEdit = isset($product);
    $action = $isEdit ? route('products.update', $product->id) : route('products.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Sidebar Tabs -->
        <div class="col-lg-3">
            <div class="glass-panel p-3 sticky-top" style="top: 100px; z-index: 10;">
                <h5 class="fw-bold text-white mb-4 px-2 tracking-wide">بيانات المنتج</h5>
                <div class="nav flex-column nav-pills custom-pills gap-2" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <button class="nav-link active d-flex align-items-center gap-3 py-3 px-3" id="basic-tab"
                        data-bs-toggle="pill" data-bs-target="#basic" type="button">
                        <i class="bi bi-info-circle fs-5"></i>
                        <span class="fw-bold small">الأساسية</span>
                    </button>
                    <button class="nav-link d-flex align-items-center gap-3 py-3 px-3" id="pricing-tab"
                        data-bs-toggle="pill" data-bs-target="#pricing" type="button">
                        <i class="bi bi-currency-dollar fs-5"></i>
                        <span class="fw-bold small">التسعيـر</span>
                    </button>
                    <button class="nav-link d-flex align-items-center gap-3 py-3 px-3" id="inventory-tab"
                        data-bs-toggle="pill" data-bs-target="#inventory" type="button">
                        <i class="bi bi-boxes fs-5"></i>
                        <span class="fw-bold small">المخزون</span>
                    </button>
                    <button class="nav-link d-flex align-items-center gap-3 py-3 px-3" id="attributes-tab"
                        data-bs-toggle="pill" data-bs-target="#attributes" type="button">
                        <i class="bi bi-list-check fs-5"></i>
                        <span class="fw-bold small">المواصفات</span>
                    </button>
                    <button class="nav-link d-flex align-items-center gap-3 py-3 px-3" id="images-tab"
                        data-bs-toggle="pill" data-bs-target="#images" type="button">
                        <i class="bi bi-images fs-5"></i>
                        <span class="fw-bold small">الصـور</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-lg-9">
            <div class="glass-panel p-5 position-relative">
                <div class="glow-orb bg-purple-500 opacity-10" style="top: -50px; left: 50%;"></div>

                <div class="tab-content" id="v-pills-tabContent">

                    <!-- 1. Basic Details -->
                    <div class="tab-pane fade show active" id="basic">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-white-10 pb-3">البيانات الأساسية</h4>
                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">اسم المنتج
                                    <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('name', $product->name ?? '') }}"
                                    placeholder="مثال: آيفون 15 برو ماكس" required>
                                @error('name') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">SKU <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-barcode"></i></span>
                                    <input type="text" name="sku" id="skuInput"
                                        class="form-control form-control-dark border-start-0 border-end-0 ps-0 text-white font-monospace placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('sku', $product->sku ?? '') }}" placeholder="AUTO-GEN" required>
                                    <button type="button" onclick="generateSKU()"
                                        class="btn btn-outline-purple border-start-0" title="توليد كود تلقائي">
                                        <i class="bi bi-magic"></i>
                                    </button>
                                </div>
                                @error('sku') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الوصف</label>
                                <textarea name="description"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    rows="4"
                                    placeholder="أدخل وصفاً دقيقاً للمنتج...">{{ old('description', $product->description ?? '') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">نوع المنتج
                                    <span class="text-danger">*</span></label>
                                <select name="type" class="form-select form-select-dark text-white cursor-pointer"
                                    required>
                                    @foreach($types as $type)
                                        <option value="{{ $type->value }}" {{ old('type', $product->type->value ?? '') == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">باركود
                                    (Scan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="barcode" id="barcodeInput"
                                        class="form-control form-control-dark border-start-0 border-end-0 ps-0 text-white font-monospace placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('barcode', $product->barcode ?? '') }}"
                                        placeholder="امسح الباركود...">
                                    <button type="button" onclick="generateBarcode()"
                                        class="btn btn-outline-purple border-start-0" title="توليد باركود تلقائي">
                                        <i class="bi bi-magic"></i> توليد
                                    </button>
                                </div>
                                @error('barcode') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">القسم /
                                    التصنيف</label>
                                <select name="category_id"
                                    class="form-select form-select-dark text-white cursor-pointer">
                                    <option value="">-- بدون تصنيف --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الماركة /
                                    Brand</label>
                                <select name="brand_id" class="form-select form-select-dark text-white cursor-pointer">
                                    <option value="">-- بدون ماركة --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الوحدة
                                    الأساسية <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-select form-select-dark text-white cursor-pointer"
                                    required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} ({{ $unit->abbreviation }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Toggles (Custom CSS) -->
                            <div class="col-12 mt-4">
                                <div class="p-4 rounded-3 bg-slate-900 bg-opacity-30 border border-white-5">
                                    <div class="d-flex flex-wrap gap-4 justify-content-center">

                                        <!-- Active Toggle -->
                                        <label class="custom-toggle d-flex align-items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                            <span class="toggle-switch"></span>
                                            <div>
                                                <span class="text-white fw-bold d-block">نشط</span>
                                                <span class="text-gray-500 x-small">يظهر في النظام</span>
                                            </div>
                                        </label>

                                        <div class="vr bg-white opacity-10 mx-2"></div>

                                        <!-- Sellable Toggle -->
                                        <label class="custom-toggle d-flex align-items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="is_sellable" value="1" {{ old('is_sellable', $product->is_sellable ?? true) ? 'checked' : '' }}>
                                            <span class="toggle-switch"></span>
                                            <div>
                                                <span class="text-white fw-bold d-block">قابل للبيع</span>
                                                <span class="text-gray-500 x-small">يظهر في الكاشير</span>
                                            </div>
                                        </label>

                                        <div class="vr bg-white opacity-10 mx-2"></div>

                                        <!-- Purchasable Toggle -->
                                        <label class="custom-toggle d-flex align-items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="is_purchasable" value="1" {{ old('is_purchasable', $product->is_purchasable ?? true) ? 'checked' : '' }}>
                                            <span class="toggle-switch"></span>
                                            <div>
                                                <span class="text-white fw-bold d-block">قابل للشراء</span>
                                                <span class="text-gray-500 x-small">يظهر في أوامر الشراء</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pricing -->
                    <div class="tab-pane fade" id="pricing">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-white-10 pb-3">بيانات التسعير</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">سعر التكلفة
                                    (Cost) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text bg-dark-input border-end-0 text-gray-500 fw-bold">EGP</span>
                                    <input type="number" step="0.01" name="cost_price"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white fw-bold placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('cost_price', $product->cost_price ?? '') }}" placeholder="0.00"
                                        required>
                                </div>
                                @error('cost_price') <div class="text-danger x-small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-success-400 small fw-bold text-uppercase ps-1">سعر البيع
                                    (Selling) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text bg-dark-input border-end-0 text-success fw-bold">EGP</span>
                                    <input type="number" step="0.01" name="sale_price"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white fw-bold placeholder-gray-600 focus-ring-success"
                                        value="{{ old('sale_price', $product->selling_price ?? '') }}"
                                        placeholder="0.00" required>
                                </div>
                                @error('sale_price') <div class="text-danger x-small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">نسبة الضريبة
                                    (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.5" name="tax_rate"
                                        class="form-control form-control-dark border-end-0 text-white placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('tax_rate', $product->tax_rate ?? 14) }}" placeholder="14">
                                    <span class="input-group-text bg-dark-input border-start-0 text-gray-500">%</span>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <label class="custom-toggle d-flex align-items-center gap-3 cursor-pointer mt-4 ms-2">
                                    <input type="checkbox" name="is_tax_inclusive" value="1" {{ old('is_tax_inclusive', $product->is_tax_inclusive ?? false) ? 'checked' : '' }}>
                                    <span class="toggle-switch"></span>
                                    <span class="text-white">السعر شامل الضريبة</span>
                                </label>
                            </div>
                        </div>

                        <h5 class="text-white fw-bold mt-5 mb-4 border-bottom border-white-10 pb-3">شرائح الأسعار
                            (Multi-Tier Pricing)</h5>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label text-gray-400 small fw-bold text-uppercase ps-1">سعر الموزع
                                    (Distributor)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500">EGP</span>
                                    <input type="number" step="0.01" name="price_distributor"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('price_distributor', $product->price_distributor ?? '') }}"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-gray-400 small fw-bold text-uppercase ps-1">سعر الجملة
                                    (Wholesale)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500">EGP</span>
                                    <input type="number" step="0.01" name="price_wholesale"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('price_wholesale', $product->price_wholesale ?? '') }}"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-gray-400 small fw-bold text-uppercase ps-1">نصف جملة (Half
                                    Wholesale)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500">EGP</span>
                                    <input type="number" step="0.01" name="price_half_wholesale"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('price_half_wholesale', $product->price_half_wholesale ?? '') }}"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-gray-400 small fw-bold text-uppercase ps-1">ربع جملة
                                    (Quarter Wholesale)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500">EGP</span>
                                    <input type="number" step="0.01" name="price_quarter_wholesale"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('price_quarter_wholesale', $product->price_quarter_wholesale ?? '') }}"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-gray-400 small fw-bold text-uppercase ps-1">سعر خاص
                                    (فني/موظف)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500">EGP</span>
                                    <input type="number" step="0.01" name="price_special"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        value="{{ old('price_special', $product->price_special ?? '') }}"
                                        placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Inventory -->
                    <div class="tab-pane fade" id="inventory">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-white-10 pb-3">إعدادات المخزون</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">تنبيه انخفاض
                                    المخزون (Min Level)</label>
                                <input type="number" name="min_stock_level"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('min_stock_level', $product->min_stock ?? 0) }}" placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">كمية إعادة
                                    الطلب</label>
                                <input type="number" name="reorder_quantity"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('reorder_quantity', $product->reorder_quantity ?? 0) }}"
                                    placeholder="0">
                            </div>

                            <div class="col-12">
                                <hr class="border-white-10 my-4">
                            </div>

                            @if(!$isEdit)
                                <div class="col-12">
                                    <div
                                        class="p-4 rounded-3 border border-dashed border-purple-500 border-opacity-30 bg-slate-900 bg-opacity-30">
                                        <h6 class="text-purple-300 fw-bold mb-3"><i class="bi bi-box-seam me-2"></i> رصيد
                                            أول المدة (Initial Stock)</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-gray-400 small">المستودع</label>
                                                <select name="initial_warehouse_id"
                                                    class="form-select form-select-dark text-white">
                                                    <option value="">-- اختر مستودع للإيداع المبدئي --</option>
                                                    @foreach($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-gray-400 small">الكمية الافتتاحية</label>
                                                <input type="number" name="initial_stock"
                                                    class="form-control form-control-dark text-white" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-12">
                                    <div
                                        class="alert bg-blue-500 bg-opacity-10 border border-blue-500 border-opacity-20 text-blue-300 d-flex align-items-center gap-3">
                                        <i class="bi bi-info-circle fs-4"></i>
                                        <div>
                                            لتعديل كميات المخزون الحالية، يرجى استخدام قسم <a
                                                href="{{ route('stock.adjust') }}"
                                                class="text-white text-decoration-underline fw-bold">تسوية المخزون</a>.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. Attributes -->
                    <div class="tab-pane fade" id="attributes">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-white-10 pb-3">المواصفات والأبعاد</h4>
                        <div class="row g-4">

                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الضمان
                                    (شهور)</label>
                                <input type="number" name="warranty_months"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('warranty_months', $product->warranty_months ?? '') }}"
                                    placeholder="12">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الطول
                                    (سم)</label>
                                <input type="number" step="0.1" name="length"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('length', $product->length ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">العرض
                                    (سم)</label>
                                <input type="number" step="0.1" name="width"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('width', $product->width ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الارتفاع
                                    (سم)</label>
                                <input type="number" step="0.1" name="height"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('height', $product->height ?? '') }}">
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">المصنع /
                                    Manufacturer</label>
                                <input type="text" name="manufacturer"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('manufacturer', $product->manufacturer ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">رقم قطعة
                                    المصنع (MPN)</label>
                                <input type="text" name="manufacturer_part_number"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('manufacturer_part_number', $product->manufacturer_part_number ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الوزن
                                    (كجم)</label>
                                <input type="number" step="0.01" name="weight"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('weight', $product->weight ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase ps-1">اللون</label>
                                <input type="text" name="color"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('color', $product->color ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">المقاس /
                                    Size</label>
                                <input type="text" name="size"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('size', $product->size ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">تاريخ
                                    الصلاحية</label>
                                <input type="date" name="expiry_date"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                    value="{{ old('expiry_date', isset($product) && $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 5. Images -->
                    <div class="tab-pane fade" id="images">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-white-10 pb-3">صور المنتج</h4>

                        @if($isEdit && $product->images->count() > 0)
                            <div class="mb-4">
                                <label class="form-label text-gray-400 small mb-3">الصور الحالية</label>
                                <div class="d-flex gap-3 flex-wrap">
                                    @foreach($product->images as $image)
                                        <div class="position-relative border border-white-10 rounded-3 overflow-hidden shadow-sm group-hover-scale"
                                            style="width: 120px; height: 120px;">
                                            <img src="{{ $image->url }}" class="w-100 h-100 object-fit-cover">
                                            @if($image->is_primary)
                                                <span
                                                    class="position-absolute top-0 start-0 badge bg-purple-500 m-2 shadow-sm text-xxs">رئيسية</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Improved Dropzone (Darker, no white box) -->
                        <div
                            class="p-5 border-2 border-dashed border-white-10 rounded-4 text-center bg-slate-900 bg-opacity-30 hover-border-purple transition-all cursor-pointer position-relative">
                            <input type="file" name="images[]"
                                class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" multiple
                                accept="image/*">
                            <div class="d-flex flex-column align-items-center">
                                <div
                                    class="icon-circle-lg bg-purple-500 bg-opacity-10 text-purple-400 mb-3 group-hover-text-purple">
                                    <i class="bi bi-cloud-arrow-up display-6"></i>
                                </div>
                                <h5 class="text-white fw-bold mb-2">اسحب وأفلت الصور هنا</h5>
                                <p class="text-gray-500 small mb-0">أو اضغط لاستعراض الملفات</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Actions -->
            <div class="d-flex justify-content-end align-items-center gap-3 mt-4">
                <a href="{{ route('products.index') }}"
                    class="btn btn-link text-gray-400 text-decoration-none hover-text-white">إلغاء</a>
                <button type="submit"
                    class="btn btn-action-purple px-5 py-3 rounded-pill fw-bold shadow-neon-purple d-flex align-items-center gap-2">
                    <i class="bi bi-save"></i>
                    <span>{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء المنتج' }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    function generateBarcode() {
        const timestamp = Date.now().toString().slice(-8); // Last 8 digits of timestamp
        const randomInfo = Math.floor(Math.random() * 9000) + 1000; // 4 random digits
        const barcode = `800${timestamp}${randomInfo}`; // Simple generation
        document.getElementById('barcodeInput').value = barcode;
    }

    function generateSKU() {
        const timestamp = Date.now().toString().slice(-6); // Last 6 digits
        const randomStr = Math.random().toString(36).substring(2, 5).toUpperCase(); // 3 random chars
        const sku = `PROD-${timestamp}-${randomStr}`;
        document.getElementById('skuInput').value = sku;
    }
</script>

<style>
    .glass-panel {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
        border-radius: 16px;
    }

    /* Tabs */
    .custom-pills .nav-link {
        color: #94a3b8;
        border-radius: 12px;
        transition: all 0.3s;
        border: 1px solid transparent;
    }

    .custom-pills .nav-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .custom-pills .nav-link.active {
        background: linear-gradient(90deg, rgba(168, 85, 247, 0.2), transparent);
        color: #d8b4fe;
        border-left: 3px solid #a855f7;
        border-radius: 4px 12px 12px 4px;
    }

    /* Inputs */
    .form-control-dark,
    .form-select-dark {
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        padding: 0.8rem 1rem;
        border-radius: 12px;
    }

    .form-control-dark:focus,
    .form-select-dark:focus {
        background: rgba(15, 23, 42, 0.8) !important;
    }

    .bg-dark-input {
        background: rgba(15, 23, 42, 0.8) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #94a3b8;
    }

    .focus-ring-purple:focus {
        border-color: #a855f7 !important;
        box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.1) !important;
    }

    .focus-ring-success:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1) !important;
    }

    /* Custom Toggles */
    .custom-toggle {
        position: relative;
        display: flex;
        align-items: center;
    }

    .custom-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-switch {
        position: relative;
        width: 48px;
        height: 26px;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        transition: .3s;
    }

    .toggle-switch:before {
        content: "";
        position: absolute;
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 2px;
        background-color: white;
        border-radius: 50%;
        transition: .3s;
    }

    .custom-toggle input:checked+.toggle-switch {
        background-color: #a855f7;
        border-color: #a855f7;
    }

    .custom-toggle input:checked+.toggle-switch:before {
        transform: translateX(20px);
    }

    /* Button */
    .btn-action-purple {
        background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
        border: none;
        color: white;
        transition: all 0.3s;
    }

    .btn-action-purple:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 30px rgba(168, 85, 247, 0.5);
    }

    .btn-outline-purple {
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #d8b4fe;
        background: transparent;
    }

    .btn-outline-purple:hover {
        background: rgba(168, 85, 247, 0.1);
        color: white;
        border-color: #a855f7;
    }

    /* Misc */
    .text-purple-400 {
        color: #c084fc !important;
    }

    .hover-border-purple:hover {
        border-color: #a855f7 !important;
        background: rgba(168, 85, 247, 0.05) !important;
    }

    .icon-circle-lg {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
</style>