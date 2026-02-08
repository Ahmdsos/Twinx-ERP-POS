@extends('layouts.app')

@section('title', $employee->full_name)
@section('header', 'ملف الموظف')

@section('content')
    <div class="container-fluid py-2">
        <!-- Top Header: Compact Name & Actions -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('hr.employees.index') }}" class="btn btn-sm btn-icon btn-outline-light rounded-circle opacity-50">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h3 class="fw-black text-white mb-0 d-flex align-items-center gap-2">
                        {{ $employee->full_name }}
                        <span class="badge bg-white bg-opacity-10 text-white fw-normal fs-6 rounded-pill px-2 py-1 border border-white border-opacity-10">
                            {{ $employee->employee_code }}
                        </span>
                        @if($employee->deliveryDriver)
                            <span class="badge bg-warning text-dark fw-bold fs-6 rounded-pill">
                                <i class="bi bi-truck me-1"></i> سائق
                            </span>
                        @endif
                    </h3>
                    <div class="text-secondary small mt-1 d-flex align-items-center gap-3">
                        <span><i class="bi bi-briefcase me-1"></i> {{ $employee->position }}</span>
                        <span class="opacity-25">|</span>
                        <span><i class="bi bi-building me-1"></i> {{ $employee->department ?? 'غير محدد' }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-lg">
                    <i class="bi bi-pencil-square me-2"></i> تعديل
                </a>
            </div>
        </div>

        <!-- Summary Cards Row (High Density) -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Status & Joining -->
            <div class="col-md-3">
                <div class="glass-card h-100 p-3 position-relative overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="icon-box-sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        @php
                            $status = $employee->status;
                            $color = $status instanceof \Modules\HR\Enums\EmployeeStatus ? $status->color() : 'secondary';
                            $label = $status instanceof \Modules\HR\Enums\EmployeeStatus ? $status->label() : $status;
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-25 text-{{ $color }} border border-{{ $color }} border-opacity-25 rounded-pill">
                            {{ $label }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="text-secondary x-small">تاريخ التعيين</div>
                        <div class="fw-bold text-white fs-5">{{ $employee->date_of_joining ? $employee->date_of_joining->format('d M Y') : '-' }}</div>
                        <div class="text-secondary x-small opacity-75 mt-1">
                            {{ $employee->date_of_joining ? $employee->date_of_joining->diffForHumans() : '' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Financials -->
            <div class="col-md-3">
                <div class="glass-card h-100 p-3 text-white">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="icon-box-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <span class="text-secondary x-small fw-bold">المستحقات المالية</span>
                    </div>
                    <div class="fw-black fs-4 text-info">{{ number_format($employee->basic_salary, 0) }} <span class="fs-6 text-secondary fw-normal">ج.م</span></div>
                    <div class="d-flex justify-content-between mt-2 pt-2 border-top border-white border-opacity-10">
                        <span class="x-small text-secondary">{{ $employee->contract_type }}</span>
                        <span class="x-small text-secondary">{{ $employee->bank_name ?? 'نقدي' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: User Link -->
            <div class="col-md-3">
                <div class="glass-card h-100 p-3 text-white">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="icon-box-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <span class="text-secondary x-small fw-bold">حساب النظام</span>
                    </div>
                    @if($employee->user)
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <div class="avatar-xs bg-primary rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                {{ mb_substr($employee->user->name, 0, 1) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-truncate">{{ $employee->user->name }}</div>
                                <div class="x-small text-secondary text-truncate">{{ $employee->user->email }}</div>
                            </div>
                        </div>
                    @else
                        <div class="mt-2 text-warning x-small d-flex align-items-center gap-1">
                            <i class="bi bi-exclamation-triangle"></i>
                            غير مرتبط بمستخدم
                        </div>
                        <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn btn-sm btn-link p-0 text-primary x-small text-decoration-none">ربط الآن</a>
                    @endif
                </div>
            </div>

            <!-- Card 4: Driver / Contact -->
            <div class="col-md-3">
                @if($employee->deliveryDriver)
                    <div class="glass-card h-100 p-3 position-relative overflow-hidden border-start border-warning border-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-box-sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <span class="text-secondary x-small fw-bold">بيانات السائق</span>
                            </div>
                            <span class="badge bg-warning text-dark x-small">{{ $employee->deliveryDriver->status->label() }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary x-small">الرخصة:</span>
                                <span class="text-white x-small fw-bold">{{ $employee->deliveryDriver->license_number }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary x-small">المركبة:</span>
                                <span class="text-white x-small fw-bold text-truncate" style="max-width: 100px;" title="{{ $employee->deliveryDriver->vehicle_info }}">{{ $employee->deliveryDriver->vehicle_info }}</span>
                            </div>
                        </div>
                    </div>
                @else
                     <div class="glass-card h-100 p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="icon-box-sm bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <span class="text-secondary x-small fw-bold">الطوارئ</span>
                        </div>
                        <div class="fw-bold text-white text-truncate">{{ $employee->emergency_contact_name ?? '---' }}</div>
                        <div class="x-small text-secondary">{{ $employee->emergency_contact_phone ?? '---' }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3">
            <!-- Left Sidebar: Personal Details -->
            <div class="col-lg-3">
                <div class="glass-card rounded-4 p-3 mb-3">
                    <h6 class="text-white fw-bold mb-3 border-bottom border-white border-opacity-10 pb-2">
                        <i class="bi bi-person-lines-fill me-2 text-primary"></i> بيانات التواصل
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <label class="d-block text-secondary x-small mb-1">البريد الإلكتروني</label>
                            <div class="text-white small text-break">{{ $employee->email }}</div>
                        </li>
                        <li class="mb-3">
                            <label class="d-block text-secondary x-small mb-1">رقم الهاتف</label>
                            <div class="text-white small font-monospace">{{ $employee->phone }}</div>
                        </li>
                        <li class="mb-3">
                            <label class="d-block text-secondary x-small mb-1">العنوان</label>
                            <div class="text-white small">{{ $employee->address ?? '---' }}</div>
                        </li>
                        <li class="mb-3">
                            <label class="d-block text-secondary x-small mb-1">رقم الهوية</label>
                            <div class="text-white small font-monospace">{{ $employee->id_number ?? '---' }}</div>
                        </li>
                        <li>
                            <label class="d-block text-secondary x-small mb-1">تاريخ الميلاد</label>
                            <div class="text-white small">{{ $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '---' }}</div>
                        </li>
                    </ul>
                </div>

                <div class="glass-card rounded-4 p-3">
                    <h6 class="text-white fw-bold mb-3 border-bottom border-white border-opacity-10 pb-2">
                        <i class="bi bi-bank me-2 text-info"></i> بيانات بنكية
                    </h6>
                     <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-secondary x-small">البنك</span>
                            <span class="text-white x-small fw-bold">{{ $employee->bank_name ?? '-' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary x-small d-block">رقم الحساب</span>
                            <span class="text-white x-small font-monospace">{{ $employee->bank_account_number ?? '-' }}</span>
                        </li>
                        <li>
                            <span class="text-secondary x-small d-block">IBAN</span>
                            <span class="text-white x-small font-monospace">{{ $employee->iban ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Content: Tabs -->
            <div class="col-lg-9">
                <div class="glass-card rounded-4 p-0 overflow-hidden h-100">
                    <div class="p-2 border-bottom border-white border-opacity-10">
                        <ul class="nav nav-pills nav-fill gap-2" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill py-2 small fw-bold" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-selected="true">
                                    <i class="bi bi-clock-history me-1"></i> سجل الحضور
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill py-2 small fw-bold" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button" role="tab" aria-selected="false">
                                    <i class="bi bi-wallet2 me-1"></i> الرواتب
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill py-2 small fw-bold" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button" role="tab" aria-selected="false">
                                    <i class="bi bi-calendar-event me-1"></i> الإجازات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill py-2 small fw-bold" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab" aria-selected="false">
                                    <i class="bi bi-file-earmark-text me-1"></i> الوظائف
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content p-3" id="myTabContent">
                        <!-- Attendance Tab -->
                        <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm text-white mb-0 align-middle">
                                    <thead class="text-secondary x-small text-uppercase">
                                        <tr>
                                            <th class="border-0">التاريخ</th>
                                            <th class="border-0">الدخول</th>
                                            <th class="border-0">الخروج</th>
                                            <th class="border-0">الحالة</th>
                                            <th class="border-0">ملاحظات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->attendance as $record)
                                            <tr>
                                                <td class="border-white border-opacity-5 fw-bold x-small">{{ $record->attendance_date->format('Y-m-d') }}</td>
                                                <td class="border-white border-opacity-5 x-small">{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '--' }}</td>
                                                <td class="border-white border-opacity-5 x-small">{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '--' }}</td>
                                                <td class="border-white border-opacity-5">
                                                    @php
                                                        $statusEnum = $record->status instanceof \Modules\HR\Enums\AttendanceStatus ? $record->status : \Modules\HR\Enums\AttendanceStatus::tryFrom($record->status);
                                                        $color = $statusEnum ? $statusEnum->color() : 'secondary';
                                                        $label = $statusEnum ? $statusEnum->label() : $record->status;
                                                    @endphp
                                                    <span class="badge bg-{{ $color }} bg-opacity-25 text-{{ $color }} x-small px-2 py-1 rounded-1">{{ $label }}</span>
                                                </td>
                                                <td class="border-white border-opacity-5 x-small text-secondary text-truncate" style="max-width: 150px;">{{ $record->notes ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-secondary opacity-50">
                                                    <i class="bi bi-calendar-x d-block fs-4 mb-2"></i>
                                                    لا توجد سجلات حضور
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payroll Tab -->
                        <div class="tab-pane fade" id="payroll" role="tabpanel">
                             <div class="table-responsive">
                                <table class="table table-hover table-sm text-white mb-0 align-middle">
                                    <thead class="text-secondary x-small text-uppercase">
                                        <tr>
                                            <th class="border-0">الفترة</th>
                                            <th class="border-0">أساسي</th>
                                            <th class="border-0 text-success">إضافي</th>
                                            <th class="border-0 text-danger">خصم</th>
                                            <th class="border-0 fw-bold">الصافي</th>
                                            <th class="border-0">الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->payrollItems as $item)
                                            <tr>
                                                <td class="border-white border-opacity-5 fw-bold x-small">{{ $item->payroll->month }}/{{ $item->payroll->year }}</td>
                                                <td class="border-white border-opacity-5 x-small">{{ number_format($item->basic_salary, 2) }}</td>
                                                <td class="border-white border-opacity-5 text-success x-small">+{{ number_format($item->allowances, 2) }}</td>
                                                <td class="border-white border-opacity-5 text-danger x-small">-{{ number_format($item->deductions, 2) }}</td>
                                                <td class="border-white border-opacity-5 fw-black text-info x-small">{{ number_format($item->net_salary, 2) }}</td>
                                                <td class="border-white border-opacity-5">
                                                    <span class="badge bg-success bg-opacity-25 text-success x-small px-2 py-1 rounded-1">مدفوع</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-secondary opacity-50">
                                                    <i class="bi bi-wallet2 d-block fs-4 mb-2"></i>
                                                    لا توجد بيانات رواتب
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Leaves Tab -->
                        <div class="tab-pane fade" id="leaves" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm text-white mb-0 align-middle">
                                    <thead class="text-secondary x-small text-uppercase">
                                        <tr>
                                            <th class="border-0">النوع</th>
                                            <th class="border-0">من</th>
                                            <th class="border-0">إلى</th>
                                            <th class="border-0 text-center">الأيام</th>
                                            <th class="border-0">الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->leaves as $leave)
                                            <tr>
                                                <td class="border-white border-opacity-5 fw-bold x-small">{{ $leave->type }}</td>
                                                <td class="border-white border-opacity-5 x-small">{{ $leave->start_date->format('Y-m-d') }}</td>
                                                <td class="border-white border-opacity-5 x-small">{{ $leave->end_date->format('Y-m-d') }}</td>
                                                <td class="border-white border-opacity-5 text-center x-small">{{ $leave->days }}</td>
                                                <td class="border-white border-opacity-5">
                                                    @php
                                                        $color = match ($leave->status) {
                                                            'approved' => 'success',
                                                            'pending' => 'warning',
                                                            'rejected' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $color }} bg-opacity-25 text-{{ $color }} x-small px-2 py-1 rounded-1">{{ $leave->status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-secondary opacity-50">
                                                    <i class="bi bi-cup-hot d-block fs-4 mb-2"></i>
                                                    لا توجد إجازات
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Docs Tab -->
                        <div class="tab-pane fade" id="docs" role="tabpanel">
                            @if($employee->documents->count() > 0)
                                <div class="row g-2">
                                    @foreach($employee->documents as $doc)
                                        <div class="col-md-4">
                                            <div class="p-2 border border-white border-opacity-10 rounded-2 bg-white bg-opacity-5 d-flex align-items-center gap-2">
                                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                                                <div class="overflow-hidden flex-grow-1">
                                                    <div class="text-white x-small fw-bold text-truncate" title="{{ $doc->title }}">{{ $doc->title }}</div>
                                                    <div class="text-secondary x-small opacity-75">{{ $doc->created_at->format('Y-m-d') }}</div>
                                                </div>
                                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-icon btn-outline-light rounded-circle opacity-50"><i class="bi bi-download"></i></a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-secondary opacity-50">
                                    <i class="bi bi-folder2-open d-block fs-4 mb-2"></i>
                                    لا توجد وثائق
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Compact Glass Theme */
    .glass-card {
        background: rgba(20, 20, 30, 0.95) !important; /* Darker, more opaque */
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    }
    
    .fw-black { font-weight: 900 !important; }
    .x-small { font-size: 0.75rem !important; }
    .icon-box-sm { width: 32px; height: 32px; font-size: 1rem; }
    
    /* Text overrides for dark mode */
    .text-secondary {
        color: #b0b8c4 !important; /* Lighter gray for readability */
    }
    
    /* Nav Pills Optimized */
    .nav-pills .nav-link {
        color: #b0b8c4;
        background: transparent;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .nav-pills .nav-link:hover {
        color: #fff;
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.1);
    }
    .nav-pills .nav-link.active {
        color: #fff !important;
        background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
        box-shadow: 0 4px 15px rgba(13,110,253,0.4);
        border-color: transparent;
    }
    
    /* Table tweaks */
    .table-hover tbody tr:hover {
        background-color: rgba(255,255,255,0.05);
    }
    
    .btn-icon {
        width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;
    }
</style>
@endsection