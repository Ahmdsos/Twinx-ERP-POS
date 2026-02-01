@extends('layouts.app')

@section('title', 'تسجيل سائق جديد')
@section('header', 'إدارة أسطول التوصيل')

@section('content')
    <div class="dashboard-wrapper">
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <a href="{{ route('hr.delivery.index') }}"
                    class="btn btn-link text-secondary text-decoration-none p-0 mb-2 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-right"></i> والعودة لأسطول التوصيل
                </a>
                <h3 class="fw-black text-white mb-0">تسجيل سائق جديد</h3>
                <p class="text-secondary small opacity-75 mt-1">قم باختيار موظف حالي وتزويده بصلاحيات التوصيل والبيانات
                    اللوجستية.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="{{ route('hr.delivery.store') }}" method="POST">
                    @csrf
                    <div class="glass-card rounded-4 p-4 mb-4 border-white border-opacity-10">
                        <h5 class="text-primary fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge"></i> اختيار الموظف والبيانات الأساسية
                        </h5>
                        <div class="row g-4">
                            <div class="col-12 text-start">
                                <label class="form-label text-secondary small fw-bold">الموظف المرشح <span
                                        class="text-danger">*</span></label>
                                <select name="employee_id"
                                    class="form-select bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none @error('employee_id') is-invalid @enderror"
                                    required>
                                    <option value="" class="bg-dark">-- اختر موظف نشط من القائمة --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" class="bg-dark" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->full_name }} ({{ $employee->employee_code }}) -
                                            {{ $employee->position }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <p class="x-small text-secondary mt-2 mb-0">يظهر هنا الموظفون الذين ليس لديهم ملف سائق نشط
                                    حالياً.</p>
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label text-secondary small fw-bold">رقم رخصة القيادة</label>
                                <input type="text" name="license_number"
                                    class="form-control bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('license_number') }}" placeholder="أدخل رقم الرخصة...">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label text-secondary small fw-bold">تاريخ انتهاء الرخصة</label>
                                <input type="date" name="license_expiry"
                                    class="form-control bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('license_expiry') }}">
                            </div>

                            <div class="col-12 text-start">
                                <label class="form-label text-secondary small fw-bold">بيانات و وصف المركبة</label>
                                <input type="text" name="vehicle_info"
                                    class="form-control bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none"
                                    value="{{ old('vehicle_info') }}"
                                    placeholder="مثلاً: تويوتا هيلوكس 2024 - رقم اللوحة (أ ب ج 123)">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label text-secondary small fw-bold">حالة السائق التشغيلية</label>
                                <select name="status"
                                    class="form-select bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none"
                                    required>
                                    <option value="available" class="bg-dark" selected>متاح للعمل فورا</option>
                                    <option value="offline" class="bg-dark">غير متصل حالياً</option>
                                    <option value="suspended" class="bg-dark">موقوف مؤقتاً</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-lg py-3">
                            <i class="bi bi-check-circle-fill me-2"></i> إتمام تسجيل السائق
                        </button>
                        <a href="{{ route('hr.delivery.index') }}"
                            class="btn btn-link text-secondary text-decoration-none fw-bold text-center">إلغاء والعودة</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .fw-black {
            font-weight: 900 !important;
        }

        .x-small {
            font-size: 0.75rem !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
@endsection