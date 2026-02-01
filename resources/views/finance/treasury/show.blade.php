@extends('layouts.app')

@section('title', 'تفاصيل السند')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-white mb-1">تفاصيل السند <span
                            class="text-info font-monospace">#{{ $transaction->id }}</span></h4>
                    <div class="text-white-50 small">{{ $transaction->transaction_date->format('Y-m-d') }}</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('treasury.index') }}" class="btn btn-glass-outline">عودة</a>
                    <button onclick="window.print()" class="btn btn-glass-outline">
                        <i class="bi bi-printer me-2"></i> طباعة الإيصال
                    </button>
                </div>
            </div>

            <div class="glass-card mb-4 print-area position-relative overflow-hidden">
                <!-- Watermark -->
                <div class="position-absolute top-50 start-50 translate-middle opacity-10">
                    <i class="bi {{ $transaction->type == 'receipt' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle' }}"
                        style="font-size: 15rem; color: white;"></i>
                </div>

                <!-- Header -->
                <div
                    class="border-bottom border-white border-opacity-10 p-4 d-flex justify-content-between align-items-center position-relative z-1">
                    <div>
                        <h5 class="text-white mb-1 font-monospace fw-bold">TWINX ERP</h5>
                        <div class="text-white-50 small">سند
                            {{ $transaction->type == 'receipt' ? 'قبض نقدية' : 'صرف نقدية' }}</div>
                    </div>
                    <div>
                        @if($transaction->type == 'receipt')
                            <span
                                class="badge bg-success bg-opacity-25 text-success fs-6 border border-success px-4 py-2">RECEIPT
                                VOUCHER</span>
                        @else
                            <span class="badge bg-danger bg-opacity-25 text-danger fs-6 border border-danger px-4 py-2">PAYMENT
                                VOUCHER</span>
                        @endif
                    </div>
                </div>

                <!-- Body -->
                <div class="p-5 position-relative z-1">
                    <div class="row g-4 mb-5">
                        <div class="col-6">
                            <div class="text-white-50 small mb-1">المبلغ</div>
                            <div class="fs-2 fw-bold text-white font-monospace">{{ number_format($transaction->amount, 2) }}
                                ج.م</div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="text-white-50 small mb-1">تاريخ المعاملة</div>
                            <div class="fs-5 text-white">{{ $transaction->transaction_date->format('Y-m-d') }}</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-white-50 small mb-1 d-block">
                                {{ $transaction->type == 'receipt' ? 'استلمنا في (الخزينة):' : 'صرفنا من (الخزينة):' }}
                            </label>
                            <div class="p-3 bg-white bg-opacity-5 rounded-3 border border-white border-opacity-10">
                                <span class="fw-bold text-white">{{ $transaction->treasuryAccount->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small mb-1 d-block">
                                {{ $transaction->type == 'receipt' ? 'من السيد / الحساب:' : 'إلى السيد / الحساب:' }}
                            </label>
                            <div class="p-3 bg-white bg-opacity-5 rounded-3 border border-white border-opacity-10">
                                <span class="fw-bold text-white">{{ $transaction->counterAccount->name }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="text-white-50 small mb-1">وذلك عن:</label>
                            <div
                                class="p-3 bg-white bg-opacity-5 rounded-3 border border-white border-opacity-10 text-white fst-italic">
                                {{ $transaction->description ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="bg-white bg-opacity-5 p-4 d-flex justify-content-between align-items-center position-relative z-1">
                    <div class="text-white-50 small">
                        <i class="bi bi-person-circle me-1"></i> المسؤول: {{ $transaction->creator->name ?? 'System' }}
                    </div>
                    <div class="text-white-50 small font-monospace">
                        Ref: {{ $transaction->reference ?? $transaction->id }}
                    </div>
                </div>
            </div>

            <!-- Journal Link -->
            @if($transaction->journalEntry)
                <div class="text-center">
                    <a href="{{ route('journal-entries.show', $transaction->journalEntry) }}"
                        class="text-white-50 text-decoration-none small hover-link">
                        <i class="bi bi-link-45deg"></i> عرض القيد المحاسبي المرتبط #{{ $transaction->journalEntry->id }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            /* More formal for print */
        }

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .hover-link:hover {
            text-decoration: underline !important;
            color: white !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: 1px solid #000;
                background: white !important;
                color: black !important;
            }

            .text-white,
            .text-white-50 {
                color: black !important;
            }

            .bg-white {
                background-color: #eee !important;
            }
        }
    </style>
@endsection