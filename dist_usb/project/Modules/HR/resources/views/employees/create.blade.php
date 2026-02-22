@extends('layouts.app')

@section('title', 'إضافة موظف جديد')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Back Button -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12 text-start">
                <a href="{{ route('hr.employees.index') }}"
                    class="btn btn-icon-box bg-white bg-opacity-10 text-body rounded-3 mb-2 d-inline-flex align-items-center justify-content-center border border-secondary border-opacity-10 border-opacity-10">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <h2 class="fw-black text-heading mb-0 mt-2">إضافة موظف جديد</h2>
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
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الاسم الأخير <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="last_name"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">البريد الإلكتروني <span
                                        class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الهاتف <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="phone"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- New Fields -->
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الهوية الوطنية / جواز
                                    السفر</label>
                                <input type="text" name="id_number"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('id_number') is-invalid @enderror"
                                    value="{{ old('id_number') }}">
                                @error('id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">تاريخ الميلاد</label>
                                <input type="date" name="birth_date"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('birth_date') is-invalid @enderror"
                                    value="{{ old('birth_date') }}">
                                @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">العنوان الحالي</label>
                                <input type="text" name="address"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('address') is-invalid @enderror"
                                    value="{{ old('address') }}" placeholder="المدينة، الحي، الشارع...">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('position') is-invalid @enderror"
                                    value="{{ old('position') }}" required>
                                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">القسم</label>
                                <input type="text" name="department"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('department') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الراتب الأساسي <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="basic_salary"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('basic_salary') is-invalid @enderror"
                                        value="{{ old('basic_salary') }}" required>
                                    <span
                                        class="input-group-text bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-secondary">ج.م</span>
                                </div>
                                @error('basic_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">تاريخ التعيين <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="date_of_joining"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none @error('date_of_joining') is-invalid @enderror"
                                    value="{{ old('date_of_joining', date('Y-m-d')) }}" required>
                                @error('date_of_joining') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">نوع العقد</label>
                                <select name="contract_type"
                                    class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none">
                                    <option value="Full Time" {{ old('contract_type') == 'Full Time' ? 'selected' : '' }}>دوام
                                        كامل</option>
                                    <option value="Part Time" {{ old('contract_type') == 'Part Time' ? 'selected' : '' }}>دوام
                                        جزئي</option>
                                    <option value="Contract" {{ old('contract_type') == 'Contract' ? 'selected' : '' }}>عقد
                                        مؤقت</option>
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
                                    value="{{ old('bank_name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم الحساب</label>
                                <input type="text" name="bank_account_number"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('bank_account_number') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">IBAN</label>
                                <input type="text" name="iban"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('iban') }}">
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
                                    value="{{ old('emergency_contact_name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">رقم هاتف الطوارئ</label>
                                <input type="text" name="emergency_contact_phone"
                                    class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                    value="{{ old('emergency_contact_phone') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Driver Section -->
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_driver" name="is_driver" value="1" {{ old('is_driver') ? 'checked' : '' }} onchange="toggleDriverFields()">
                            <h5 class="form-check-label text-warning fw-bold d-inline-block ms-2" for="is_driver">
                                <i class="bi bi-truck"></i> تعيين كسائق توصيل
                            </h5>
                        </div>

                        <div id="driver_fields" style="display: none;">
                            <div class="row g-4 text-start">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">رقم الرخصة <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="license_number"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('license_number') }}">
                                    @error('license_number') <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">تاريخ انتهاء الرخصة</label>
                                    <input type="date" name="license_expiry"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('license_expiry') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">نوع المركبة</label>
                                    <input type="text" name="vehicle_type"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('vehicle_type') }}" placeholder="مثال: موتوسيكل، سيارة فان...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small fw-bold">رقم اللوحة / معلومات
                                        المركبة</label>
                                    <input type="text" name="vehicle_plate"
                                        class="form-control bg-surface-secondary bg-opacity-25 text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                                        value="{{ old('vehicle_plate') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4 text-start">
                    <div class="glass-card-deep rounded-4 p-4 mb-4">
                        <h6 class="text-heading fw-bold mb-3">الحالة والوصول</h6>
                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">الحالة</label>
                            <select name="status"
                                class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none" required>
                                @foreach(Modules\HR\Models\Employee::getStatusLabels() as $val => $label)
                                    <option value="{{ $val }}" class="bg-surface-secondary text-body" {{ old('status', 'active') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold">حساب النظام المرتبط (User)</label>
                            <select name="user_id"
                                class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none">
                                <option value="" class="bg-surface-secondary text-secondary">-- اختر مستخدم --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" class="bg-surface-secondary text-body" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-secondary opacity-50 x-small mt-2">
                                <i class="bi bi-info-circle me-1"></i> ربط الموظف بمستخدم يتيح له تسجيل الدخول واستخدام
                                النظام.
                            </div>
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
                // Optional: Set required attributes if needed, but backend validation handles logic
            } else {
                driverFields.style.display = 'none';
            }
        }

        // Run on load to handle validation errors (old input)
        document.addEventListener('DOMContentLoaded', function () {
            toggleDriverFields();
        });
    </script>
@endpush