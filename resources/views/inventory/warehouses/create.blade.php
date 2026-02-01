@extends('layouts.app')

@section('title', 'إضافة مستودع جديد')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-5">
                    <div
                        class="d-inline-flex align-items-center justify-content-center icon-box-lg mb-3 shadow-neon-purple">
                        <i class="bi bi-building-fill-add fs-2 text-white"></i>
                    </div>
                    <h3 class="fw-bold text-white tracking-wide">إضافة مستودع جديد</h3>
                    <p class="text-gray-400">تجهيز نقطة تخزين جديدة للنظام</p>
                </div>

                <!-- Glass Card Content -->
                <div class="glass-panel p-5 position-relative overflow-hidden">
                    <div class="glow-orb bg-purple-500 opacity-10" style="top: -50px; right: -50px;"></div>

                    <form action="{{ route('warehouses.store') }}" method="POST">
                        @csrf

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase tracking-wider ps-1">كود
                                    المستودع <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-qr-code"></i></span>
                                    <input type="text" name="code"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple font-monospace"
                                        placeholder="WH-001" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase tracking-wider ps-1">اسم
                                    المستودع <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-tag-fill"></i></span>
                                    <input type="text" name="name"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        placeholder="مثال: المستودع الرئيسي، فرع المعادي..." required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase tracking-wider ps-1">العنوان</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" name="address"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        placeholder="العنوان بالتفصيل">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase tracking-wider ps-1">رقم
                                    الهاتف</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-telephone-fill"></i></span>
                                    <input type="text" name="phone"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        placeholder="01xxxxxxxxx">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label text-purple-400 small fw-bold text-uppercase tracking-wider ps-1">البريد
                                    الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-envelope-fill"></i></span>
                                    <input type="email" name="email"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                                        placeholder="email@example.com">
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border border-white-10 bg-white-5 transition-all hover:bg-white-10 cursor-pointer"
                                    onclick="document.getElementById('isActiveCheck').click()">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-box-sm bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-power"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-white mb-0">تفعيل المستودع</h6>
                                            <p class="mb-0 text-gray-500 x-small">متاح للعمليات</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch custom-switch-purple">
                                        <input class="form-check-input fs-5 cursor-pointer" type="checkbox" name="is_active"
                                            value="1" id="isActiveCheck" checked onclick="event.stopPropagation()">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border border-white-10 bg-white-5 transition-all hover:bg-white-10 cursor-pointer"
                                    onclick="document.getElementById('isDefaultCheck').click()">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-box-sm bg-amber bg-opacity-10 text-amber-400">
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-white mb-0">تعيين كافتراضي</h6>
                                            <p class="mb-0 text-gray-500 x-small">الخيار التلقائي بالنظام</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch custom-switch-amber">
                                        <input class="form-check-input fs-5 cursor-pointer" type="checkbox"
                                            name="is_default" value="1" id="isDefaultCheck"
                                            onclick="event.stopPropagation()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top border-white-10">
                            <a href="{{ route('warehouses.index') }}"
                                class="btn btn-link text-gray-400 text-decoration-none hover-text-white d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right"></i> إلغاء
                            </a>
                            <button type="submit"
                                class="btn btn-action-purple px-5 py-2 rounded-pill fw-bold shadow-neon-purple d-flex align-items-center gap-2">
                                <i class="bi bi-check-lg"></i> حفظ المستودع
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Scoped Styles for Create Form (Purple Theme) */
        .icon-box-lg {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(30, 41, 59, 0.5));
            border: 1px solid rgba(168, 85, 247, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.15);
        }

        .icon-box-sm {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8;
        }

        .form-control-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 0.8rem 1rem;
        }

        .form-control-dark:focus {
            border-color: #a855f7 !important;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .custom-switch-purple .form-check-input:checked {
            background-color: #a855f7;
            border-color: #a855f7;
        }

        .custom-switch-amber .form-check-input:checked {
            background-color: #f59e0b;
            border-color: #f59e0b;
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-action-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.5);
        }

        .placeholder-gray-600::placeholder {
            color: #475569;
        }

        .bg-white-5 {
            background: rgba(255, 255, 255, 0.02);
        }

        .bg-white-10 {
            background: rgba(255, 255, 255, 0.05);
        }

        .border-white-10 {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        .x-small {
            font-size: 0.75rem;
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .bg-purple-500 {
            background-color: #a855f7 !important;
        }
    </style>
@endsection