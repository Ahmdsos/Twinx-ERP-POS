@extends('layouts.app')

@section('title', $category->name . ' - التفاصيل')

@section('content')
    <div class="container-fluid p-0">
        <!-- Hero Header -->
        <div class="position-relative overflow-hidden rounded-bottom-5 mb-5"
            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden">
                <div class="glow-orb bg-cyan-500" style="top: -20%; left: 20%; opacity: 0.1;"></div>
                <div class="glow-orb bg-purple-500" style="bottom: -20%; right: 20%; opacity: 0.1;"></div>
            </div>

            <div class="container py-5 position-relative z-1">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-4 mb-3">
                            <a href="{{ route('categories.index') }}"
                                class="btn btn-icon-only btn-glass-back text-gray-400 hover-text-white">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                            class="text-gray-500 text-decoration-none">الرئيسية</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}"
                                            class="text-gray-500 text-decoration-none">التصنيفات</a></li>
                                    @if($category->parent)
                                        <li class="breadcrumb-item"><a href="{{ route('categories.show', $category->parent) }}"
                                                class="text-gray-500 text-decoration-none">{{ $category->parent->name }}</a>
                                        </li>
                                    @endif
                                    <li class="breadcrumb-item active text-cyan-400 fw-bold" aria-current="page">
                                        {{ $category->name }}</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex align-items-center gap-4">
                            <div class="hero-icon-box bg-gradient-to-br from-cyan-500 to-blue-600 shadow-neon-lg">
                                <i
                                    class="bi {{ $category->parent_id ? 'bi-diagram-2' : 'bi-diagram-3-fill' }} text-white display-4"></i>
                            </div>
                            <div>
                                <h1 class="display-5 fw-bold text-white mb-2 tracking-wide">{{ $category->name }}</h1>
                                <p class="text-gray-400 mb-0 fs-5 max-w-2xl">
                                    {{ $category->description ?? 'لا يوجد وصف متاح لهذا التصنيف.' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex gap-3 justify-content-lg-end">
                            <a href="{{ route('categories.edit', $category) }}"
                                class="btn btn-glass-warning d-flex align-items-center gap-2 px-4 py-3">
                                <i class="bi bi-pencil-square"></i>
                                <span>تعديل</span>
                            </a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                data-confirm="هل أنت متأكد من حذف هذا التصنيف؟">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-glass-danger d-flex align-items-center gap-2 px-4 py-3">
                                    <i class="bi bi-trash"></i>
                                    <span>حذف</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Stats Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">المنتجات</p>
                                <h2 class="text-white fw-bold mb-0">{{ $category->products->count() }}</h2>
                            </div>
                            <div class="icon-circle bg-cyan-500 bg-opacity-10 text-cyan-400">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                        <div class="progress bg-gray-700" style="height: 4px;">
                            <div class="progress-bar bg-cyan-500" style="width: 75%"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">إجمالي المخزون</p>
                                <h2 class="text-white fw-bold mb-0">
                                    {{ $category->products->sum(fn($p) => $p->total_stock) }}</h2>
                            </div>
                            <div class="icon-circle bg-purple-500 bg-opacity-10 text-purple-400">
                                <i class="bi bi-layers-fill"></i>
                            </div>
                        </div>
                        <div class="progress bg-gray-700" style="height: 4px;">
                            <div class="progress-bar bg-purple-500" style="width: 60%"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">قيمة المخزون</p>
                                <h2 class="text-white fw-bold mb-0">
                                    {{ number_format($category->products->sum(fn($p) => $p->total_stock * $p->cost_price), 0) }}
                                    <span class="fs-6 text-gray-500">ج.م</span></h2>
                            </div>
                            <div class="icon-circle bg-emerald-500 bg-opacity-10 text-emerald-400">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="progress bg-gray-700" style="height: 4px;">
                            <div class="progress-bar bg-emerald-500" style="width: 45%"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">الحالة</p>
                                <h2 class="{{ $category->is_active ? 'text-success' : 'text-danger' }} fw-bold mb-0">
                                    {{ $category->is_active ? 'نشط' : 'معطل' }}</h2>
                            </div>
                            <div
                                class="icon-circle {{ $category->is_active ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $category->is_active ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-activity"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 small text-gray-400">
                            <span
                                class="d-inline-block rounded-circle {{ $category->is_active ? 'bg-success' : 'bg-danger' }}"
                                style="width: 6px; height: 6px;"></span>
                            {{ $category->is_active ? 'ظاهر في النظام' : 'مخفي من النظام' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Subcategories List -->
                @if($category->children->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="text-white fw-bold"><i class="bi bi-diagram-2 me-2 text-cyan-400"></i>التصنيفات الفرعية
                            </h5>
                        </div>
                        <div class="row g-3">
                            @foreach($category->children as $child)
                                <div class="col-md-3">
                                    <a href="{{ route('categories.show', $child) }}"
                                        class="glass-panel p-3 d-flex align-items-center gap-3 text-decoration-none hover-scale transition-all">
                                        <div class="icon-square bg-white bg-opacity-5 text-gray-300">
                                            <i class="bi bi-folder"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-1 fw-bold">{{ $child->name }}</h6>
                                            <span class="text-gray-500 small">{{ $child->products->count() }} منتج</span>
                                        </div>
                                        <i class="bi bi-chevron-left ms-auto text-gray-600"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Products Table -->
                <div class="col-12">
                    <div class="glass-panel overflow-hidden border-top-gradient">
                        <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-white mb-0">قائمة المنتجات المرتبطة</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">اسم المنتج / SKU</th>
                                        <th>السعر (بيع)</th>
                                        <th>السعر (تكلفة)</th>
                                        <th>المخزون الحالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($category->products as $product)
                                        <tr class="table-row-hover">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-product">
                                                        @if($product->image_url)
                                                            <img src="{{ $product->image_url }}" alt="Product"
                                                                class="rounded-3 w-100 h-100 object-fit-cover">
                                                        @else
                                                            <i class="bi bi-box-seam text-gray-500"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <a href="#"
                                                            class="fw-bold text-white text-decoration-none hover-glow">{{ $product->name }}</a>
                                                        <div class="small text-gray-500 font-monospace">
                                                            {{ $product->sku ?? 'NO-SKU' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success fs-6">
                                                    {{ number_format($product->selling_price, 2) }}
                                                    <small class="text-success text-opacity-50"
                                                        style="font-size: 0.7em;">EGP</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-gray-400">
                                                    {{ number_format($product->cost_price, 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column" style="width: 120px;">
                                                    <div class="d-flex justify-content-between small mb-1">
                                                        <span class="text-white fw-bold">{{ $product->total_stock }}</span>
                                                        <span class="text-gray-500">{{ $product->unit->name ?? 'قطعة' }}</span>
                                                    </div>
                                                    <div class="progress bg-gray-700" style="height: 4px;">
                                                        <div class="progress-bar {{ $product->total_stock <= $product->reorder_level ? 'bg-danger' : 'bg-cyan-500' }}"
                                                            style="width: {{ min(($product->total_stock / max($product->max_stock ?: 100, 1)) * 100, 100) }}%">
                                                        </div>
                                                    </div>
                                                    @if($product->total_stock <= $product->reorder_level)
                                                        <span
                                                            class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-20 mt-1"
                                                            style="font-size: 0.65rem;">منخفض</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="empty-state opacity-50">
                                                    <i class="bi bi-box-seam display-4 text-gray-600 mb-3"></i>
                                                    <p class="text-gray-400">لا توجد منتجات في هذا التصنيف حالياً</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styling Reuse from Index + Specifics */
        :root {
            --bg-dark: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        .hero-icon-box {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-glass-back {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn-glass-back:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
            color: white !important;
        }

        .btn-glass-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
            transition: all 0.3s;
        }

        .btn-glass-warning:hover {
            background: rgba(245, 158, 11, 0.2);
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-glass-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: all 0.3s;
        }

        .btn-glass-danger:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #fff;
            transform: translateY(-2px);
        }

        .glass-stat-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 16px;
        }

        .hover-scale:hover {
            transform: scale(1.02);
            background: rgba(255, 255, 255, 0.08);
        }

        .icon-square {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-product {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-outline-cyber {
            border: 1px solid rgba(34, 211, 238, 0.3);
            color: #22d3ee;
        }

        .btn-outline-cyber:hover {
            background: rgba(34, 211, 238, 0.1);
            color: #fff;
            border-color: #22d3ee;
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
            padding: 1.25rem 1rem;
            border-bottom-width: 0;
        }

        .table-dark-custom td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .table-row-hover:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .bg-cyan-500 {
            background-color: #06b6d4 !important;
        }

        .hover-text-cyan:hover {
            color: #22d3ee !important;
        }

        .border-top-gradient {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #0ea5e9, #8b5cf6) 1;
        }
    </style>
@endsection