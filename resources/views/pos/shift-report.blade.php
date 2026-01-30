@extends('layouts.app')

@section('title', 'تقرير إغلاق الوردية - Twinx ERP')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-end mb-4 no-print">
                    <div>
                        <h1 class="h3 mb-1 fw-bold">تقرير تفصيلي للوردية</h1>
                        <p class="text-muted mb-0"><i class="bi bi-person-check me-1"></i> الموظف:
                            {{ $shift->user->name ?? 'غير محدد' }}</p>
                    </div>
                    <div class="btn-group shadow-sm">
                        <button onclick="window.print()" class="btn btn-white border border-2">
                            <i class="bi bi-printer me-2"></i> طباعة التقرير
                        </button>
                        <a href="{{ route('pos.index') }}" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-cart3 me-2"></i> العودة للكاشير
                        </a>
                    </div>
                </div>

                <!-- Report Body -->
                <div class="card border-0 shadow-lg overflow-hidden">
                    <!-- Status Banner -->
                    <div class="py-2 text-center text-white fw-bold {{ $shift->status === 'open' ? 'bg-success' : 'bg-dark' }}"
                        style="letter-spacing: 2px; font-size: 0.8rem;">
                        {{ $shift->status === 'open' ? 'الوردية حالياً: مفتوحة' : 'الوردية حالياً: مغلقة' }}
                    </div>

                    <div class="card-body p-5">
                        <!-- Invoice Header (Print only) -->
                        <div class="d-none d-print-block text-center mb-5 border-bottom pb-4">
                            <h2 class="fw-bold mb-1">TWINX ERP</h2>
                            <p class="text-muted small mb-1">شركة المنصة الموحدة للتفتيش والتدريب</p>
                            <h4 class="mt-4 mb-0 fw-bold">تقرير وردية كاشير</h4>
                            <p class="small">رقم الوردية: #SH-{{ str_pad($shift->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>

                        <div class="row g-5">
                            <!-- Left Column: Dates & Sales Counts -->
                            <div class="col-md-6 border-end-md">
                                <h6 class="text-uppercase fw-bold text-primary mb-4" style="letter-spacing: 1px;">معلومات
                                    الفترة</h6>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">تاريخ وموعد البدء:</span>
                                        <span class="fw-bold text-ltr">{{ $shift->opened_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">تاريخ وموعد الإغلاق:</span>
                                        <span
                                            class="fw-bold text-ltr">{{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : 'مازالت مفتوحة' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">مدة الوردية:</span>
                                        <span
                                            class="fw-bold">{{ $shift->closed_at ? $shift->opened_at->diffForHumans($shift->closed_at, true) : $shift->opened_at->diffForHumans(now(), true) }}</span>
                                    </div>
                                </div>

                                <h6 class="text-uppercase fw-bold text-primary mb-4 mt-5" style="letter-spacing: 1px;">
                                    إحصائيات المبيعات</h6>
                                <div class="p-4 rounded-4 bg-light">
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                        <span class="text-muted">عدد العمليات الكلي:</span>
                                        <span class="fw-bold fs-5 text-dark">{{ $shift->total_sales }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">إجمالي المبيعات (ضريبة + خصم):</span>
                                        <span class="fw-bold fs-5 text-primary">{{ number_format($shift->total_amount, 2) }}
                                            ج.م</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Money Breakdown -->
                            <div class="col-md-6">
                                <h6 class="text-uppercase fw-bold text-primary mb-4" style="letter-spacing: 1px;">تفاصيل
                                    المبالغ والتحصيل</h6>

                                <table class="table table-borderless align-middle">
                                    <tbody>
                                        <tr>
                                            <td class="ps-0 py-3 text-muted"><i class="bi bi-wallet2 me-2"></i> رصيد
                                                الافتتاح (عهدة)</td>
                                            <td class="pe-0 py-3 text-end fw-bold">
                                                {{ number_format($shift->opening_cash, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 py-3 text-muted text-success"><i
                                                    class="bi bi-cash-stack me-2"></i> مبيعات نقدية (+)</td>
                                            <td class="pe-0 py-3 text-end fw-bold text-success">
                                                {{ number_format($shift->total_cash, 2) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="ps-0 py-3 fw-bold text-dark">المتوقع وجوده في الدرج</td>
                                            <td class="pe-0 py-3 text-end fw-bold text-dark fs-5">
                                                {{ number_format($shift->opening_cash + $shift->total_cash, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 py-3 text-muted text-info"><i
                                                    class="bi bi-credit-card me-2"></i> مبيعات ببطاقات بنكية</td>
                                            <td class="pe-0 py-3 text-end fw-bold text-info">
                                                {{ number_format($shift->total_card, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                @if($shift->status === 'closed')
                                    <div
                                        class="mt-4 p-4 rounded-4 {{ $shift->cash_difference == 0 ? 'bg-success-subtle' : ($shift->cash_difference > 0 ? 'bg-primary-subtle' : 'bg-danger-subtle') }}">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">الرصيد الفعلي المسلم:</span>
                                            <span class="fw-bold">{{ number_format($shift->closing_cash, 2) }} ج.م</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">الفارق (عجز/زيادة):</span>
                                            <span
                                                class="fw-bold fs-4 {{ $shift->cash_difference >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $shift->cash_difference >= 0 ? '+' : '' }}{{ number_format($shift->cash_difference, 2) }}
                                                ج.م
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($shift->closing_notes)
                            <div class="mt-5 pt-4 border-top">
                                <h6 class="text-uppercase fw-bold text-muted mb-3 small" style="letter-spacing: 1px;">ملاحظات
                                    الإغلاق</h6>
                                <div class="p-3 bg-light rounded italic text-secondary">
                                    "{{ $shift->closing_notes }}"
                                </div>
                            </div>
                        @endif

                        <!-- Signatures (Print only) -->
                        <div class="d-none d-print-block mt-5 pt-5">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-top pt-2 mx-5">توقيع الكاشير</div>
                                </div>
                                <div class="col-6">
                                    <div class="border-top pt-2 mx-5">توقيع المدير المسؤول</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning for Discrepancy (No print) -->
                @if($shift->status === 'closed' && abs($shift->cash_difference) > 0)
                    <div class="alert alert-warning border-0 shadow-sm mt-4 d-flex align-items-center no-print">
                        <i class="bi bi-exclamation-octagon fs-2 me-3 text-warning"></i>
                        <div>
                            <h6 class="fw-bold mb-1">تنبيه: يوجد فرق في عهدة الوردية!</h6>
                            <p class="mb-0 small">الرجاء مراجعة الفواتير النقدية والتأكد من مطابقتها للدرج قبل ترحيل الوردية.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .text-ltr {
            direction: ltr;
            display: inline-block;
        }

        @media (min-width: 768px) {
            .border-end-md {
                border-right: 1px solid #eee !important;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                border-radius: 0 !important;
            }

            .container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
            }

            body {
                background-color: white !important;
            }

            .p-5 {
                padding: 2rem !important;
            }

            .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
@endsection