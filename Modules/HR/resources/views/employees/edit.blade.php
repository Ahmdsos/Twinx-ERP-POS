@extends('layouts.app')

@section('title', 'تعديل بيانات الموظف')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Back Button -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12 text-start">
                <a href="{{ route('hr.employees.show', $employee->id) }}"
                    class="btn btn-icon-box bg-white bg-opacity-10 text-white rounded-3 mb-2 d-inline-flex align-items-center justify-content-center border border-white border-opacity-10">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <h2 class="fw-black text-white mb-0 mt-2">تعديل: {{ $employee->full_name }}</h2>
                <p class="text-secondary small opacity-75">تحديث السجل الوظيفي والبيانات المالية للموظف.</p>
            </div>
        </div>

        <form action="{{ route('hr.employees.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h5 class="text-primary fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-person-bounding-box"></i> تعديل البيانات الأساسية
                        </h5>
                        <div class="row g-4 text-start">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الاسم الأول</label>
                                <input type="text" name="first_name"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('first_name', $employee->first_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الاسم الأخير</label>
                                <input type="text" name="last_name"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('last_name', $employee->last_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">البريد الإلكتروني</label>
                                <input type="email" name="email"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('email', $employee->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الهاتف</label>
                                <input type="text" name="phone"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('phone', $employee->phone) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card-deep rounded-4 p-4">
                        <h5 class="text-info fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-briefcase-fill"></i> البيانات المالية والوظيفية
                        </h5>
                        <div class="row g-4 text-start">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">المسمى الوظيفي</label>
                                <input type="text" name="position"
                                    class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('position', $employee->position) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الراتب الأساسي</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="basic_salary"
                                        class="form-control bg-dark bg-opacity-25 text-white border-white border-opacity-10 shadow-none"
                                        value="{{ old('basic_salary', $employee->basic_salary) }}" required>
                                    <span
                                        class="input-group-text bg-dark border-white border-opacity-10 text-secondary">ج.م</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 text-start">
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h6 class="text-white fw-bold mb-3">الحالة والوصول</h6>
                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">تغيير الحالة</label>
                            <select name="status"
                                class="form-select bg-dark border-white border-opacity-10 text-white shadow-none" required>
                                @foreach(Modules\HR\Models\Employee::getStatusLabels() as $val => $label)
                                    <option value="{{ $val }}" class="bg-dark text-white" {{ old('status', $employee->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-black shadow-lg py-3 border-0"
                            style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                            <i class="bi bi-check-circle-fill me-2"></i> حفظ التحديثات
                        </button>
                        <a href="{{ route('hr.employees.show', $employee->id) }}"
                            class="btn btn-link text-secondary text-decoration-none fw-bold text-center">إلغاء والتجاهل</a>
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
            background: rgba(10, 15, 30, 0.98) !important;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.7);
        }

        .text-secondary {
            color: #cbd5e0 !important;
            /* Brighter gray */
            opacity: 1 !important;
        }

        .form-label.text-secondary {
            color: #90cdf4 !important;
            /* Brighter blue for labels */
        }

        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            color: #ffffff !important;
            background-color: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(0, 0, 0, 0.5) !important;
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-icon-box {
            width: 40px;
            height: 40px;
        }
    </style>
@endsection