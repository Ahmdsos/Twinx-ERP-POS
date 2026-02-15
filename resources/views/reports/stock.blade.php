@extends('layouts.app')

@section('title', __('Inventory Reports'))

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-heading mb-2">
                    <i class="bi bi-box-seam text-info me-2"></i>{{ __('Inventory Reports') }}</h2>
                <p class="text-gray-400 mb-0">تحليلات حركة المخزون وتقييم الأرصدة</p>
            </div>
             <div class="d-flex gap-2">
                 <button class="btn btn-glass-outline rounded-pill px-4">
                    <i class="bi bi-cloud-download me-2"></i> تصدير Excel
                </button>
            </div>
        </div>

        <div class="row g-4">
             <div class="col-md-3">
                <div class="glass-panel p-4 text-center h-100 hover-scale cursor-pointer position-relative overflow-hidden">
                    <div class="absolute-glow top-0 end-0 bg-info/20"></div>
                    <div class="icon-box bg-info/20 text-info rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-list-check fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-heading mb-1">جرد المخزون</h5>
                    <p class="text-gray-500 x-small mb-0">الأرصدة الحالية في كل المخازن</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="glass-panel p-4 text-center h-100 hover-scale cursor-pointer position-relative overflow-hidden">
                    <div class="absolute-glow top-0 end-0 bg-orange-500/20"></div>
                     <div class="icon-box bg-orange-500/20 text-orange-400 rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-left-right fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-heading mb-1">حركة الأصناف</h5>
                    <p class="text-gray-500 x-small mb-0">سجل حركات الوارد والمنصرف</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-panel p-4 text-center h-100 hover-scale cursor-pointer position-relative overflow-hidden">
                    <div class="absolute-glow top-0 end-0 bg-red-500/20"></div>
                     <div class="icon-box bg-red-500/20 text-red-400 rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-heading mb-1">{{ __('Low Stock') }}</h5>
                    <p class="text-gray-500 x-small mb-0">منتجات وصلت لحد الطلب</p>
                </div>
            </div>

             <div class="col-md-3">
                <div class="glass-panel p-4 text-center h-100 hover-scale cursor-pointer position-relative overflow-hidden">
                    <div class="absolute-glow top-0 end-0 bg-green-500/20"></div>
                     <div class="icon-box bg-green-500/20 text-green-400 rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-heading mb-1">{{ __('Stock Valuation') }}</h5>
                    <p class="text-gray-500 x-small mb-0">قيمة المخزون الحالية</p>
                </div>
            </div>
        </div>

        <!-- Placeholder for content -->
        <div class="glass-panel p-5 mt-5 text-center">
            <div class="opacity-50">
                <i class="bi bi-bar-chart-steps display-1 text-info mb-3"></i>
                <h4 class="text-heading mt-3">جاري بناء تقارير المخزون...</h4>
            </div>
        </div>
    </div>

    <style>
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .absolute-glow {
            position: absolute;
            width: 100px; height: 100px;
            filter: blur(40px);
            opacity: 0.5;
            pointer-events: none;
        }
        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
            transition: all 0.3s;
        }
        .btn-glass-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-primary);
            color: var(--text-primary);
        }
        .hover-scale:hover {
            transform: translateY(-5px);
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
    </style>
@endsection
