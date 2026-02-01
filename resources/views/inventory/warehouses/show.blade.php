@extends('layouts.app')

@section('title', $warehouse->name . ' - التفاصيل')

@section('content')
    <div class="container-fluid p-0">
        <!-- Hero Header -->
        <div class="position-relative overflow-hidden rounded-bottom-5 mb-5"
            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden">
                <div class="glow-orb bg-purple-500" style="top: -20%; left: 20%; opacity: 0.1;"></div>
                <div class="glow-orb bg-cyan-500" style="bottom: -20%; right: 20%; opacity: 0.1;"></div>
            </div>

            <div class="container py-5 position-relative z-1">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-4 mb-3">
                            <a href="{{ route('warehouses.index') }}"
                                class="btn btn-icon-only btn-glass-back text-gray-400 hover-text-white">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                            class="text-gray-500 text-decoration-none">الرئيسية</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}"
                                            class="text-gray-500 text-decoration-none">المخازن</a></li>
                                    <li class="breadcrumb-item active text-purple-400 fw-bold" aria-current="page">
                                        {{ $warehouse->name }}</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex align-items-center gap-4">
                            <div class="hero-icon-box bg-gradient-to-br from-purple-500 to-indigo-600 shadow-neon-lg">
                                <i class="bi bi-building-fill text-white display-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-3">
                                    <h1 class="display-5 fw-bold text-white mb-2 tracking-wide">{{ $warehouse->name }}</h1>
                                    @if($warehouse->is_default)
                                        <span
                                            class="badge bg-purple-500 bg-opacity-20 text-purple-400 border border-purple-500 border-opacity-30 rounded-pill px-3 py-1">رئيسي</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-4 text-gray-400 mt-2">
                                    <span><i class="bi bi-qr-code me-2"></i>{{ $warehouse->code }}</span>
                                    <span><i
                                            class="bi bi-geo-alt me-2"></i>{{ $warehouse->address ?? 'لا يوجد عنوان' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex gap-3 justify-content-lg-end">
                            <a href="{{ route('warehouses.edit', $warehouse) }}"
                                class="btn btn-glass-warning d-flex align-items-center gap-2 px-4 py-3">
                                <i class="bi bi-pencil-square"></i>
                                <span>تعديل</span>
                            </a>
                            <!-- Delete button (only if no stock) -->
                            @if($warehouse->stocks_count == 0 && !$warehouse->is_default)
                                <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا المستودع؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-glass-danger d-flex align-items-center gap-2 px-4 py-3">
                                        <i class="bi bi-trash"></i>
                                        <span>حذف</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Stats Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">إجمالي قيمة المخزون</p>
                                <h2 class="text-white fw-bold mb-0">{{ number_format($totalValue, 2) }} <span
                                        class="fs-6 text-gray-500">ج.م</span></h2>
                            </div>
                            <div class="icon-circle bg-emerald-500 bg-opacity-10 text-emerald-400">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">عدد الأصناف</p>
                                <h2 class="text-white fw-bold mb-0">{{ $warehouse->stocks_count }}</h2>
                            </div>
                            <div class="icon-circle bg-amber-500 bg-opacity-10 text-amber-400">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="glass-stat-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-gray-400 small text-uppercase fw-bold mb-1">حالة المستودع</p>
                                <h2 class="{{ $warehouse->is_active ? 'text-success' : 'text-danger' }} fw-bold mb-0">
                                    {{ $warehouse->is_active ? 'نشط' : 'معطل' }}</h2>
                            </div>
                            <div
                                class="icon-circle {{ $warehouse->is_active ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $warehouse->is_active ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-activity"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Stock Table -->
                <div class="col-12">
                    <div class="glass-panel overflow-hidden border-top-gradient-purple">
                        <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-white mb-0">محتويات المستودع</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">المنتج</th>
                                        <th>التصنيف</th>
                                        <th>سعر البيع</th>
                                        <th>الكمية</th>
                                        <th>متوسط التكلفة</th>
                                        <th>القيمة الإجمالية</th>
                                        <th class="pe-4">آخر حركة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stocks as $stock)
                                        <tr class="table-row-hover">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-product">
                                                        @if($stock->product && $stock->product->image_url)
                                                            <img src="{{ $stock->product->image_url }}" alt="Product"
                                                                class="rounded-3 w-100 h-100 object-fit-cover">
                                                        @else
                                                            <i class="bi bi-box text-gray-500"></i>
                                                        @endif
                                                    </div>
                                                    <div class="fw-bold text-white">{{ $stock->product->name ?? 'منتج محذوف' }}
                                                    </div>
                                                    <div class="small text-gray-500 font-monospace">
                                                        {{ $stock->product->sku ?? '-' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                            <span class="badge bg-purple-500 bg-opacity-10 text-purple-400 border border-purple-500 border-opacity-20 fw-normal px-3 py-2">
                                                {{ $stock->product->category->name ?? '-' }}
                                            </span>
                                        </td>
                                            <td>
                                                <div class="text-white">
                                                    {{ number_format($stock->product->selling_price, 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-cyan-400 fs-5">{{ $stock->quantity }}</span>
                                                    <span
                                                        class="text-gray-500 small">{{ $stock->product->unit->name ?? '' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-400">{{ number_format($stock->average_cost, 2) }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-emerald-400">
                                                    {{ number_format($stock->quantity * $stock->average_cost, 2) }}
                                                    <small class="text-emerald-500 text-opacity-50"
                                                        style="font-size: 0.7em;">EGP</small>
                                                </div>
                                            </td>
                                            <td class="pe-4">
                                                <div class="small text-gray-400">
                                                    {{ $stock->last_movement_at ? $stock->last_movement_at->diffForHumans() : '-' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="empty-state opacity-50">
                                                    <i class="bi bi-box-seam display-4 text-gray-600 mb-3"></i>
                                                    <p class="text-gray-400">المستودع فارغ حالياً</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $stocks->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styling Reuse + Purple Specifics */
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

        .avatar-product {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
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

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-emerald-400 {
            color: #34d399 !important;
        }

        .text-amber-400 {
            color: #fbbf24 !important;
        }

        .border-top-gradient-purple {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #a855f7, #22d3ee) 1;
        }
    </style>
@endsection