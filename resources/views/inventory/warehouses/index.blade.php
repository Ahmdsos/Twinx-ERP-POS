@extends('layouts.app')

@section('title', 'إدارة المخازن')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-purple shadow-neon">
                    <i class="bi bi-building-fill fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">إدارة المخازن</h2>
                    <p class="mb-0 text-gray-400 small">التحكم في مستودعات الشركة وتوزيع المخزون</p>
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('warehouses.import.form') }}"
                    class="btn btn-dark-glass d-flex align-items-center gap-2 border-0 text-gray-300 hover-text-white">
                    <i class="bi bi-cloud-upload"></i>
                    <span class="d-none d-md-block">استيراد</span>
                </a>
                <a href="{{ route('warehouses.create') }}"
                    class="btn btn-action-purple d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-plus-lg"></i>
                    <span class="fw-bold">إضافة مستودع</span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card glass-panel position-relative overflow-hidden h-100 p-4">
                    <div class="z-1 position-relative">
                        <p class="text-cyan-400 small fw-bold text-uppercase mb-2 tracking-wider">إجمالي المستودعات</p>
                        <h1 class="fw-bold text-white mb-0 display-4">{{ $warehouses->count() }}</h1>
                        <p class="text-gray-500 small mt-2">موقع تخزين مسجل</p>
                    </div>
                    <i
                        class="bi bi-geo-alt-fill position-absolute bottom-0 end-0 display-1 text-white opacity-5 transform-scale-150"></i>
                    <div class="glow-orb bg-cyan-500"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card glass-panel position-relative overflow-hidden h-100 p-4">
                    <div class="z-1 position-relative">
                        <p class="text-emerald-400 small fw-bold text-uppercase mb-2 tracking-wider">قيمة المخزون الكلية</p>
                        <h1 class="fw-bold text-white mb-0 display-4">{{ number_format($totalValue, 0) }}</h1>
                        <p class="text-gray-500 small mt-2">جنيه مصري (EGP)</p>
                    </div>
                    <i
                        class="bi bi-cash-stack position-absolute bottom-0 end-0 display-1 text-white opacity-5 transform-scale-150"></i>
                    <div class="glow-orb bg-emerald-500"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card glass-panel position-relative overflow-hidden h-100 p-4">
                    <div class="z-1 position-relative">
                        <p class="text-amber-400 small fw-bold text-uppercase mb-2 tracking-wider">عدد الأصناف</p>
                        <h1 class="fw-bold text-white mb-0 display-4">{{ number_format($totalItems) }}</h1>
                        <p class="text-gray-500 small mt-2">صنف مخزني (SKUs)</p>
                    </div>
                    <i
                        class="bi bi-box-seam-fill position-absolute bottom-0 end-0 display-1 text-white opacity-5 transform-scale-150"></i>
                    <div class="glow-orb bg-amber-500"></div>
                </div>
            </div>
        </div>

        <!-- Warehouses Grid -->
        <h5 class="fw-bold text-white mb-4 ps-2 border-start border-4 border-purple border-opacity-50">قائمة المستودعات</h5>

        <div class="row g-4">
            @forelse($warehouses as $warehouse)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-panel position-relative overflow-hidden h-100 transition-all hover-transform-y">
                        @if($warehouse->is_default)
                            <div class="position-absolute top-0 end-0 m-3 z-2">
                                <span class="badge bg-purple-500 text-white shadow-neon-sm fw-normal px-2 py-1">الرئيسي</span>
                            </div>
                        @endif

                        <div class="p-4 border-bottom border-white-10">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-square bg-white bg-opacity-5 text-purple-400 border border-white-10">
                                    <i class="bi bi-shop fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-white mb-1">
                                        <a href="{{ route('warehouses.show', $warehouse) }}"
                                            class="text-decoration-none text-white hover-text-purple">{{ $warehouse->name }}</a>
                                    </h5>
                                    <div class="small text-gray-500 font-monospace">{{ $warehouse->code }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3 text-gray-400 small">
                                    <i class="bi bi-geo-alt text-cyan-400"></i>
                                    <span class="text-truncate">{{ $warehouse->address ?? 'لا يوجد عنوان' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3 text-gray-400 small">
                                    <i class="bi bi-telephone text-emerald-400"></i>
                                    <span>{{ $warehouse->phone ?? '-' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3 text-gray-400 small">
                                    <i class="bi bi-person-circle text-amber-400"></i>
                                    <span>{{ $warehouse->manager ?? 'غير محدد' }}</span>
                                </div>
                            </div>

                        <div class="bg-slate-900 bg-opacity-50 rounded-3 p-3 mb-4 border border-white-5">
                                <div class="row text-center">
                                    <div class="col-6 border-end border-white-10">
                                        <p class="text-gray-500 x-small text-uppercase mb-1">قيمة الممتلكات</p>
                                        <div class="fw-bold text-white">{{ number_format($warehouse->stock_value, 0) }}</div>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-gray-500 x-small text-uppercase mb-1">عدد الأصناف</p>
                                        <div class="fw-bold text-white">{{ $warehouse->stocks_count }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="pulse-dot {{ $warehouse->is_active ? 'bg-success' : 'bg-danger' }}"></div>
                                    <span class="small {{ $warehouse->is_active ? 'text-success' : 'text-danger' }}">
                                        {{ $warehouse->is_active ? 'نشط' : 'معطل' }}
                                    </span>
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-icon-only text-gray-400 hover-text-white" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul
                                        class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-neon-sm border-white-10">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                                href="{{ route('warehouses.show', $warehouse) }}">
                                                <i class="bi bi-eye text-cyan-400"></i> عرض التفاصيل
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                                href="{{ route('warehouses.edit', $warehouse) }}">
                                                <i class="bi bi-pencil text-warning"></i> تعديل
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider border-white-10">
                                        </li>
                                        <li>
                                            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST"
                                                onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                    <i class="bi bi-trash"></i> حذف
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="glass-panel text-center py-5">
                        <div class="icon mb-3 text-gray-600"><i class="bi bi-building-slash display-4"></i></div>
                        <h5 class="text-gray-400">لا توجد مستودعات</h5>
                        <p class="text-gray-600 mb-0">قم بإضافة أول مستودع لبدء إدارة مخزونك</p>
                        <a href="{{ route('warehouses.create') }}" class="btn btn-outline-purple btn-sm mt-3">إضافة مستودع</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        /* Premium Theme Variables (Purple Variant) */
        :root {
            --bg-dark: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --purple-glow: 0 0 20px rgba(168, 85, 247, 0.3);
        }

        /* Core Layout */
        .btn-dark-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            transition: all 0.3s;
        }

        .btn-dark-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
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

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .hover-transform-y:hover {
            transform: translateY(-5px);
            border-color: rgba(168, 85, 247, 0.3);
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
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2) 0%, rgba(126, 34, 206, 0.2) 100%);
            border-color: rgba(168, 85, 247, 0.3);
        }

        /* Stats Cards */
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .glow-orb {
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.15;
            top: -50px;
            right: -50px;
        }

        .transform-scale-150 {
            transform: scale(1.5) rotate(-10deg);
        }

        /* Utilities */
        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .text-emerald-400 {
            color: #34d399 !important;
        }

        .text-amber-400 {
            color: #fbbf24 !important;
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-gray-300 {
            color: #cbd5e1 !important;
        }

        .text-gray-400 {
            color: #94a3b8 !important;
        }

        .text-gray-500 {
            color: #64748b !important;
        }

        .bg-purple-500 {
            background-color: #a855f7 !important;
        }

        .icon-square {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .border-purple {
            border-color: #a855f7 !important;
        }

        .hover-text-purple:hover {
            color: #c084fc !important;
        }

        .x-small {
            font-size: 0.7rem;
            letter-spacing: 0.05em;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            box-shadow: 0 0 0 rgba(255, 255, 255, 0.4);
            animation: pulse 2s infinite;
        }
    </style>
@endsection