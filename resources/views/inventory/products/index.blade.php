@extends('layouts.app')

@section('title', 'قائمة المنتجات')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-purple shadow-neon">
                    <i class="bi bi-box-seam fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">إدارة المنتجات</h2>
                    <p class="mb-0 text-gray-400 small">قاعدة بيانات الأصناف والمخزون</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="/inventory_guide.html" target="_blank"
                    class="btn btn-purple-glass d-flex align-items-center gap-2 shadow-lg" title="دليل الاستخدام">
                    <i class="bi bi-question-circle"></i>
                    <span class="fw-bold d-none d-md-inline">دليل الاستخدام</span>
                </a>
                <div class="dropdown">
                    <button class="btn btn-purple-glass d-flex align-items-center gap-2 shadow-lg dropdown-toggle"
                        type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i>
                        <span class="fw-bold d-none d-md-inline">تصدير</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark bg-slate-900 border-white-10 shadow-neon"
                        aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('products.export', ['format' => 'xlsx']) }}">
                                <i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('products.export', ['format' => 'json']) }}">
                                <i class="bi bi-filetype-json text-warning"></i> JSON (Unified)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('products.export', ['format' => 'csv']) }}">
                                <i class="bi bi-file-earmark-code text-info"></i> Pro Editor (.csv)
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn btn-purple-glass d-flex align-items-center gap-2 shadow-lg"
                    data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i>
                    <span class="fw-bold d-none d-md-inline">استيراد</span>
                </button>
                <a href="{{ route('products.create') }}"
                    class="btn btn-action-purple d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-plus-lg"></i>
                    <span class="fw-bold">إضافة منتج جديد</span>
                </a>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-slate-900 border border-purple-500 border-opacity-20 shadow-neon">
                    <div class="modal-header border-bottom border-white-10">
                        <h5 class="modal-title text-white fw-bold">
                            <i class="bi bi-file-earmark-spreadsheet text-purple-400 me-2"></i> استيراد المنتجات
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info bg-opacity-10 border-info border-opacity-20 text-info small">
                                <i class="bi bi-info-circle me-1"></i>
                                يمكنك رفع ملف <strong>Excel/CSV</strong> للمنتجات فقط، أو ملف <strong>JSON</strong> لتحديث
                                الـ 5 صفحات معاً (أقسام، ماركات، وحدات، مخازن، منتجات).
                            </div>
                            <div class="mb-3">
                                <label for="importFile" class="form-label text-gray-300">ملف التحديث
                                    (Excel/CSV/JSON)</label>
                                <input class="form-control form-control-dark" type="file" id="importFile" name="file"
                                    required accept=".xlsx,.xls,.csv,.json">
                            </div>
                        </div>
                        <div class="modal-footer border-top border-white-10">
                            <button type="button" class="btn btn-ghost text-gray-400" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-action-purple">
                                <i class="bi bi-upload me-1"></i> بدء الاستيراد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filters Section (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-white-5 rounded-4 p-4 mb-5">
            <form action="{{ route('products.index') }}" method="GET" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">بحث سريع</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                            value="{{ request('search') }}" placeholder="الاسم، SKU، أو الباركود...">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-3">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">التصنيف</label>
                    <select name="category_id"
                        class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div class="col-md-3">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">الماركة</label>
                    <select name="brand_id" class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter (Neon Toggle) -->
                <div class="col-md-auto d-flex align-items-end pb-1">
                    <label
                        class="custom-toggle d-flex align-items-center gap-3 cursor-pointer p-2 rounded-3 hover-bg-white-5 transition-all">
                        <input type="checkbox" name="active_only" value="1" {{ request()->boolean('active_only', true) ? 'checked' : '' }}>
                        <span class="toggle-switch"></span>
                        <span class="text-white small fw-bold">النشط فقط</span>
                    </label>
                </div>

                <!-- Stock Status -->
                <div class="col-md-2">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">حالة المخزون</label>
                    <select name="stock_status"
                        class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>متوفر (>0)
                        </option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>نفد (0)
                        </option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>نواقص (Low)
                        </option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="col-md-2">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">ترتيب حسب</label>
                    <select name="sort_by" class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>الاسم</option>
                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>الرقم (ID)</option>
                        <option value="sku" {{ request('sort_by') == 'sku' ? 'selected' : '' }}>SKU</option>
                        <option value="barcode" {{ request('sort_by') == 'barcode' ? 'selected' : '' }}>الباركود</option>
                        <option value="selling_price" {{ request('sort_by') == 'selling_price' ? 'selected' : '' }}>سعر البيع
                        </option>
                        <option value="total_stock_qty" {{ request('sort_by') == 'total_stock_qty' ? 'selected' : '' }}>الكمية
                        </option>
                    </select>
                </div>

                <!-- Sort Dir -->
                <div class="col-md-1">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">الاتجاه</label>
                    <select name="sort_dir" class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                        <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="col-md d-flex align-items-end">
                    <button type="submit" class="btn btn-purple-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i> تصفية
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'brand_id', 'stock_status', 'sort_by']))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-light ms-2" title="مسح الفلاتر">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-purple">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th style="width: 60px;">IMG</th>
                            <th>تفاصيل المنتج</th>
                            <th>التصنيف / النوع</th>
                            <th>الماركة</th>
                            <th>الأسعار (بيع / شراء)</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 text-gray-500 font-monospace">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="avatar-product-md">
                                        @if($product->primary_image_url)
                                            <img src="{{ $product->primary_image_url }}" alt="Prod"
                                                class="w-100 h-100 object-fit-cover rounded-3">
                                        @else
                                            <i class="bi bi-image text-gray-600 fs-5"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="text-white fw-bold mb-1">{{ $product->name }}</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-gray-400 x-small font-monospace px-0">{{ $product->sku }}</span>
                                            @if($product->barcode)
                                                <span class="text-gray-600 x-small"><i
                                                        class="bi bi-upc me-1"></i>{{ $product->barcode }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($product->category)
                                        <span
                                            class="badge bg-purple-500 bg-opacity-10 text-white border border-purple-500 border-opacity-20 fw-normal">
                                            {{ $product->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-600 small">-</span>
                                    @endif
                                    <div class="text-gray-500 x-small mt-1">{{ $product->type->label() }}</div>
                                </td>
                                <td>
                                    @if($product->brand)
                                        <span class="text-white small fw-bold">{{ $product->brand->name }}</span>
                                    @else
                                        <span class="text-gray-600 small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-success fw-bold">{{ number_format($product->selling_price, 2) }}
                                            <small class="text-success text-opacity-50">EGP</small></span>
                                        <span class="text-gray-500 x-small">Cost:
                                            {{ number_format($product->cost_price, 2) }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $stock = $product->total_stock;
                                        $stockClass = $stock <= 0 ? 'text-danger' :
                                            ($stock <= $product->reorder_level ? 'text-warning' : 'text-cyan-400');
                                    @endphp
                                    <div class="{{ $stockClass }} fw-bold fs-6">
                                        {{ number_format($stock, 2) }}
                                        <span class="text-gray-500 small fw-normal">{{ $product->unit->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="indicator-dot bg-success shadow-neon-sm"></span>
                                            <span class="text-gray-300 small">نشط</span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="indicator-dot bg-danger"></span>
                                            <span class="text-gray-500 small">معطل</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-icon-glass"
                                            title="عرض التفاصيل">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-icon-glass text-blue-400" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @if($stock <= 0)
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                                class="d-inline" data-confirm="هل أنت متأكد من حذف هذا المنتج؟">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon-glass text-danger" title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state opacity-50">
                                        <i class="bi bi-box-seam display-4 text-gray-600 mb-3"></i>
                                        <p class="text-gray-400">لا توجد منتجات مسجلة، ابدأ بإضافة أول منتج.</p>
                                        <a href="{{ route('products.create') }}"
                                            class="btn btn-sm btn-outline-purple mt-2">إضافة منتج</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-top border-white-10">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <style>
        /* Scoped Styles for Products Index (Purple Theme) */
        :root {
            --bg-dark: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --purple-glow: 0 0 20px rgba(168, 85, 247, 0.3);
        }

        .icon-box {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .bg-gradient-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2) 0%, rgba(147, 51, 234, 0.2) 100%);
            border-color: rgba(168, 85, 247, 0.3);
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            border: none;
            color: white;
            transition: all 0.3s;
            box-shadow: var(--purple-glow);
        }

        .btn-action-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.5);
        }

        .btn-purple-glass {
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid rgba(168, 85, 247, 0.2);
            color: #d8b4fe;
            transition: all 0.3s;
        }

        .btn-purple-glass:hover {
            background: rgba(168, 85, 247, 0.2);
            color: white;
            transform: translateY(-1px);
        }

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 16px;
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 0.6rem 1rem;
        }

        .form-control-dark:focus,
        .form-select-dark:focus {
            border-color: #a855f7 !important;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--glass-border);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            padding: 1rem;
        }

        .table-dark-custom td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .table-row-hover:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .avatar-product-md {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-cytan-400 {
            color: #22d3ee !important;
        }

        .bg-purple-500 {
            background-color: #a855f7 !important;
        }

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: scale(1.05);
        }

        .border-top-gradient-purple {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #a855f7, #c084fc) 1;
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
    </style>
@endsection