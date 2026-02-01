@extends('layouts.app')

@section('title', 'تفاصيل مسيرة الرواتب')
@section('header', 'الموارد البشرية')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header & Actions -->
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6 text-start">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('hr.payroll.index') }}"
                        class="btn btn-icon-box bg-white bg-opacity-10 text-white rounded-3">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <div>
                        <h2 class="text-white fw-black mb-0">مسيرة رواتب شهر {{ $payroll->month }} / {{ $payroll->year }}
                        </h2>
                        <p class="text-secondary small mt-1 opacity-75">كود المرجع: #{{ $payroll->id }} | الحالة:
                            <span
                                class="badge bg-{{ $payroll->status == 'processed' ? 'success' : ($payroll->status == 'posted' ? 'info' : 'warning') }} bg-opacity-10 text-{{ $payroll->status == 'processed' ? 'success' : ($payroll->status == 'posted' ? 'info' : 'warning') }} border border-white border-opacity-10 rounded-pill px-3">
                                {{ Modules\HR\Models\Payroll::getStatusLabels()[$payroll->status] ?? $payroll->status }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end gap-2">
                    @if($payroll->status == 'draft')
                        <form action="{{ route('hr.payroll.post', $payroll->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-black border-0 shadow-lg"
                                style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                                <i class="bi bi-send-fill me-1"></i> اعتماد وترحيل للحسابات
                            </button>
                        </form>
                    @elseif($payroll->status == 'processed')
                        <div
                            class="badge bg-success bg-opacity-20 text-success p-3 rounded-4 border border-success border-opacity-25">
                            <i class="bi bi-check-all fs-5 me-1"></i> تم الاعتماد والترحيل بنجاح
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 border-white border-opacity-10 text-center bg-dark bg-opacity-25">
                    <div class="text-secondary x-small fw-bold mb-1 opacity-75">إجمالي الرواتب الأساسية</div>
                    <div class="text-white h3 fw-black mb-0">{{ number_format($payroll->items->sum('basic_salary'), 2) }}
                        <span class="fs-6">ج.م</span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 border-white border-opacity-10 text-center bg-dark bg-opacity-25">
                    <div class="text-secondary x-small fw-bold mb-1 opacity-75 text-success">إجمالي البدلات والإضافات</div>
                    <div class="text-success h3 fw-black mb-0">+ {{ number_format($payroll->total_allowances, 2) }} <span
                            class="fs-6 text-white text-opacity-50">ج.م</span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 border-white border-opacity-10 text-center bg-dark bg-opacity-25">
                    <div class="text-secondary x-small fw-bold mb-1 opacity-75 text-danger">إجمالي الاستقطاعات</div>
                    <div class="text-danger h3 fw-black mb-0">- {{ number_format($payroll->total_deductions, 2) }} <span
                            class="fs-6 text-white text-opacity-50">ج.م</span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 border-white border-opacity-10 text-center bg-primary bg-opacity-10"
                    style="border-left: 4px solid #0d6efd !important;">
                    <div class="text-primary x-small fw-bold mb-1">صافي المسيرة (للدفع)</div>
                    <div class="text-white h2 fw-black mb-0">{{ number_format($payroll->net_salary, 2) }} <span
                            class="fs-6 opacity-50">ج.م</span></div>
                </div>
            </div>
        </div>

        <!-- Details Table -->
        <div class="glass-card rounded-4 border-white border-opacity-10 overflow-hidden shadow-lg bg-dark bg-opacity-25">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white text-start">
                    <thead>
                        <tr class="bg-white bg-opacity-5 border-bottom border-white border-opacity-10">
                            <th class="ps-4 py-3 border-0 text-secondary x-small fw-black text-uppercase">الموظف</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الراتب
                                الأساسي</th>
                            <th
                                class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center text-success">
                                بدلات</th>
                            <th
                                class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center text-danger">
                                خصومات</th>
                            <th class="py-3 border-0 text-secondary x-small fw-black text-uppercase text-center">الصافي</th>
                            @if($payroll->status == 'draft')
                                <th class="pe-4 py-3 border-0 text-secondary x-small fw-black text-uppercase text-end">تعديل
                                    يدوي</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payroll->items as $item)
                            <tr class="border-bottom border-white border-opacity-5">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                            style="width: 35px; height: 35px;">
                                            {{ mb_substr($item->employee->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-6">{{ $item->employee->full_name }}</div>
                                            <div class="text-secondary x-small opacity-50">{{ $item->employee->position }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center opacity-75">{{ number_format($item->basic_salary, 2) }}</td>
                                <td class="py-3 text-center text-success">+ {{ number_format($item->allowances, 2) }}</td>
                                <td class="py-3 text-center text-danger">- {{ number_format($item->deductions, 2) }}</td>
                                <td class="py-3 text-center fw-black">{{ number_format($item->net_salary, 2) }}</td>

                                @if($payroll->status == 'draft')
                                    <td class="pe-4 py-3 text-end">
                                        <button type="button"
                                            class="btn btn-icon-box bg-white bg-opacity-10 text-white rounded-3 btn-edit-item"
                                            data-bs-toggle="modal" data-bs-target="#editItemModal" data-id="{{ $item->id }}"
                                            data-name="{{ $item->employee->full_name }}" data-allowances="{{ $item->allowances }}"
                                            data-deductions="{{ $item->deductions }}" data-notes="{{ $item->notes }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-white border-opacity-10 rounded-4 shadow-lg bg-dark">
                <div class="modal-header border-bottom border-white border-opacity-5 p-4">
                    <h5 class="modal-title text-white fw-bold">تعديل رواتب الموظف: <span id="modalEmployeeName"
                            class="text-primary"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editItemForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 text-start">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">إجمالي البدلات</label>
                                <input type="number" step="0.01" name="allowances" id="modalAllowances"
                                    class="form-control bg-white bg-opacity-5 border-white border-opacity-10 text-white shadow-none"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">إجمالي الخصومات</label>
                                <input type="number" step="0.01" name="deductions" id="modalDeductions"
                                    class="form-control bg-white bg-opacity-5 border-white border-opacity-10 text-white shadow-none"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-secondary small fw-bold">ملاحظات التعديل</label>
                                <textarea name="notes" id="modalNotes"
                                    class="form-control bg-white bg-opacity-5 border-white border-opacity-10 text-white shadow-none"
                                    rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white border-opacity-5 p-4">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none fw-bold"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill fw-black shadow-lg border-0">حفظ
                            التغييرات</button>
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
            /* Higher opacity for shadow backgrounds */
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

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #0d6efd;
            color: white;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editButtons = document.querySelectorAll('.btn-edit-item');
            const modalName = document.getElementById('modalEmployeeName');
            const modalAllowances = document.getElementById('modalAllowances');
            const modalDeductions = document.getElementById('modalDeductions');
            const modalNotes = document.getElementById('modalNotes');
            const form = document.getElementById('editItemForm');

            editButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    modalName.textContent = this.dataset.name;
                    modalAllowances.value = this.dataset.allowances;
                    modalDeductions.value = this.dataset.deductions;
                    modalNotes.value = this.dataset.notes;
                    form.action = `/hr/payroll/items/${id}`;
                });
            });
        });
    </script>
@endsection