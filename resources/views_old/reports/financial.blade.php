@extends('layouts.app')

@section('title', 'التقارير المالية - Twinx ERP')
@section('page-title', 'التقارير المالية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير المالية</li>
@endsection

@section('content')
    <div class="row g-4">
        <!-- Trial Balance -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-journal-text fs-1 text-primary mb-3 d-block"></i>
                    <h5>ميزان المراجعة</h5>
                    <p class="text-muted small">عرض أرصدة جميع الحسابات</p>
                    <a href="/api/v1/reports/financial/trial-balance" class="btn btn-outline-primary btn-sm"
                        target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- P&L -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-graph-up fs-1 text-success mb-3 d-block"></i>
                    <h5>قائمة الدخل</h5>
                    <p class="text-muted small">الإيرادات والمصروفات وصافي الربح</p>
                    <a href="/api/v1/reports/financial/profit-loss" class="btn btn-outline-success btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Balance Sheet -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bar-chart-line fs-1 text-info mb-3 d-block"></i>
                    <h5>الميزانية العمومية</h5>
                    <p class="text-muted small">الأصول والخصوم وحقوق الملكية</p>
                    <a href="/api/v1/reports/financial/balance-sheet" class="btn btn-outline-info btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- AR Aging -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-wallet2 fs-1 text-warning mb-3 d-block"></i>
                    <h5>تقادم الذمم المدينة (AR)</h5>
                    <p class="text-muted small">تحليل مستحقات العملاء حسب العمر</p>
                    <a href="/api/v1/reports/aging/ar" class="btn btn-outline-warning btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- AP Aging -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-credit-card fs-1 text-danger mb-3 d-block"></i>
                    <h5>تقادم الذمم الدائنة (AP)</h5>
                    <p class="text-muted small">تحليل مستحقات الموردين حسب العمر</p>
                    <a href="/api/v1/reports/aging/ap" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>تحميل JSON
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection