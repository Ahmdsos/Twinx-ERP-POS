@extends('layouts.app')

@section('title', 'تقرير الوردية - Twinx ERP')
@section('page-title', 'تقرير الوردية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pos.index') }}">نقطة البيع</a></li>
    <li class="breadcrumb-item active">تقرير الوردية</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>تقرير الوردية</h5>
                    <span class="badge bg-{{ $shift->status === 'open' ? 'success' : 'secondary' }} fs-6">
                        {{ $shift->status === 'open' ? 'مفتوحة' : 'مغلقة' }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Shift Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">الموظف</td>
                                    <td class="fw-bold">{{ $shift->user->name ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">وقت الفتح</td>
                                    <td>{{ $shift->opened_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">وقت الإغلاق</td>
                                    <td>{{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">عدد الفواتير</td>
                                    <td class="fw-bold">{{ $shift->total_sales }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">إجمالي المبيعات</td>
                                    <td class="fw-bold text-success">{{ number_format($shift->total_amount, 2) }} ج.م</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Cash Summary -->
                    <h6 class="text-muted mb-3"><i class="bi bi-cash-coin me-2"></i>ملخص النقدية</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <p class="mb-1 text-muted small">رصيد الافتتاح</p>
                                    <h4 class="mb-0">{{ number_format($shift->opening_cash, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success bg-opacity-10 h-100">
                                <div class="card-body text-center">
                                    <p class="mb-1 text-muted small">مبيعات نقدية</p>
                                    <h4 class="mb-0 text-success">{{ number_format($shift->total_cash, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info bg-opacity-10 h-100">
                                <div class="card-body text-center">
                                    <p class="mb-1 text-muted small">مبيعات بطاقات</p>
                                    <h4 class="mb-0 text-info">{{ number_format($shift->total_card, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary bg-opacity-10 h-100">
                                <div class="card-body text-center">
                                    <p class="mb-1 text-muted small">المتوقع في الصندوق</p>
                                    <h4 class="mb-0 text-primary">
                                        {{ number_format($shift->expected_cash ?? ($shift->opening_cash + $shift->total_cash), 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($shift->status === 'closed')
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <p class="mb-1 text-muted">رصيد الإغلاق الفعلي</p>
                                        <h3 class="mb-0">{{ number_format($shift->closing_cash, 2) }} ج.م</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="card h-100 {{ $shift->cash_difference >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10">
                                    <div class="card-body text-center">
                                        <p class="mb-1 text-muted">الفرق</p>
                                        <h3 class="mb-0 {{ $shift->cash_difference >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $shift->cash_difference >= 0 ? '+' : '' }}{{ number_format($shift->cash_difference, 2) }}
                                            ج.م
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($shift->closing_notes)
                            <div class="alert alert-light">
                                <strong>ملاحظات الإغلاق:</strong> {{ $shift->closing_notes }}
                            </div>
                        @endif
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('pos.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-right me-1"></i>العودة لنقطة البيع
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>طباعة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection