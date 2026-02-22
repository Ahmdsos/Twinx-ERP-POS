@extends('layouts.app')

@section('title', 'تفاصيل السلفة')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">تفاصيل السلفة #{{ $advance->id }}</h4>
                <div class="text-muted small">تاريخ الطلب: {{ $advance->request_date->format('Y-m-d') }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('hr.advances.index') }}" class="btn btn-outline-light">عودة للقائمة</a>

                @if($advance->status == 'pending')
                    <form action="{{ route('hr.advances.approve', $advance->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i> اعتماد السلفة</button>
                    </form>
                @endif

                @if($advance->status == 'approved')
                    <form action="{{ route('hr.advances.pay', $advance->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-cash-stack me-1"></i> صرف نقدية</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card glass-card border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase mb-1">الموظف</div>
                                <div class="fs-5 fw-bold text-body">{{ $advance->employee->first_name }}
                                    {{ $advance->employee->last_name }}
                                </div>
                                <div class="small text-muted font-monospace">{{ $advance->employee->employee_code }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase mb-1">المبلغ</div>
                                <div class="display-6 fw-bold text-primary">{{ number_format($advance->amount, 2) }} <small
                                        class="fs-5 text-muted">ج.م</small></div>
                            </div>
                            <div class="col-12">
                                <div class="dashed-line my-2"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase mb-1">شهر الاستحقاق</div>
                                <div class="fs-5 fw-bold text-body">
                                    {{ date('F', mktime(0, 0, 0, $advance->repayment_month, 1)) }}
                                    {{ $advance->repayment_year }}
                                </div>
                                <div class="small text-warning"><i class="bi bi-exclamation-triangle me-1"></i> سيتم الخصم
                                    تلقائياً من هذا الراتب</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase mb-1">الحالة الحالية</div>
                                @if($advance->status == 'pending')
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">معلق (بانتظار الموافقة)</span>
                                @elseif($advance->status == 'approved')
                                    <span class="badge bg-info text-dark fs-6 px-3 py-2">معتمد (بانتظار الصرف)</span>
                                @elseif($advance->status == 'paid')
                                    <span class="badge bg-success fs-6 px-3 py-2">تم الصرف (قيد مديونية)</span>
                                @elseif($advance->status == 'deducted')
                                    <span class="badge bg-secondary fs-6 px-3 py-2">تم الخصم (سداد كامل)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card glass-card border-0">
                    <div class="card-header bg-transparent border-bottom border-light border-opacity-10">
                        <h6 class="fw-bold text-heading m-0">ملاحظات</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-body m-0">{{ $advance->notes ?? 'لا توجد ملاحظات.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <div class="card glass-card border-0 mb-4">
                    <div class="card-header bg-transparent border-bottom border-light border-opacity-10">
                        <h6 class="fw-bold text-heading m-0">سجل العمليات</h6>
                    </div>
                    <div class="list-group list-group-flush bg-transparent">
                        <div class="list-group-item bg-transparent border-light border-opacity-10 px-4 py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-body">تم الطلب</h6>
                                <small class="text-muted">{{ $advance->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">بواسطة النظام</p>
                        </div>

                        @if($advance->approved_by)
                            <div class="list-group-item bg-transparent border-light border-opacity-10 px-4 py-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 text-success">تم الاعتماد</h6>
                                    <small class="text-muted">{{ $advance->approved_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1 small text-muted">بواسطة {{ $advance->approver->name ?? 'Admin' }}</p>
                            </div>
                        @endif

                        @if($advance->paid_by)
                            <div class="list-group-item bg-transparent border-light border-opacity-10 px-4 py-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 text-primary">تم الصرف</h6>
                                    <small class="text-muted">{{ $advance->paid_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1 small text-muted">بواسطة {{ $advance->payer->name ?? 'Admin' }}</p>
                                @if($advance->journal_entry_id)
                                    <a href="{{ route('journal-entries.show', $advance->journal_entry_id) }}"
                                        class="small text-info text-decoration-none">
                                        <i class="bi bi-link-45deg"></i> عرض القيد المحاسبي
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection