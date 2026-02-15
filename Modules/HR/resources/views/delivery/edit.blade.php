@extends('layouts.app')

@section('title', 'تعديل بيانات السائق')
@section('header', 'إدارة أسطول التوصيل')

@section('content')
    <div class="dashboard-wrapper">
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <a href="{{ route('hr.delivery.index') }}"
                    class="btn btn-link text-secondary text-decoration-none p-0 mb-2 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-right"></i> والعودة لأسطول التوصيل
                </a>
                <h3 class="fw-black text-heading mb-0">تعديل ملف السائق: {{ $driver->employee->full_name ?? 'موظف غير متوفر' }}</h3>
                <p class="text-secondary small opacity-75 mt-1">تحديث بيانات الرخصة، المركبة، وحالة العمل الميداني.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="{{ route('hr.delivery.update', $driver) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="glass-card rounded-4 p-4 mb-4 border-secondary border-opacity-10 border-opacity-10">
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-white bg-opacity-5 rounded-4">
                            <div class="avatar-circle rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                style="width: 54px; height: 54px;">
                                <i class="bi bi-person-vcard fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-heading fw-bold mb-0">{{ $driver->employee->full_name ?? 'بيانات غير متوفرة' }}</h6>
                                <div class="text-secondary small">{{ $driver->employee->position ?? '---' }} | كود:
                                    {{ $driver->employee->employee_code ?? '---' }}</div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 text-start">
                                <label class="form-label text-secondary small fw-bold">رقم رخصة القيادة</label>
                                <input type="text" name="license_number"
                                    class="form-control bg-white bg-opacity-5 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('license_number', $driver->license_number) }}">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label text-secondary small fw-bold">تاريخ انتهاء الرخصة</label>
                                <input type="date" name="license_expiry"
                                    class="form-control bg-white bg-opacity-5 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('license_expiry', $driver->license_expiry ? $driver->license_expiry->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12 text-start">
                                <label class="form-label text-secondary small fw-bold">بيانات و وصف المركبة المتصلة</label>
                                <input type="text" name="vehicle_info"
                                    class="form-control bg-white bg-opacity-5 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('vehicle_info', $driver->vehicle_info) }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-lg py-3">
                            <i class="bi bi-save-fill me-2"></i> تحديث السجل اللوجستي
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