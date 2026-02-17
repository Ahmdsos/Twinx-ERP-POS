@extends('layouts.app')

@section('title', 'طلب سلفة جديدة')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card glass-card border-0">
                    <div class="card-header bg-transparent border-bottom border-light border-opacity-10 py-3">
                        <h5 class="fw-bold text-heading m-0">
                            <i class="bi bi-plus-circle me-2 text-primary"></i> تسجيل طلب سلفة
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('hr.advances.store') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">الموظف</label>
                                <select name="employee_id" class="form-select form-select-lg bg-transparent text-body"
                                    required>
                                    <option value="" selected disabled>اختر الموظف...</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ (isset($selected_employee_id) && $selected_employee_id == $emp->id) ? 'selected' : '' }}>
                                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase">المبلغ المطلوب</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i
                                                class="bi bi-cash"></i></span>
                                        <input type="number" name="amount"
                                            class="form-control bg-transparent border-start-0 text-body" placeholder="0.00"
                                            step="0.01" min="1" required>
                                        <span class="input-group-text bg-transparent text-muted">ج.م</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase">خصم من راتب
                                        شهر</label>
                                    <div class="d-flex gap-2">
                                        <select name="repayment_month"
                                            class="form-select form-select-lg bg-transparent text-body" required>
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ date('n') + 1 == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }} ({{ $m }})
                                                </option>
                                            @endfor
                                        </select>
                                        <select name="repayment_year"
                                            class="form-select form-select-lg bg-transparent text-body" required>
                                            @for($y = date('Y'); $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i> سيتم خصم
                                        المبلغ تلقائياً عند استحقاق هذا الشهر.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">ملاحظات</label>
                                <textarea name="notes" class="form-control bg-transparent text-body" rows="3"
                                    placeholder="سبب طلب السلفة..."></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-3 border-top border-light border-opacity-10">
                                <a href="{{ route('hr.advances.index') }}"
                                    class="btn btn-light bg-transparent border-0 text-muted">إلغاء</a>
                                <button type="submit" class="btn btn-primary px-5 fw-bold">حفظ الطلب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection