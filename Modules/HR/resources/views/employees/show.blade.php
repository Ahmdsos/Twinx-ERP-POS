@extends('layouts.app')

@section('title', 'ملف الموظف')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Back Button -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12 text-start">
                <a href="{{ route('hr.employees.index') }}"
                    class="btn btn-icon-box bg-primary bg-opacity-20 text-white rounded-3 mb-2 d-inline-flex align-items-center justify-content-center border border-primary border-opacity-25">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div class="d-flex align-items-center gap-3">
                    <h2 class="fw-black text-white mb-0 mt-2">{{ $employee->full_name }}</h2>
                    <span
                        class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 mt-2">
                        {{ $employee->employee_code }}
                    </span>
                </div>
                <p class="text-secondary small opacity-75">{{ $employee->position }} | {{ $employee->department }}</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sidebar: Quick Info -->
            <div class="col-lg-4 text-start">
                <div class="glass-card-deep rounded-4 p-4 mb-4">
                    <div class="text-center mb-4">
                        <div class="avatar-huge rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto mb-3 border border-primary border-opacity-20 shadow-lg"
                            style="width: 120px; height: 120px; font-size: 3rem;">
                            {{ mb_substr($employee->first_name, 0, 1) }}
                        </div>
                        <h4 class="text-white fw-bold mb-1">{{ $employee->full_name }}</h4>
                        @php $color = Modules\HR\Models\Employee::getStatusColors()[$employee->status] ?? 'secondary'; @endphp
                        <span
                            class="badge bg-{{ $color }} bg-opacity-20 text-{{ $color }} border border-{{ $color }} border-opacity-25 rounded-pill px-3 py-2 x-small fw-bold mb-4">
                            {{ Modules\HR\Models\Employee::getStatusLabels()[$employee->status] ?? $employee->status }}
                        </span>
                    </div>

                    <div class="info-list">
                        <div
                            class="info-item d-flex justify-content-between py-2 border-bottom border-white border-opacity-5">
                            <span class="text-secondary small fw-bold">البريد الإلكتروني:</span>
                            <span class="text-white small">{{ $employee->email }}</span>
                        </div>
                        <div
                            class="info-item d-flex justify-content-between py-2 border-bottom border-white border-opacity-5">
                            <span class="text-secondary small fw-bold">رقم الهاتف:</span>
                            <span class="text-white small">{{ $employee->phone }}</span>
                        </div>
                        <div
                            class="info-item d-flex justify-content-between py-2 border-bottom border-white border-opacity-5">
                            <span class="text-secondary small fw-bold">تاريخ التعيين:</span>
                            <span
                                class="text-white small">{{ $employee->date_of_joining ? $employee->date_of_joining->format('Y-m-d') : '---' }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-secondary small fw-bold">الراتب الأساسي:</span>
                            <span class="text-primary fw-black">{{ number_format($employee->basic_salary, 2) }} ج.م</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('hr.employees.edit', $employee->id) }}"
                            class="btn btn-primary rounded-pill fw-bold border-0 shadow-sm py-2">
                            <i class="bi bi-pencil-square me-2"></i> تعديل البيانات
                        </a>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="glass-card-deep rounded-4 p-4 mb-4 border-start border-warning border-4">
                    <h6 class="text-warning fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock"></i> جهة اتصال الطوارئ
                    </h6>
                    <div class="info-item mb-2 text-start">
                        <div class="text-secondary x-small">الاسم:</div>
                        <div class="text-white fw-bold">{{ $employee->emergency_contact_name ?? 'غير مسجل' }}</div>
                    </div>
                    <div class="info-item text-start">
                        <div class="text-secondary x-small">الهاتف:</div>
                        <div class="text-white fw-bold">{{ $employee->emergency_contact_phone ?? 'غير مسجل' }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Content: Tabs -->
            <div class="col-lg-8">
                <div class="glass-card-deep rounded-4 p-2 mb-4">
                    <nav>
                        <div class="nav nav-pills nav-fill p-1" id="nav-tab" role="tablist">
                            <button class="nav-link active rounded-pill text-white fw-bold" id="nav-personal-tab"
                                data-bs-toggle="tab" data-bs-target="#nav-personal" type="button" role="tab">البيانات
                                التفصيلية</button>
                            <button class="nav-link rounded-pill text-white fw-bold" id="nav-documents-tab"
                                data-bs-toggle="tab" data-bs-target="#nav-documents" type="button"
                                role="tab">الوثائق</button>
                            <button class="nav-link rounded-pill text-white fw-bold" id="nav-payroll-tab"
                                data-bs-toggle="tab" data-bs-target="#nav-payroll" type="button" role="tab">سجل
                                الرواتب</button>
                            <button class="nav-link rounded-pill text-white fw-bold" id="nav-leaves-tab"
                                data-bs-toggle="tab" data-bs-target="#nav-leaves" type="button" role="tab">الإجازات</button>
                        </div>
                    </nav>
                </div>

                <div class="tab-content" id="nav-tabContent">
                    <!-- Tab 1: Personal Info -->
                    <div class="tab-pane fade show active" id="nav-personal" role="tabpanel">
                        <div class="glass-card-deep rounded-4 p-4 text-start">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary fw-bold mb-3">البيانات البنكية</h6>
                                    <div
                                        class="p-3 rounded-3 bg-primary bg-opacity-5 border border-primary border-opacity-10">
                                        <div class="mb-2">
                                            <div class="text-secondary x-small">اسم البنك:</div>
                                            <div class="text-white fw-bold">{{ $employee->bank_name ?? '---' }}</div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="text-secondary x-small">رقم الحساب:</div>
                                            <div class="text-white fw-bold">{{ $employee->bank_account_number ?? '---' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-secondary x-small">IBAN:</div>
                                            <div class="text-white fw-bold">{{ $employee->iban ?? '---' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary fw-bold mb-3">بيانات التعاقد</h6>
                                    <div
                                        class="p-3 rounded-3 bg-primary bg-opacity-5 border border-primary border-opacity-10">
                                        <div class="mb-2">
                                            <div class="text-secondary x-small">نوع العقد:</div>
                                            <div class="text-white fw-bold">{{ $employee->contract_type ?? 'دوام كامل' }}
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="text-secondary x-small">رقم الهوية:</div>
                                            <div class="text-white fw-bold">{{ $employee->id_number ?? '---' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-secondary x-small">العنوان:</div>
                                            <div class="text-white small">{{ $employee->address ?? '---' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other tabs would go here, omitting for brevity to focus on recovery -->
                    <div class="tab-pane fade" id="nav-documents" role="tabpanel">
                        <div class="glass-card-deep rounded-4 p-4 text-center">
                            <i class="bi bi-files display-4 text-secondary opacity-25 mb-3"></i>
                            <p class="text-secondary">مركز الوثائق قيد التطوير أو لا توجد وثائق حالياً.</p>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-payroll" role="tabpanel">
                        <div class="glass-card-deep rounded-4 p-4 text-center">
                            <i class="bi bi-wallet2 display-4 text-secondary opacity-25 mb-3"></i>
                            <p class="text-secondary">سجل الرواتب التفصيلي للموظف سيتم عرضه هنا.</p>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-leaves" role="tabpanel">
                        <div class="glass-card-deep rounded-4 p-4 text-center">
                            <i class="bi bi-calendar-event display-4 text-secondary opacity-25 mb-3"></i>
                            <p class="text-secondary">سجل الإجازات والطلبات المعلقة.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-black {
            font-weight: 900 !important;
        }

        .x-small {
            font-size: 0.725rem !important;
        }

        .glass-card-deep {
            background: rgba(10, 15, 30, 0.98) !important;
            backdrop-filter: blur(25px) saturate(160%);
            -webkit-backdrop-filter: blur(25px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.7);
        }

        .text-secondary {
            color: #cbd5e0 !important;
            opacity: 1 !important;
        }

        .nav-pills .nav-link {
            color: #cbd5e0 !important;
        }

        .nav-pills .nav-link.active {
            background: #0d6efd !important;
            color: #fff !important;
        }

        .btn-icon-box {
            width: 40px;
            height: 40px;
            transition: all 0.2s;
        }

        .btn-icon-box:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection