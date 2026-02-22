@extends('layouts.app')

@section('title', 'تعديل المصروف')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-heading mb-0">تعديل المصروف #{{ $expense->reference_number }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('expenses.index') }}" class="btn btn-glass-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ التعديلات
                        </button>
                    </div>
                </div>

                @if($expense->journal_entry_id)
                    <div class="alert alert-warning d-flex align-items-center mb-3">
                        <i class="bi bi-exclamation-triangle me-2 fs-5"></i>
                        <div>
                            <strong>تنبيه:</strong> هذا المصروف مرتبط بقيد محاسبي. عند التعديل سيتم عكس القيد القديم وإنشاء قيد جديد تلقائياً.
                        </div>
                    </div>
                @endif

                <!-- Main Data -->
                <div class="glass-card p-4">
                    <div class="row g-4">
                        <!-- Date & Category -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">تاريخ المصروف <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date"
                                class="form-control bg-transparent text-body border-secondary"
                                value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">بند المصروف (التصنيف) <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select bg-transparent text-body border-secondary" required>
                                <option value="">اختر بند المصروف...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Account -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">حساب الدفع (من أين تم الدفع؟) <span class="text-danger">*</span></label>
                            <select name="payment_account_id" class="form-select bg-transparent text-body border-secondary" required>
                                <option value="">اختر الخزينة أو البنك...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}"
                                        {{ old('payment_account_id', $expense->payment_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ $account->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Amounts -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">المبلغ (بدون ضريبة) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount"
                                    class="form-control bg-transparent text-body border-secondary"
                                    value="{{ old('amount', $expense->amount) }}"
                                    required>
                                <span class="input-group-text bg-transparent text-body border-secondary">{{ __('EGP') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">قيمة الضريبة (إن وجدت)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_amount"
                                    class="form-control bg-transparent text-body border-secondary"
                                    value="{{ old('tax_amount', $expense->tax_amount ?? 0) }}">
                                <span class="input-group-text bg-transparent text-body border-secondary">{{ __('EGP') }}</span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">المستفيد (اختياري)</label>
                            <input type="text" name="payee" class="form-control bg-transparent text-body border-secondary"
                                value="{{ old('payee', $expense->payee) }}"
                                placeholder="اسم الشخص أو الجهة المستلمة للمبلغ">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-gray-300">ملاحظات / وصف</label>
                            <textarea name="notes" class="form-control bg-transparent text-body border-secondary" rows="3"
                                placeholder="تفاصيل المصروف...">{{ old('notes', $expense->notes) }}</textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">مرفقات (صورة الفاتورة)</label>
                            @if($expense->attachment)
                                <div class="mb-2">
                                    <span class="badge bg-success">مرفق موجود</span>
                                    <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="ms-2 text-info">عرض المرفق الحالي</a>
                                </div>
                            @endif
                            <input type="file" name="attachment"
                                class="form-control bg-transparent text-body border-secondary">
                            <div class="form-text text-muted">الملفات المسموحة: PDF, JPG, PNG (حد أقصى 2MB) — اتركه فارغاً للاحتفاظ بالمرفق الحالي</div>
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
            .input-group-text {
                border-color: rgba(255, 255, 255, 0.2);
            }
            option {
                background-color: var(--input-bg);
                color: var(--text-primary);
            }
        </style>
    @endpush
@endsection