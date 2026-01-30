@extends('layouts.app')

@section('title', 'التقرير اليومي للمبيعات - POS')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">التقرير اليومي للمبيعات</h1>
                <p class="text-muted small mb-0"><i class="bi bi-calendar3 me-1"></i> تاريخ اليوم:
                    {{ now()->format('Y-m-d') }}</p>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline-primary border-2">
                    <i class="bi bi-printer me-2"></i> طباعة التقرير
                </button>
                <a href="{{ route('pos.index') }}" class="btn btn-primary px-4 border-2">
                    <i class="bi bi-cart3 me-2"></i> العودة للكاشير
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-white"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="opacity-75 mb-1 small">إجمالي المبيعات</p>
                                <h2 class="mb-0 fw-bold">{{ number_format($summary['total_sales'], 2) }} <small
                                        class="fs-6">ج.م</small></h2>
                            </div>
                            <div class="fs-1 opacity-25"><i class="bi bi-currency-exchange"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-white"
                    style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="opacity-75 mb-1 small">عدد الفواتير</p>
                                <h2 class="mb-0 fw-bold">{{ $summary['sales_count'] }}</h2>
                            </div>
                            <div class="fs-1 opacity-25"><i class="bi bi-receipt"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-white"
                    style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="opacity-75 mb-1 small text-dark">إجمالي الخصومات</p>
                                <h2 class="mb-0 fw-bold text-dark">{{ number_format($summary['total_discounts'], 2) }}
                                    <small class="fs-6">ج.م</small></h2>
                            </div>
                            <div class="fs-1 opacity-25 text-dark"><i class="bi bi-percent"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-white"
                    style="background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="opacity-75 mb-1 small">رصيد الكاش الحالي</p>
                                <h2 class="mb-0 fw-bold">{{ number_format($summary['cash_sales'], 2) }} <small
                                        class="fs-6">ج.م</small></h2>
                            </div>
                            <div class="fs-1 opacity-25"><i class="bi bi-wallet2"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Payment Methods Breakdown -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>توزيع طرق الدفع
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mt-2">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success-subtle text-success p-2 rounded-circle me-3"><i
                                            class="bi bi-cash"></i></span>
                                    <span>نقدي</span>
                                </div>
                                <span class="fw-bold">{{ number_format($summary['cash_sales'], 2) }} ج.م</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle me-3"><i
                                            class="bi bi-credit-card"></i></span>
                                    <span>بطاقة</span>
                                </div>
                                <span class="fw-bold">{{ number_format($summary['card_sales'], 2) }} ج.م</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle me-3"><i
                                            class="bi bi-person-badge"></i></span>
                                    <span>آجل</span>
                                </div>
                                <span class="fw-bold">{{ number_format($summary['credit_sales'], 2) }} ج.م</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Shift Info -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-info"></i>تفاصيل الوردية
                            الحالية</h5>
                    </div>
                    <div class="card-body">
                        @if($summary['shift'])
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded bg-light">
                                        <p class="text-muted small mb-1">الموظف المسؤول</p>
                                        <p class="fw-bold mb-0">{{ $summary['shift']->user->name ?? 'غير محدد' }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded bg-light">
                                        <p class="text-muted small mb-1">وقت البدء</p>
                                        <p class="fw-bold mb-0 text-ltr">{{ $summary['shift']->opened_at->format('Y-m-d H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded bg-light">
                                        <p class="text-muted small mb-1">الرصيد الافتتاحي</p>
                                        <p class="fw-bold mb-0 text-success">
                                            {{ number_format($summary['shift']->opening_cash, 2) }} ج.م</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded bg-warning-subtle">
                                        <p class="text-muted small mb-1">إجمالي الكاش المتوقع</p>
                                        <p class="fw-bold mb-0 text-dark">
                                            {{ number_format($summary['shift']->opening_cash + $summary['cash_sales'], 2) }} ج.م
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3"></i>
                                <p class="text-muted">لا توجد وردية مفتوحة حالياً</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {

            .navbar,
            .btn-group,
            .sidebar {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .container-fluid {
                padding: 0 !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }

            .text-ltr {
                direction: ltr;
                display: inline-block;
            }
        }

        .text-ltr {
            direction: ltr;
            display: inline-block;
        }
    </style>
@endsection