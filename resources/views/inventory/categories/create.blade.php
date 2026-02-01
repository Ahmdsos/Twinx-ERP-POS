@extends('layouts.app')

@section('title', 'إضافة تصنيف جديد')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center icon-box-lg mb-3 shadow-neon">
                        <i class="bi bi-diagram-2-fill fs-2 text-white"></i>
                    </div>
                    <h3 class="fw-bold text-white tracking-wide">إضافة تصنيف جديد</h3>
                    <p class="text-gray-400">تنظيم هيكلي دقيق لمنتجات المخزون</p>
                </div>

                <!-- Glass Card Content -->
                <div class="glass-panel p-5 position-relative overflow-hidden">
                    <div class="glow-orb bg-cyan-500 opacity-10" style="top: -50px; right: -50px;"></div>

                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf

                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">اسم
                                    التصنيف <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-tag-fill"></i></span>
                                    <input type="text" name="name"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring"
                                        placeholder="مثال: إلكترونيات، هواتف ذكية..." required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label
                                    class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">التصنيف
                                    الأب (اختياري)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-diagram-3"></i></span>
                                    <select name="parent_id"
                                        class="form-select form-select-dark border-start-0 ps-0 text-white cursor-pointer hover:bg-white-5">
                                        <option value="">-- تصنيف رئيسي (Root) --</option>
                                        @foreach($categories as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-text text-gray-500 small mt-2 ms-2"><i class="bi bi-info-circle me-1"></i>
                                    اختر تصنيفاً أباً لإنشاء "تصنيف فرعي" أو اتركه فارغاً لإنشاء "تصنيف رئيسي".</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label
                                class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">الوصف</label>
                            <textarea name="description"
                                class="form-control form-control-dark text-white placeholder-gray-600" rows="4"
                                placeholder="أضف وصفاً تقنياً أو ملاحظات إدارية..."></textarea>
                        </div>

                        <div class="mb-5">
                            <div class="d-flex align-items-center justify-content-between p-4 rounded-3 border border-white-10 bg-white-5 transition-all hover:bg-white-10 cursor-pointer"
                                onclick="document.getElementById('isActiveCheck').click()">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box-sm bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-power"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-white mb-1">تفعيل التصنيف</h6>
                                        <p class="mb-0 text-gray-500 small">السماح بتداول المنتجات المندرجة تحته</p>
                                    </div>
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input fs-4 cursor-pointer" type="checkbox" name="is_active"
                                        value="1" id="isActiveCheck" checked onclick="event.stopPropagation()">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top border-white-10">
                            <a href="{{ route('categories.index') }}"
                                class="btn btn-link text-gray-400 text-decoration-none hover-text-white d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right"></i> إلغاء
                            </a>
                            <button type="submit"
                                class="btn btn-action-primary px-5 py-2 rounded-pill fw-bold shadow-neon d-flex align-items-center gap-2">
                                <i class="bi bi-check-lg"></i> إنشاء التصنيف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Scoped Styles for Form */
        .icon-box-lg {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(30, 41, 59, 0.5));
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.15);
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

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 0.8rem 1rem;
        }

        .form-control-dark:focus,
        .form-select-dark:focus {
            border-color: #0ea5e9 !important;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .custom-switch .form-check-input {
            background-color: #334155;
            border-color: #475569;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%28255, 255, 255, 0.25%29'/%3e%3c/svg%3e");
        }

        .custom-switch .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
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

        .hover-bg-white-5:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .hover-bg-white-10:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
@endsection