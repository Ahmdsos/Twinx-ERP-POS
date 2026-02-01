@extends('layouts.app')

@section('title', 'تقارير الموارد البشرية')
@section('header', 'تقارير الموارد البشرية')

@section('content')
    <div class="dashboard-wrapper">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h3 class="fw-black text-white mb-0">مركز التقارير الإدارية</h3>
                <p class="text-secondary small opacity-75 mt-1">توليد تقارير تحليلية دقيقة للحضور، الانصراف، والرواتب لفترات
                    زمنية محددة.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="glass-card rounded-4 border-white border-opacity-10 overflow-hidden shadow-lg">
                    <div class="card-header bg-white bg-opacity-5 py-4 border-bottom border-white border-opacity-10 px-4">
                        <h5 class="mb-0 text-white fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-bar-graph text-primary"></i> معايير استخراج التقرير
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('hr.reports.generate') }}" method="GET" target="_blank">
                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold">تحليل الفترة الزمنية <span
                                        class="text-danger">*</span></label>
                                <div class="row g-3">
                                    <div class="col-6 text-start">
                                        <label class="x-small text-secondary mb-1">من تاريخ</label>
                                        <input type="date" name="from_date"
                                            class="form-control bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none h-48"
                                            required value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-6 text-start">
                                        <label class="x-small text-secondary mb-1">إلى تاريخ</label>
                                        <input type="date" name="to_date"
                                            class="form-control bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none h-48"
                                            required value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label text-secondary small fw-bold">تحديد الموظف (اختياري)</label>
                                <select name="employee_id"
                                    class="form-select bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none h-48">
                                    <option value="" class="bg-dark">استخراج لكل طاقم العمل</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" class="bg-dark">{{ $employee->full_name }}
                                            ({{ $employee->employee_code }})</option>
                                    @endforeach
                                </select>
                                <div class="x-small text-secondary mt-2 opacity-50">اترك هذا الخيار فارغاً للحصول على تقرير
                                    مجمع يشمل جميع الموظفين المسجلين.</div>
                            </div>

                            <div class="d-grid gap-3 pt-2">
                                <button type="submit"
                                    class="btn btn-primary btn-lg rounded-pill fw-bold shadow-lg py-3 d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-printer-fill"></i>
                                    <span>توليد التقرير للطباعة (PDF)</span>
                                </button>
                                <p class="text-center text-secondary x-small mb-0">سيتم فتح التقرير بصيغة قابلة للطباعة في
                                    نافذة جديدة.</p>
                            </div>
                        </form>
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
            font-size: 0.75rem !important;
        }

        .h-48 {
            height: 48px !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(13, 110, 253, 0.5);
            color: white;
        }
    </style>
@endsection