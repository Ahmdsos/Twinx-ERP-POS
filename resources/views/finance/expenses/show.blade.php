@extends('layouts.app')

@section('title', __('Expense Details'))

@section('content')
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    مصروف <span class="text-info font-monospace">{{ $expense->reference_number }}</span>
                </h4>
                <div class="text-muted small">
                    <i class="bi bi-calendar-event me-1"></i> {{ $expense->expense_date->format('Y-m-d') }}
                    <span class="mx-2">|</span>
                    <i class="bi bi-funnel me-1"></i> {{ $expense->category->name }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('expenses.index') }}" class="btn btn-glass-outline">عودة للقائمة</a>
                <button onclick="window.print()" class="btn btn-glass-outline">
                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}</button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-md-8">
            <div class="glass-card p-4 h-100">
                <div
                    class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-3">
                    <h5 class="text-heading mb-0">بيانات المصروف</h5>
                    <span
                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 py-2 rounded-pill">
                        تم الصرف
                    </span>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small mb-1">{{ __('Amount') }}</label>
                        <div class="text-body fs-5 fw-bold">{{ number_format($expense->amount, 2) }} ج.م</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small mb-1">{{ __('Tax') }}</label>
                        <div class="text-body fs-5 fw-bold">{{ number_format($expense->tax_amount, 2) }} ج.م</div>
                    </div>
                    <div class="col-12 border-top border-secondary border-opacity-10 border-opacity-10 pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="text-body small mb-0">{{ __('Grand Total') }}</label>
                            <div class="text-warning fs-4 fw-bold font-monospace">
                                {{ number_format($expense->total_amount, 2) }} ج.م</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted small mb-1">حساب الدفع</label>
                        <div class="d-flex align-items-center">
                            <div class="bg-surface bg-opacity-10 p-2 rounded-circle me-3">
                                <i class="bi bi-wallet2 text-info"></i>
                            </div>
                            <div>
                                <div class="text-body fw-bold">{{ $expense->paymentAccount->name }}</div>
                                <div class="text-muted small font-monospace">{{ $expense->paymentAccount->code }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small mb-1">المستفيد</label>
                        <div class="text-body">{{ $expense->payee ?? '-' }}</div>
                    </div>
                </div>

                @if($expense->notes)
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 border-opacity-10">
                        <label class="text-muted small mb-2">ملاحظات:</label>
                        <p class="text-body bg-surface bg-opacity-5 p-3 rounded-3 mb-0">{{ $expense->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <!-- Journal Entry Link -->
            <div class="glass-card p-4 mb-4">
                <h6 class="text-heading-50 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-2 mb-3">القيد المحاسبي</h6>
                @if($expense->journalEntry)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-body">{{ __('Entry Number') }}</span>
                        <a href="{{ route('journal-entries.show', $expense->journalEntry) }}"
                            class="text-info font-monospace text-decoration-none fw-bold">
                            #{{ $expense->journalEntry->id }}
                        </a>
                    </div>
                    <div class="alert alert-info bg-opacity-10 border-info border-opacity-25 text-info mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        تم إنشاء قيد يومية تلقائي لهذا المصروف.
                    </div>
                @else
                    <div class="text-muted small">لا يوجد قيد محاسبي مرتبط</div>
                @endif
            </div>

            <!-- Attachment -->
            <div class="glass-card p-4">
                <h6 class="text-heading-50 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-2 mb-3">المرفقات</h6>
                @if($expense->attachment)
                    <div class="text-center py-3">
                        <i class="bi bi-file-earmark-pdf fs-1 text-danger opacity-75 mb-2 d-block"></i>
                        <a href="{{ Storage::url($expense->attachment) }}" target="_blank"
                            class="btn btn-sm btn-outline-light w-100">
                            عرض المرفق
                        </a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-paperclip fs-1 opacity-25 d-block mb-2"></i>
                        لا توجد مرفقات
                    </div>
                @endif
            </div>

            <div class="text-center mt-3">
                <div class="text-muted small">تم التسجيل بواسطة {{ $expense->creator->name ?? 'System' }}</div>
                <div class="text-muted small">{{ $expense->created_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    <style>
        

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }
    </style>
@endsection