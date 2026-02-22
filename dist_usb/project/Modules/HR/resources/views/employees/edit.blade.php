@extends('layouts.app')

@section('title', 'تعديل بيانات الموظف')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Back Button -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12 text-start">
                <a href="{{ route('hr.employees.show', $employee->id) }}"
                    class="btn btn-icon-box bg-white bg-opacity-10 text-body rounded-3 mb-2 d-inline-flex align-items-center justify-content-center border border-secondary border-opacity-10 border-opacity-10">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <h2 class="fw-black text-heading mb-0 mt-2">تعديل: {{ $employee->full_name }}</h2>
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
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('first_name', $employee->first_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الاسم الأخير</label>
                                <input type="text" name="last_name"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('last_name', $employee->last_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">البريد الإلكتروني</label>
                                <input type="email" name="email"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('email', $employee->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الهاتف</label>
                                <input type="text" name="phone"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('phone', $employee->phone) }}" required>
                            </div>

                            <!-- New Fields -->
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الهوية الوطنية / جواز
                                    السفر</label>
                                <input type="text" name="id_number"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('id_number', $employee->id_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">تاريخ الميلاد</label>
                                <input type="date" name="birth_date"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('birth_date', $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">العنوان الحالي</label>
                                <input type="text" name="address"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('address', $employee->address) }}" placeholder="المدينة، الحي، الشارع...">
                            </div>
                        </div>
                    </div>

                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h5 class="text-info fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-briefcase-fill"></i> البيانات المالية والوظيفية
                        </h5>
                        <div class="row g-4 text-start">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">المسمى الوظيفي</label>
                                <input type="text" name="position"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('position', $employee->position) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">القسم</label>
                                <input type="text" name="department"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('department', $employee->department) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الراتب الأساسي</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="basic_salary"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('basic_salary', $employee->basic_salary) }}" required>
                                    <span
                                        class="input-group-text bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-secondary">ج.م</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">تاريخ التعيين</label>
                                <input type="date" name="date_of_joining"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('date_of_joining', $employee->date_of_joining ? $employee->date_of_joining->format('Y-m-d') : '') }}">
                            </div>
                             <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">نوع العقد</label>
                                <select name="contract_type" class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none">
                                    <option value="Full Time" {{ old('contract_type', $employee->contract_type) == 'Full Time' ? 'selected' : '' }}>دوام كامل</option>
                                    <option value="Part Time" {{ old('contract_type', $employee->contract_type) == 'Part Time' ? 'selected' : '' }}>دوام جزئي</option>
                                    <option value="Contract" {{ old('contract_type', $employee->contract_type) == 'Contract' ? 'selected' : '' }}>عقد مؤقت</option>
                                </select>
                            </div>
                        </div>
                    </div>

                     <!-- Bank Info -->
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h5 class="text-success fw-bold mb-4 d-flex align-items-center gap-2">
                             <i class="bi bi-bank2"></i> البيانات البنكية
                        </h5>
                        <div class="row g-4 text-start">
                             <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">اسم البنك</label>
                                <input type="text" name="bank_name"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('bank_name', $employee->bank_name) }}">
                            </div>
                             <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الحساب</label>
                                <input type="text" name="bank_account_number"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('bank_account_number', $employee->bank_account_number) }}">
                            </div>
                             <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">IBAN</label>
                                <input type="text" name="iban"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('iban', $employee->iban) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h5 class="text-danger fw-bold mb-4 d-flex align-items-center gap-2">
                             <i class="bi bi-telephone-plus"></i> الطوارئ
                        </h5>
                        <div class="row g-4 text-start">
                             <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">اسم جهة الاتصال</label>
                                <input type="text" name="emergency_contact_name"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                            </div>
                             <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم هاتف الطوارئ</label>
                                <input type="text" name="emergency_contact_phone"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Driver Section -->
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_driver" name="is_driver" value="1" 
                                {{ old('is_driver', $employee->deliveryDriver ? 1 : 0) ? 'checked' : '' }} onchange="toggleDriverFields()">
                            <h5 class="form-check-label text-warning fw-bold d-inline-block ms-2" for="is_driver">
                                <i class="bi bi-truck"></i> تعيين كسائق توصيل
                            </h5>
                        </div>

                        <div id="driver_fields" style="display: none;">
                            <div class="row g-4 text-start">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">رقم الرخصة</label>
                                    <input type="text" name="license_number"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('license_number', $employee->deliveryDriver?->license_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">تاريخ انتهاء الرخصة</label>
                                    <input type="date" name="license_expiry"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('license_expiry', $employee->deliveryDriver?->license_expiry) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">نوع المركبة</label>
                                    <input type="text" name="vehicle_type"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('vehicle_type', $employee->deliveryDriver ? explode(' - ', $employee->deliveryDriver->vehicle_info)[0] : '') }}" 
                                        placeholder="مثال: موتوسيكل، سيارة فان...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">رقم اللوحة</label>
                                    <input type="text" name="vehicle_plate"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('vehicle_plate', $employee->deliveryDriver && count(explode(' - ', $employee->deliveryDriver->vehicle_info)) > 1 ? explode(' - ', $employee->deliveryDriver->vehicle_info)[1] : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 text-start">
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h6 class="text-heading fw-bold mb-3">الحالة والوصول</h6>
                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">تغيير الحالة</label>
                            <select name="status"
                                class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none" required>
                                @foreach(Modules\HR\Models\Employee::getStatusLabels() as $val => $label)
                                    <option value="{{ $val }}" class="bg-surface-secondary text-body" {{ old('status', $employee->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">حساب النظام المرتبط (User)</label>
                            <select name="user_id"
                                class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none">
                                <option value="" class="bg-surface-secondary text-secondary">-- غير مرتبط --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" class="bg-surface-secondary text-body" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-secondary opacity-50 x-small mt-2">
                                <i class="bi bi-info-circle me-1"></i> تغيير الحساب المرتبط قد يؤثر على صلاحيات الدخول.
                            </div>
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

@push('scripts')
    <script>
        function toggleDriverFields() {
            const isDriver = document.getElementById('is_driver').checked;
            const driverFields = document.getElementById('driver_fields');

            if (isDriver) {
                driverFields.style.display = 'block';
            } else {
                driverFields.style.display = 'none';
            }
        }

        // Run on load
        document.addEventListener('DOMContentLoaded', function () {
            toggleDriverFields();
        });
    </script>
@endpush