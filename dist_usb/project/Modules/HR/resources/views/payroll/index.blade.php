@extends('layouts.app')

@section('title', 'مسيرات الرواتب')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Action -->
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6 text-start">
                <h2 class="text-heading fw-black mb-0">إدارة <span class="text-primary">الرواتب</span></h2>
                <p class="text-secondary small mt-2 opacity-75">توليد المسيرات الشهرية، مراجعة الاستحقاقات، والترحيل
                    للحسابات العامة.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button
                    class="btn btn-primary btn-lg rounded-pill px-4 shadow-lg border-0 d-inline-flex align-items-center gap-2"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);" data-bs-toggle="modal"
                    data-bs-target="#generatePayrollModal">
                    <i class="bi bi-gear-wide-connected fs-5"></i>
                    <span class="fw-bold">توليد مسيرة جديدة</span>
                </button>
            </div>
        </div>

        <!-- Payroll Table -->
        <div class="glass-card rounded-4 border-secondary border-opacity-10 border-opacity-10 overflow-hidden shadow-lg bg-surface-secondary bg-opacity-40">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-heading text-start">
                    <thead>
                        <tr class="bg-white bg-opacity-5 border-bottom border-secondary border-opacity-10 border-opacity-10">
                            <th class="ps-4 py-3 border-0 text-secondary x-small fw-black text-uppercase">الشهر / السنة</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">إجمالي
                                الرواتب</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">البدلات
                            </th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الخصومات
                            </th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الصافي
                                الإجمالي</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الحالة</th>
                            <th class="pe-4 py-3 border-0 text-secondary x-small fw-black text-uppercase text-end">الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                            <tr class="border-bottom border-secondary border-opacity-10 border-opacity-5">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 bg-primary bg-opacity-10 rounded-2 text-primary">
                                            <i class="bi bi-calendar-check fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                {{ \Carbon\Carbon::create()->month($payroll->month)->translatedFormat('F') }}
                                            </div>
                                            <div class="text-secondary small opacity-50">{{ $payroll->year }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center opacity-75">
                                    {{ number_format($payroll->items->sum('basic_salary'), 2) }}</td>
                                <td class="py-3 text-center text-success">+ {{ number_format($payroll->total_allowances, 2) }}
                                </td>
                                <td class="py-3 text-center text-danger">- {{ number_format($payroll->total_deductions, 2) }}
                                </td>
                                <td class="py-3 text-center fw-black text-primary">
                                    {{ number_format($payroll->net_salary ?? 0, 2) }}</td>
                                <td class="py-3 text-center">
                                    @php $color = $payroll->status == 'processed' ? 'success' : ($payroll->status == 'posted' ? 'info' : 'warning'); @endphp
                                    <span
                                        class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 rounded-pill px-3 py-2 x-small fw-bold">
                                        {{ Modules\HR\Models\Payroll::getStatusLabels()[$payroll->status] ?? $payroll->status }}
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <a href="{{ route('hr.payroll.show', $payroll->id) }}"
                                        class="btn btn-icon-box bg-white bg-opacity-10 text-body rounded-3 shadow-none">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="opacity-25 mb-3"><i class="bi bi-wallet2 display-1 text-secondary"></i></div>
                                    <h5 class="text-secondary">لا توجد مسيرات رواتب مسجلة حالياً</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Generate Payroll Modal -->
    <div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-secondary border-opacity-10 border-opacity-10 rounded-4 shadow-lg bg-surface-secondary">
                <div class="modal-header border-bottom border-secondary border-opacity-10 border-opacity-5 p-4">
                    <h5 class="modal-title text-heading fw-bold">توليد كشف رواتب جديد</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('hr.payroll.generate') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 text-start">
                        <p class="text-secondary small mb-4">اختر الشهر والسنة لتوليد مسيرة الرواتب. سيقوم النظام بحساب
                            الحضور والانصراف والخصومات تلقائياً.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">الشهر</label>
                                <select name="month"
                                    class="form-select bg-white bg-opacity-5 border-secondary border-opacity-10 border-opacity-10 text-body shadow-none"
                                    required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" class="bg-surface-secondary" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">السنة</label>
                                <select name="year"
                                    class="form-select bg-white bg-opacity-5 border-secondary border-opacity-10 border-opacity-10 text-body shadow-none"
                                    required>
                                    @for($y = date('Y'); $y >= 2023; $y--)
                                        <option value="{{ $y }}" class="bg-surface-secondary">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-10 border-opacity-5 p-4">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none fw-bold"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill fw-black shadow-lg border-0">ابدأ
                            الاحتساب الآن</button>
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

        .glass-card {
            background: rgba(18, 18, 18, 0.5);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
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

        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #0d6efd;
            color: white;
        }
    </style>
@endsection