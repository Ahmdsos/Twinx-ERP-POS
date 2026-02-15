@extends('layouts.app')

@section('title', 'إضافة بند مصروف')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ route('expense-categories.store') }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-heading mb-0">إضافة بند مصروف جديد</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('expense-categories.index') }}" class="btn btn-glass-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ البند
                        </button>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <label class="form-label text-secondary">اسم البند <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-transparent text-body border-secondary"
                                placeholder="مثال: إيجار، كهرباء، رواتب..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">الكود (اختياري)</label>
                            <input type="text" name="code" class="form-control bg-transparent text-body border-secondary"
                                placeholder="EXP-001">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-secondary">الحساب المحاسبي المرتبط</label>
                            <select name="account_id" class="form-select bg-transparent text-body border-secondary">
                                <option value="">-- اختر حساب المصروف --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">عند اختيار هذا البند في المصروفات، سيتم التوجيه لهذا
                                الحساب تلقائياً.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-secondary">وصف إضافي</label>
                            <textarea name="description" class="form-control bg-transparent text-body border-secondary"
                                rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            

            .btn-glass-outline {
                background: var(--btn-glass-bg);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: var(--text-primary);
            }

            .form-control:focus,
            .form-select:focus {
                background-color: rgba(30, 41, 59, 0.9);
                border-color: #3b82f6;
                box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
                color: var(--text-primary);
            }

            option {
                background-color: var(--input-bg);
                color: var(--text-primary);
            }
        </style>
    @endpush
@endsection