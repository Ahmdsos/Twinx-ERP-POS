@extends('layouts.app')

@section('title', 'تفاصيل التحصيل: ' . $customerPayment->payment_number)

@section('content')
    <div class="container p-0" style="max-width: 800px;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-gradient-to-br from-green-600 to-teal-700 rounded-circle shadow-lg text-white">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-0">تفاصيل التحصيل</h2>
                    <p class="text-gray-400 mb-0 x-small">عرض بيانات سند قبض</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('customer-payments.index') }}" class="btn btn-glass-outline rounded-pill">
                    <i class="bi bi-arrow-right me-2"></i> القائمة
                </a>
                <a href="{{ route('customer-payments.print', $customerPayment->id) }}" target="_blank"
                    class="btn btn-dark border-start border-white/10 text-info hover-bg-info-dark rounded-pill px-4">
                    <i class="bi bi-printer me-2"></i> طباعة الإيصال
                </a>
            </div>
        </div>

        <div class="glass-panel p-0 overflow-hidden position-relative">
            <div class="absolute-glow top-0 end-0 bg-success/20"></div>

            <div class="bg-white/5 p-4 border-bottom border-white/10 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div
                        class="avatar-sm bg-success/20 text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-hash"></i>
                    </div>
                    <span
                        class="fs-4 font-monospace fw-bold text-white tracking-wide">{{ $customerPayment->payment_number }}</span>
                </div>
                <div class="badge bg-success/20 text-success border border-success/20 px-3 py-2 rounded-pill">
                    تم التحصيل
                </div>
            </div>

            <div class="p-5">
                <div class="row g-5">
                    <div class="col-md-6 border-end border-white/10">
                        <label class="section-label mb-3 text-success">بيانات العميل</label>
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="avatar-md bg-gradient-to-br from-gray-700 to-gray-800 rounded-circle text-white d-flex align-items-center justify-content-center border border-white/10 shadow-sm"
                                style="width: 50px; height: 50px;">
                                <span class="fs-5 fw-bold">{{ substr($customerPayment->customer->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h5 class="text-white fw-bold mb-0">{{ $customerPayment->customer->name }}</h5>
                                <small class="text-gray-500">Code: {{ $customerPayment->customer->code ?? 'N/A' }}</small>
                            </div>
                        </div>

                        <div class="vstack gap-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-400">تاريخ التحصيل</span>
                                <span
                                    class="text-white font-monospace">{{ $customerPayment->payment_date->format('Y-m-d') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-400">طريقة الدفع</span>
                                <span class="text-white">{{ $customerPayment->payment_method }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-400">الرقم المرجعي</span>
                                <span
                                    class="text-white font-monospace">{{ $customerPayment->reference_number ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 ps-md-5">
                        <label class="section-label mb-3 text-success">تفاصيل المبلغ</label>
                        <div class="bg-success/5 border border-success/10 rounded-4 p-4 text-center mb-4">
                            <small class="text-success-300 text-uppercase letter-spacing-2 d-block mb-1">المبلغ
                                الإجمالي</small>
                            <h1 class="display-5 fw-bold text-white text-glow mb-0">
                                {{ number_format($customerPayment->amount, 2) }}</h1>
                            <span class="text-gray-500 small">GEN</span>
                        </div>

                        <label class="section-label mb-2">ملاحظات</label>
                        <div class="bg-white/5 rounded-3 p-3 text-gray-300 small">
                            {{ $customerPayment->notes ?? 'لا توجد ملاحظات مسجلة على هذا الإيصال.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900/50 p-3 border-top border-white/10 d-flex justify-content-between text-gray-500 x-small">
                <div>
                    <i class="bi bi-person me-1"></i> المسؤول: <span
                        class="text-gray-300">{{ $customerPayment->creator->name ?? 'System' }}</span>
                </div>
                <div>
                    <i class="bi bi-clock me-1"></i> تم الإنشاء: {{ $customerPayment->created_at->format('Y-m-d h:i A') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            display: block;
        }

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
        }

        .absolute-glow {
            position: absolute;
            width: 200px;
            height: 200px;
            filter: blur(60px);
            pointer-events: none;
        }

        .hover-bg-info-dark:hover {
            background-color: #0c4a6e !important;
            color: #38bdf8 !important;
        }
    </style>
@endsection