@extends('layouts.app')

@section('title', 'تقارير المخزون - Twinx ERP')
@section('page-title', 'تقارير المخزون')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تقارير المخزون</li>
@endsection

@section('content')
    <div class="row g-4">
        <!-- Stock Valuation -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-box-seam fs-1 text-primary mb-3 d-block"></i>
                    <h5>تقييم المخزون</h5>
                    <p class="text-muted small">إجمالي قيمة المخزون لكل منتج</p>
                    <a href="/api/v1/reports/stock/valuation" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3 d-block"></i>
                    <h5>المخزون المنخفض</h5>
                    <p class="text-muted small">المنتجات التي تحتاج إعادة طلب</p>
                    <a href="/api/v1/reports/stock/low-stock" class="btn btn-outline-warning btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Movements -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-left-right fs-1 text-info mb-3 d-block"></i>
                    <h5>حركات المخزون</h5>
                    <p class="text-muted small">سجل جميع حركات الدخول والخروج</p>
                    <a href="/api/v1/reports/stock/movements" class="btn btn-outline-info btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- By Warehouse -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-building fs-1 text-success mb-3 d-block"></i>
                    <h5>المخزون حسب المستودع</h5>
                    <p class="text-muted small">توزيع المخزون على المستودعات</p>
                    <a href="/api/v1/reports/stock/by-warehouse" class="btn btn-outline-success btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection