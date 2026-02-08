@extends('layouts.app')

@section('title', 'سجل الحضور والانصراف')
@section('header', 'إدارة الحضور والانصراف')

@section('content')
    <div class="dashboard-wrapper">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h3 class="fw-black text-white mb-1">سجل <span class="text-primary">الحضور</span></h3>
                <p class="text-secondary small opacity-75">متابعة الانضباط والمواعيد اليومية للموظفين.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                    data-bs-target="#manualLogModal">
                    <i class="bi bi-plus-circle me-1"></i> تسجيل يدوي (HR)
                </button>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="glass-card p-3 rounded-4 border-start border-primary border-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-secondary small mb-1">حضور اليوم</h6>
                            <h3 class="text-white fw-black mb-0">{{ $todayCount ?? 0 }}</h3>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="glass-card rounded-4 p-4 mb-4 shadow-lg">
            <form action="{{ route('hr.attendance.index') }}" method="GET" class="row g-3 align-items-end" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-bold">الموظف</label>
                    <select name="employee_id"
                        class="form-select bg-dark text-white border-secondary border-opacity-25 shadow-none"
                        onchange="document.getElementById('filterForm').submit()">
                        <option value="">كل الموظفين</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary small fw-bold">الحالة</label>
                    <select name="status"
                        class="form-select bg-dark text-white border-secondary border-opacity-25 shadow-none"
                        onchange="document.getElementById('filterForm').submit()">
                        <option value="">كل الحالات</option>
                        @foreach(\Modules\HR\Enums\AttendanceStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary small fw-bold">من تاريخ</label>
                    <input type="date" name="from_date"
                        class="form-control bg-dark text-white border-secondary border-opacity-25 shadow-none"
                        value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary small fw-bold">إلى تاريخ</label>
                    <input type="date" name="to_date"
                        class="form-control bg-dark text-white border-secondary border-opacity-25 shadow-none"
                        value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">
                        <i class="bi bi-filter me-1"></i> تصفية
                    </button>
                    <a href="{{ route('hr.attendance.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Content Table -->
        <div class="glass-card rounded-4 shadow-lg overflow-hidden">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr class="bg-white bg-opacity-5">
                            <th class="px-4 py-3 border-0">الموظف</th>
                            <th class="px-4 py-3 border-0">التاريخ</th>
                            <th class="px-4 py-3 border-0 text-center">حضور</th>
                            <th class="px-4 py-3 border-0 text-center">انصراف</th>
                            <th class="px-4 py-3 border-0 text-center">المدة</th>
                            <th class="px-4 py-3 border-0 text-center">الحالة</th>
                            <th class="px-4 py-3 border-0 text-center">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($attendances as $record)
                            <tr class="align-middle border-bottom border-white border-opacity-5">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                                            style="width: 35px; height: 35px;">
                                            <i class="bi bi-person h6 mb-0"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white small">{{ $record->employee->full_name }}</div>
                                            <div class="text-secondary x-small">{{ $record->employee->position }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary small">{{ $record->attendance_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-center text-success fw-bold small">
                                    {{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '---' }}
                                </td>
                                <td class="px-4 py-3 text-center text-danger fw-bold small">
                                    {{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '---' }}
                                </td>
                                <td class="px-4 py-3 text-center text-info small">{{ $record->duration_formatted }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusEnum = $record->status instanceof \Modules\HR\Enums\AttendanceStatus ? $record->status : \Modules\HR\Enums\AttendanceStatus::tryFrom($record->status);
                                        $color = $statusEnum ? $statusEnum->color() : 'secondary';
                                        $label = $statusEnum ? $statusEnum->label() : $record->status;
                                    @endphp
                                    <span class="badge bg-{{ $color }} rounded-pill px-3 py-1 x-small fw-bold">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-secondary x-small opacity-75">{{ $record->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="opacity-25 pb-3"><i class="bi bi-calendar-x display-4"></i></div>
                                    <h6 class="text-secondary">لا توجد سجلات حضور مطابقة لهذا البحث.</h6>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
                <div class="p-4 border-top border-white border-opacity-5">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Manual Log Modal -->
    <div class="modal fade" id="manualLogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-white border-opacity-10 rounded-4">
                <div class="modal-header border-bottom border-white border-opacity-10 p-4">
                    <h5 class="modal-title text-white fw-bold">تسجيل حضور يدوي (HR)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('hr.attendance.manual-log') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">اختيار الموظف</label>
                            <select name="employee_id"
                                class="form-select bg-dark text-white border-secondary border-opacity-25" required>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}
                                        ({{ $employee->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label text-secondary small fw-bold">تاريخ الحضور</label>
                                <input type="date" name="attendance_date"
                                    class="form-control bg-dark text-white border-secondary border-opacity-25" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold">وقت الحضور</label>
                                <input type="time" name="clock_in"
                                    class="form-control bg-dark text-white border-secondary border-opacity-25">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold">وقت الانصراف</label>
                                <input type="time" name="clock_out"
                                    class="form-control bg-dark text-white border-secondary border-opacity-25">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">الحالة</label>
                            <select name="status" class="form-select bg-dark text-white border-secondary border-opacity-25"
                                required>
                                @foreach(\Modules\HR\Enums\AttendanceStatus::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary small fw-bold">ملاحظات السبب</label>
                            <textarea name="notes"
                                class="form-control bg-dark text-white border-secondary border-opacity-25" rows="2"
                                placeholder="أدخل سبب التسجيل اليدوي..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white border-opacity-10 p-4">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none fw-bold"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">حفظ السجل</button>
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

        :root {
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
        }

        .pagination .page-link {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
    </style>
@endsection