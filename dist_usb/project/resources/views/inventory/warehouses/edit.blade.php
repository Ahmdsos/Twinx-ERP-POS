@extends('layouts.app')

@section('title', 'تعديل مستودع')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center icon-box-lg mb-3 shadow-neon-amber">
                        <i class="bi bi-pencil-square fs-2 text-body"></i>
                    </div>
                    <h3 class="fw-bold text-heading tracking-wide">تعديل بيانات المستودع</h3>
                    <p class="text-secondary">تحديث: <span class="text-amber-400 fw-bold">{{ $warehouse->name }}</span></p>
                </div>

                <!-- Glass Card Content -->
                <div class="glass-panel p-5 position-relative overflow-hidden">
                    <div class="glow-orb bg-amber-500 opacity-10" style="top: -50px; right: -50px;"></div>

                    <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">كود
                                    المستودع</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-qr-code"></i></span>
                                    <input type="text" value="{{ $warehouse->code }}"
                                        class="form-control form-control border-start-0 ps-0 text-secondary font-monospace"
                                        disabled style="background: rgba(0,0,0,0.5) !important;">
                                </div>
                                <div class="form-text text-gray-600 x-small ms-1 mt-1">لا يمكن تعديل كود المستودع</div>
                            </div>
                            <div class="col-md-8">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">اسم
                                    المستودع <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-tag-fill"></i></span>
                                    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}"
                                        class="form-control form-control border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-amber"
                                        placeholder="اسم المستودع..." required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">{{ __('Address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" name="address" value="{{ old('address', $warehouse->address) }}"
                                        class="form-control form-control border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-amber"
                                        placeholder="العنوان بالتفصيل">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">رقم
                                    الهاتف</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-telephone-fill"></i></span>
                                    <input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}"
                                        class="form-control form-control border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-amber"
                                        placeholder="01xxxxxxxxx">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">البريد
                                    الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-envelope-fill"></i></span>
                                    <input type="email" name="email" value="{{ old('email', $warehouse->email) }}"
                                        class="form-control form-control border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-amber"
                                        placeholder="email@example.com">
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border border-secondary border-opacity-10-10 bg-surface-5 transition-all hover:bg-surface-10 cursor-pointer"
                                    onclick="document.getElementById('isActiveCheck').click()">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="icon-box-sm {{ $warehouse->is_active ? 'bg-success text-success' : 'bg-danger text-danger' }} bg-opacity-10">
                                            <i class="bi bi-power"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-heading mb-0">تفعيل المستودع</h6>
                                            <p
                                                class="mb-0 {{ $warehouse->is_active ? 'text-success' : 'text-danger' }} x-small">
                                                {{ $warehouse->is_active ? 'نشط حالياً' : 'معطل حالياً' }}</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch custom-switch-amber">
                                        <input class="form-check-input fs-5 cursor-pointer" type="checkbox" name="is_active"
                                            value="1" id="isActiveCheck" {{ $warehouse->is_active ? 'checked' : '' }}
                                            onclick="event.stopPropagation()">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border border-secondary border-opacity-10-10 bg-surface-5 transition-all hover:bg-surface-10 cursor-pointer"
                                    onclick="document.getElementById('isDefaultCheck').click()">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-box-sm bg-amber bg-opacity-10 text-amber-400">
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-heading mb-0">تعيين كافتراضي</h6>
                                            <p class="mb-0 text-gray-500 x-small">تعيين كمستودع رئيسي للنظام</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch custom-switch-amber">
                                        <input class="form-check-input fs-5 cursor-pointer" type="checkbox"
                                            name="is_default" value="1" id="isDefaultCheck" {{ $warehouse->is_default ? 'checked' : '' }} onclick="event.stopPropagation()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top border-secondary border-opacity-10-10">
                            <a href="{{ route('warehouses.index') }}"
                                class="btn btn-link text-secondary text-decoration-none hover-text-white d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right"></i>{{ __('Cancel') }}</a>
                            <button type="submit"
                                class="btn btn-action-amber px-5 py-2 rounded-pill fw-bold shadow-neon-amber d-flex align-items-center gap-2">
                                <i class="bi bi-save"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Scoped Styles for Edit Form (Amber Theme) */
        .icon-box-lg {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(30, 41, 59, 0.5));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.15);
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
            color: var(--text-secondary);
        }

        .form-control-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
            padding: 0.8rem 1rem;
        }

        .form-control-dark:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .custom-switch-amber .form-check-input:checked {
            background-color: #f59e0b;
            border-color: #f59e0b;
        }

        .btn-action-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: var(--text-primary);
            transition: all 0.3s;
        }

        .btn-action-amber:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.5);
        }

        .placeholder-gray-600::placeholder {
            color: #475569;
        }

        .bg-surface-5 {
            background: rgba(255, 255, 255, 0.02);
        }

        .bg-surface-10 {
            background: var(--btn-glass-bg);
        }

        .border-secondary border-opacity-10-10 {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        .x-small {
            font-size: 0.75rem;
        }

        .text-amber-400 {
            color: #fbbf24 !important;
        }

        .bg-amber-500 {
            background-color: #f59e0b !important;
        }
    </style>
@endsection