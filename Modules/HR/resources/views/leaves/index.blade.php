@extends('layouts.app')

@section('title', 'إدارة الإجازات')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6 text-start">
                <h2 class="fw-black text-heading mb-0">إدارة <span class="text-primary">الإجازات</span></h2>
                <p class="text-secondary small opacity-75">مراجعة واعتماد طلبات الإجازات ومتابعة الأرصدة.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-lg border-0"
                    data-bs-toggle="modal" data-bs-target="#createLeaveModal"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <i class="bi bi-plus-lg me-1"></i> تسجيل إجازة جديدة
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="glass-card mb-4 p-3 rounded-4 border-secondary border-opacity-10 border-opacity-10 bg-surface-secondary bg-opacity-25">
            <form action="{{ route('hr.leaves.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="text-secondary x-small fw-bold mb-1">بحث عن موظف</label>
                    <div class="input-group">
                        <span class="input-group-text bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-secondary"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control bg-surface-secondary text-body border-secondary border-opacity-10 border-opacity-10 shadow-none"
                            placeholder="اسم الموظف...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary x-small fw-bold mb-1">الحالة</label>
                    <select name="status" class="form-select bg-surface-secondary text-body border-secondary border-opacity-10 border-opacity-10 shadow-none">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبولة</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100 rounded-pill fw-bold border-0 bg-opacity-25">
                        <i class="bi bi-funnel me-1"></i> تصفية
                    </button>
                </div>
            </form>
        </div>

        <!-- Leaves Table -->
        <div class="glass-card rounded-4 border-secondary border-opacity-10 border-opacity-10 overflow-hidden shadow-lg bg-surface-secondary bg-opacity-40">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-heading text-start">
                    <thead class="bg-white bg-opacity-5 border-bottom border-secondary border-opacity-10 border-opacity-10">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary x-small fw-black text-uppercase">الموظف</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase">نوع الإجازة</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase">التاريخ</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">المدة</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الحالة</th>
                            <th class="pe-4 py-3 border-0 text-secondary x-small fw-black text-uppercase text-end">الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr class="border-bottom border-secondary border-opacity-10 border-opacity-5 transition-hover">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;">
                                            {{ mb_substr($leave->employee->first_name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-6 text-heading">
                                                {{ $leave->employee->full_name ?? 'غير معروف' }}
                                            </div>
                                            <div class="text-secondary x-small opacity-75">
                                                {{ $leave->employee->position ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="d-block fw-bold opacity-90">{{ $leave->leave_type }}</span>
                                    @if($leave->reason)
                                        <span class="d-block text-secondary x-small opacity-50 text-truncate"
                                            style="max-width: 150px;">{{ $leave->reason }}</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span
                                            class="fw-bold fs-7">{{ \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') }}</span>
                                        <span class="text-secondary x-small opacity-50">إلى
                                            {{ \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <span
                                        class="badge bg-white bg-opacity-10 text-body border border-secondary border-opacity-10 border-opacity-10 rounded-pill px-3 py-2 fw-normal">
                                        {{ $leave->total_days }} يوم
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    @if($leave->status == 'pending')
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="bi bi-hourglass-split me-1"></i> قيد الانتظار
                                        </span>
                                    @elseif($leave->status == 'approved')
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i> مقبولة
                                        </span>
                                    @elseif($leave->status == 'rejected')
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i> مرفوضة
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    @if($leave->status == 'pending')
                                        <div class="d-flex justify-content-end gap-2">
                                            <form action="{{ route('hr.leaves.approve', $leave->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-icon btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                    title="قبول" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('hr.leaves.reject', $leave->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-icon btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                    title="رفض" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-secondary opacity-25 d-inline-flex align-items-center gap-1 small">
                                            <i class="bi bi-check-all fs-5"></i> مكتمل
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="opacity-25 mb-3"><i class="bi bi-calendar-x display-1 text-secondary"></i></div>
                                    <h5 class="text-secondary fw-bold">لا توجد طلبات إجازة</h5>
                                    <p class="text-secondary opacity-50 small">لم يتم العثور على أي سجلات مطابقة للبحث الحالي.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leaves->hasPages())
                <div class="p-4 border-top border-secondary border-opacity-10 border-opacity-10">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    </div>
    </div>

    <!-- Create Leave Modal -->
    <div class="modal fade" id="createLeaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-secondary border-opacity-10 border-opacity-10 rounded-4 shadow-lg bg-surface-secondary">
                <div class="modal-header border-bottom border-secondary border-opacity-10 border-opacity-5 p-4">
                    <h5 class="modal-title text-heading fw-bold">تسجيل إجازة جديدة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('hr.leaves.store', ['employee' => 0]) }}" method="POST">
                    @csrf
                    <!-- Note: Route param 'employee' is dummy here, controller handles employee_id from body -->

                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">الموظف <span
                                    class="text-danger">*</span></label>
                            <select name="employee_id"
                                class="form-select bg-surface-secondary text-body border-secondary border-opacity-25 shadow-none"
                                required>
                                <option value="" selected disabled>اختر الموظف...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">نوع الإجازة <span
                                    class="text-danger">*</span></label>
                            <select name="leave_type"
                                class="form-select bg-surface-secondary text-body border-secondary border-opacity-25 shadow-none"
                                required>
                                <option value="annual">إجازة سنوية</option>
                                <option value="sick">إجازة مرضية</option>
                                <option value="unpaid">إجازة غير مدفوعة</option>
                                <option value="emergency">إجازة طارئة</option>
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold">من تاريخ <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="start_date"
                                    class="form-control bg-surface-secondary text-body border-secondary border-opacity-25 shadow-none"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold">إلى تاريخ <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="end_date"
                                    class="form-control bg-surface-secondary text-body border-secondary border-opacity-25 shadow-none"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">سبب الإجازة</label>
                            <textarea name="reason"
                                class="form-control bg-surface-secondary text-body border-secondary border-opacity-25 shadow-none"
                                rows="3" placeholder="اختياري..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-10 border-opacity-5 p-4">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none fw-bold"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill fw-black shadow-lg border-0">حفظ
                            الطلب</button>
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
            font-size: 0.725rem !important;
        }

        .fs-7 {
            font-size: 0.85rem !important;
        }

        .glass-card {
            background: rgba(18, 18, 18, 0.6);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        .transition-hover {
            transition: background-color 0.2s ease;
        }

        .avatar-sm {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(0, 0, 0, 0.5) !important;
            border-color: #0d6efd !important;
            color: white !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Pagination Customization for Dark Mode */
        .pagination .page-link {
            background-color: transparent;
            border-color: rgba(255, 255, 255, 0.1);
            color: #cbd5e0;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            background-color: transparent;
            color: rgba(255, 255, 255, 0.2);
        }
    </style>
@endsection