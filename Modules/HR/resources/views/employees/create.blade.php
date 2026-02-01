@extends('layouts.app')

@section('title', 'إضافة موظف جديد')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Back Button -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12 text-start">
                <a href="{{ route('hr.employees.index') }}"
                    class="btn btn-icon-box bg-white bg-opacity-10 text-white rounded-3 mb-2 d-inline-flex align-items-center justify-content-center border border-white border-opacity-10">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <h2 class="fw-black text-white mb-0 mt-2">إضافة موظف جديد</h2>
                <p class="text-secondary small opacity-75">إنشاء سجل وظيفي جديد وإدخال البيانات الأساسية والمالية.</p>
            </div>
        </div>

        <form action="{{ route('hr.employees.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <!-- Left Column: Main Forms -->
                <div class="col-lg-8">
                    <!-- Personal Info Card -->
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h5 class="text-primary fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-person-bounding-box"></i> البيانات الشخصية الأساسية
                        </h5>
                        <div class="row g-4 text-start">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الاسم الأول <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="first_name"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الاسم الأخير <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="last_name"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">البريد الإلكتروني <span
                                        class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الهاتف <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="phone"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Professional Info Card -->
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h5 class="text-info fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-briefcase-fill"></i> الوظيفة والتعاقد
                        </h5>
                        <div class="row g-4 text-start">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">المسمى الوظيفي <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="position"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('position') is-invalid @enderror"
                                    value="{{ old('position') }}" required>
                                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">القسم</label>
                                <input type="text" name="department"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('department') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الراتب الأساسي <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="basic_salary"
                                        class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('basic_salary') is-invalid @enderror"
                                        value="{{ old('basic_salary') }}" required>
                                    <span
                                        class="input-group-text bg-dark border-white border-opacity-10 text-secondary">ج.م</span>
                                </div>
                                @error('basic_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">تاريخ التعيين <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="date_of_joining"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none @error('date_of_joining') is-invalid @enderror"
                                    value="{{ old('date_of_joining', date('Y-m-d')) }}" required>
                                @error('date_of_joining') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4 text-start">
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h6 class="text-white fw-bold mb-3">الحالة والوصول</h6>
                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">الحالة</label>
                            <select name="status"
                                class="form-select bg-dark border-white border-opacity-10 text-white shadow-none" required>
                                @foreach(Modules\HR\Models\Employee::getStatusLabels() as $val => $label)
                                    <option value="{{ $val }}" class="bg-dark text-white" {{ old('status', 'active') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-black shadow-lg py-3 border-0"
                            style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                            <i class="bi bi-person-plus-fill me-2"></i> حفظ الموظف الجديد
                        </button>
                        <a href="{{ route('hr.employees.index') }}"
                            class="btn btn-link text-secondary text-decoration-none fw-bold text-center">إلغاء والعودة</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .fw-black {
            font-weight: 900 !important;
        }

        .glass-card-deep {
            background: rgba(13, 22, 45, 0.85);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            color: white !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: #0d6efd !important;
            color: white !important;
        }

        .btn-icon-box {
            width: 40px;
            height: 40px;
        }
    </style>
@endsection