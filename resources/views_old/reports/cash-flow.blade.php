@extends('layouts.app')

@section('title', 'تقرير التدفقات النقدية')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير التدفقات النقدية</h1>
                <p class="text-muted mb-0">Cash Flow Report</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>
                    طباعة
                </button>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cash Flow Report -->
        <div class="row g-4">
            <!-- Operating Activities - Cash In -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-down-circle me-2"></i>التدفقات النقدية الداخلة</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>المبيعات النقدية</td>
                                <td class="text-end fw-bold text-success">{{ number_format($salesCash, 2) }}</td>
                            </tr>
                            <tr>
                                <td>تحصيلات من العملاء</td>
                                <td class="text-end fw-bold text-success">{{ number_format($customerPayments, 2) }}</td>
                            </tr>
                            <tr class="table-success">
                                <td class="fw-bold">إجمالي التدفقات الداخلة</td>
                                <td class="text-end fw-bold">{{ number_format($cashIn, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Operating Activities - Cash Out -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-up-circle me-2"></i>التدفقات النقدية الخارجة</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>مدفوعات للموردين</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($purchasePayments, 2) }}</td>
                            </tr>
                            <tr>
                                <td>المصروفات</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($expenses, 2) }}</td>
                            </tr>
                            <tr class="table-danger">
                                <td class="fw-bold">إجمالي التدفقات الخارجة</td>
                                <td class="text-end fw-bold">{{ number_format($cashOut, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>ملخص التدفق النقدي</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h6 class="text-muted">رصيد افتتاحي</h6>
                                <h4 class="text-primary">{{ number_format($openingBalance, 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">+ تدفقات داخلة</h6>
                                <h4 class="text-success">{{ number_format($cashIn, 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">- تدفقات خارجة</h6>
                                <h4 class="text-danger">{{ number_format($cashOut, 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">= صافي التدفق</h6>
                                <h4 class="{{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($netCashFlow, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {

                .btn,
                form,
                .sidebar,
                .navbar {
                    display: none !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }

                .container-fluid {
                    padding: 0 !important;
                }
            }
        </style>
    @endpush
@endsection