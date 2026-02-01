@extends('layouts.app')

@section('title', 'تعديل التصنيف')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center icon-box-lg mb-3 shadow-neon-amber">
                        <i class="bi bi-pencil-square fs-2 text-white"></i>
                    </div>
                    <h3 class="fw-bold text-white tracking-wide">تعديل التصنيف</h3>
                    <p class="text-gray-400">تحديث بيانات: <span class="text-amber-400 fw-bold">{{ $category->name }}</span>
                    </p>
                </div>

                <!-- Glass Card Content -->
                <div class="glass-panel p-5 position-relative overflow-hidden">
                    <div class="glow-orb bg-amber-500 opacity-10" style="top: -50px; right: -50px;"></div>

                    <form action="{{ route('categories.update', $category) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">اسم
                                    التصنيف <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-tag-fill"></i></span>
                                    <input type="text" name="name" value="{{ old('name', $category->name) }}"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-amber"
                                        placeholder="مثال: إلكترونيات، هواتف ذكية..." required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label
                                    class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">التصنيف
                                    الأب</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-diagram-3"></i></span>
                                    <select name="parent_id"
                                        class="form-select form-select-dark border-start-0 ps-0 text-white cursor-pointer hover:bg-white-5">
                                        <option value="">-- تصنيف رئيسي (Root) --</option>
                                        @foreach($categories as $parent)
                                            <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label
                                class="form-label text-amber-400 small fw-bold text-uppercase tracking-wider ps-1">الوصف</label>
                            <textarea name="description"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-amber"
                                rows="4">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <div class="mb-5">
                            <div class="d-flex align-items-center justify-content-between p-4 rounded-3 border border-white-10 bg-white-5 transition-all hover:bg-white-10 cursor-pointer"
                                onclick="document.getElementById('isActiveCheck').click()">
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        class="icon-box-sm {{ $category->is_active ? 'bg-success text-success' : 'bg-danger text-danger' }} bg-opacity-10">
                                        <i class="bi bi-power"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-white mb-1">تفعيل التصنيف</h6>
                                        <p class="mb-0 text-gray-500 small">حالة الظهور الحالية: <span
                                                class="{{ $category->is_active ? 'text-success' : 'text-danger' }}">{{ $category->is_active ? 'نشط' : 'غير نشط' }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="form-check form-switch custom-switch-amber">
                                    <input class="form-check-input fs-4 cursor-pointer" type="checkbox" name="is_active"
                                        value="1" id="isActiveCheck" {{ $category->is_active ? 'checked' : '' }}
                                        onclick="event.stopPropagation()">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top border-white-10">
                            <a href="{{ route('categories.index') }}"
                                class="btn btn-link text-gray-400 text-decoration-none hover-text-white d-flex align-items-center gap-2">
                                <i class="bi bi-x-lg"></i> إلغاء
                            </a>
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
            border-color: #f59e0b !important;
            /* Amber-500 */
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .custom-switch-amber .form-check-input {
            background-color: #334155;
            border-color: #475569;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%28255, 255, 255, 0.25%29'/%3e%3c/svg%3e");
        }

        .custom-switch-amber .form-check-input:checked {
            background-color: #f59e0b;
            border-color: #f59e0b;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
        }

        .btn-action-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-action-amber:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.5);
        }

        .text-amber-400 {
            color: #fbbf24 !important;
        }

        .bg-amber-500 {
            background-color: #f59e0b !important;
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