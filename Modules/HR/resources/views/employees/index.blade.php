@extends('layouts.app')

@section('title', 'إدارة الموظفين')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Stats -->
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6 text-start">
                <h2 class="text-heading fw-black mb-0">إدارة <span class="text-primary">الموظفين</span></h2>
                <p class="text-secondary small mt-2 opacity-75">إدارة بيانات الفريق، الرواتب، والوثائق الرسمية في مكان واحد.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('hr.employees.create') }}"
                    class="btn btn-primary btn-lg rounded-pill px-4 shadow-lg border-0 d-inline-flex align-items-center gap-2"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <i class="bi bi-person-plus-fill fs-5"></i>
                    <span class="fw-bold">إضافة موظف جديد</span>
                </a>
            </div>
        </div>

        <!-- Metrics Bar -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-3 rounded-4 border-secondary border-opacity-10 border-opacity-10 h-100 bg-surface-secondary bg-opacity-25">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-primary"><i
                                class="bi bi-people-fill fs-4"></i></div>
                        <div>
                            <div class="text-secondary x-small fw-bold opacity-75">إجمالي الموظفين</div>
                            <div class="h4 text-heading mb-0">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-3 rounded-4 border-secondary border-opacity-10 border-opacity-10 h-100 bg-surface-secondary bg-opacity-25">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3 text-success"><i
                                class="bi bi-check-circle-fill fs-4"></i></div>
                        <div>
                            <div class="text-secondary x-small fw-bold opacity-75">نشط حالياً</div>
                            <div class="h4 text-heading mb-0">{{ $stats['active'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-3 rounded-4 border-secondary border-opacity-10 border-opacity-10 h-100 bg-surface-secondary bg-opacity-25">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-warning"><i
                                class="bi bi-clock-history fs-4"></i></div>
                        <div>
                            <div class="text-secondary x-small fw-bold opacity-75">في إجازة</div>
                            <div class="h4 text-heading mb-0">{{ $stats['on_leave'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-3 rounded-4 border-secondary border-opacity-10 border-opacity-10 h-100 bg-surface-secondary bg-opacity-25">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-info bg-opacity-10 rounded-3 text-info"><i class="bi bi-wallet2 fs-4"></i></div>
                        <div>
                            <div class="text-secondary x-small fw-bold opacity-75">كتلة الرواتب</div>
                            <div class="h4 text-heading mb-0">{{ number_format($stats['total_salaries'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table -->
        <div class="glass-card rounded-4 border-secondary border-opacity-10 border-opacity-10 overflow-hidden shadow-lg bg-surface-secondary bg-opacity-40">
            <!-- Filter Header -->
            <div class="p-4 border-bottom border-secondary border-opacity-10 border-opacity-5 bg-surface-secondary bg-opacity-25">
                <form action="{{ route('hr.employees.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span
                                class="input-group-text bg-white bg-opacity-5 border-secondary border-opacity-10 border-opacity-10 text-secondary border-end-0 rounded-start-pill ps-3"><i
                                    class="bi bi-search"></i></span>
                            <input type="text" name="search"
                                class="form-control bg-white bg-opacity-5 border-secondary border-opacity-10 border-opacity-10 text-body shadow-none rounded-end-pill py-2"
                                placeholder="ابحث بالاسم، الكود، أو القسم..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 text-start">
                        <select name="status"
                            class="form-select bg-surface-secondary border-secondary border-opacity-10 border-opacity-10 text-body shadow-none rounded-pill py-2">
                            <option value="">كل الحالات...</option>
                            @foreach(Modules\HR\Models\Employee::getStatusLabels() as $val => $label)
                                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="department"
                            class="form-control bg-white bg-opacity-5 border-secondary border-opacity-10 border-opacity-10 text-body shadow-none rounded-pill py-2"
                            placeholder="القسم..." value="{{ request('department') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit"
                            class="btn btn-secondary w-100 rounded-pill py-2 fw-bold border-0 bg-white bg-opacity-10 text-body">
                            <i class="bi bi-funnel me-1"></i> تصفية
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Content -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-heading text-start">
                    <thead>
                        <tr class="bg-white bg-opacity-5 border-bottom border-secondary border-opacity-10 border-opacity-10">
                            <th class="ps-4 py-3 border-0 text-secondary x-small fw-black text-uppercase">الموظف</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase">الكود</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">القسم /
                                المنصب</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الراتب
                                الأساسي</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الحالة</th>
                            <th class="pe-4 py-3 border-0 text-secondary x-small fw-black text-uppercase text-end">الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr class="border-bottom border-secondary border-opacity-10 border-opacity-5">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-box rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                            style="width: 40px; height: 40px;">
                                            {{ mb_substr($employee->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-6">{{ $employee->full_name }}</div>
                                            <div class="text-secondary x-small opacity-50">{{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span
                                        class="badge bg-white bg-opacity-10 text-body px-3 py-2 rounded-pill x-small fw-bold">
                                        {{ $employee->employee_code }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="small fw-bold">{{ $employee->department }}</div>
                                    <div class="x-small text-secondary opacity-75">{{ $employee->position }}</div>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="fw-black text-primary">{{ number_format($employee->basic_salary, 2) }}</div>
                                    <div class="x-small text-secondary opacity-50 text-heading">ج.م</div>
                                </td>
                                <td class="py-3 text-center">
                                    <span
                                        class="badge bg-{{ $employee->current_status->color() }} bg-opacity-10 text-{{ $employee->current_status->color() }} border border-{{ $employee->current_status->color() }} border-opacity-25 rounded-pill px-3 py-2 x-small fw-bold">
                                        {{ $employee->current_status->label() }}
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('hr.employees.show', $employee->id) }}"
                                            class="btn btn-icon-box bg-primary bg-opacity-10 text-primary rounded-3"
                                            title="عرض">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </a>
                                        <a href="{{ route('hr.employees.edit', $employee->id) }}"
                                            class="btn btn-icon-box bg-info bg-opacity-10 text-info rounded-3" title="تعديل">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('hr.employees.destroy', $employee->id) }}" method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-icon-box bg-danger bg-opacity-10 text-danger rounded-3"
                                                title="حذف">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="opacity-25 mb-3"><i class="bi bi-people display-1 text-secondary"></i></div>
                                    <h5 class="text-secondary">لا يوجد موظفين مسجلين حالياً</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="p-4 border-top border-secondary border-opacity-10 border-opacity-5">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .fw-black {
            font-weight: 900 !important;
        }

        .x-small {
            font-size: 0.725rem !important;
        }

        .glass-card {
            background: rgba(10, 15, 30, 0.95) !important;
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
        }

        .text-secondary {
            color: #cbd5e0 !important;
            /* Brighter gray */
            opacity: 1 !important;
        }

        .btn-icon-box {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-icon-box:hover {
            transform: translateY(-2px);
            border-color: currentColor;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .form-control,
        .form-select {
            background-color: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .table thead th {
            color: #90cdf4 !important;
            font-weight: 800 !important;
        }
    </style>
@endsection