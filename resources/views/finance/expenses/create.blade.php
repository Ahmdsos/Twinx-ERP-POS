@extends('layouts.app')

@section('title', 'تسجيل مصروف جديد')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-white mb-0">تسجيل مصروف جديد</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('expenses.index') }}" class="btn btn-glass-outline">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ المصروف
                        </button>
                    </div>
                </div>

                <!-- Main Data -->
                <div class="glass-card p-4">
                    <div class="row g-4">
                        <!-- Date & Category -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">تاريخ المصروف <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date"
                                class="form-control bg-transparent text-white border-secondary" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">بند المصروف (التصنيف) <span
                                    class="text-danger">*</span></label>
                            <select name="category_id" class="form-select bg-transparent text-white border-secondary"
                                required>
                                <option value="">اختر بند المصروف...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text text-white-50 small">يتم التوجيه المحاسبي تلقائياً بناءً على التصنيف</div>
                        </div>

                        <!-- Payment Account -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">حساب الدفع (من أين تم الدفع؟) <span
                                    class="text-danger">*</span></label>
                            <select name="payment_account_id" class="form-select bg-transparent text-white border-secondary"
                                required>
                                <option value="">اختر الخزينة أو البنك...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Amounts -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">المبلغ (بدون ضريبة) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount"
                                    class="form-control bg-transparent text-white border-secondary" placeholder="0.00"
                                    required>
                                <span class="input-group-text bg-transparent text-white border-secondary">ج.م</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">قيمة الضريبة (إن وجدت)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_amount"
                                    class="form-control bg-transparent text-white border-secondary" placeholder="0.00"
                                    value="0">
                                <span class="input-group-text bg-transparent text-white border-secondary">ج.م</span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">المستفيد (اختياري)</label>
                            <input type="text" name="payee" class="form-control bg-transparent text-white border-secondary"
                                placeholder="اسم الشخص أو الجهة المستلمة للمبلغ">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-gray-300">ملاحظات / وصف</label>
                            <textarea name="notes" class="form-control bg-transparent text-white border-secondary" rows="3"
                                placeholder="تفاصيل المصروف..."></textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">مرفقات (صورة الفاتورة)</label>
                            <input type="file" name="attachment"
                                class="form-control bg-transparent text-white border-secondary">
                            <div class="form-text text-white-50">الملفات المسموحة: PDF, JPG, PNG (حد أقصى 2MB)</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .glass-card {
                background: rgba(30, 41, 59, 0.7);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 12px;
            }

            .btn-glass-outline {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: white;
            }

            .form-control:focus,
            .form-select:focus {
                background-color: rgba(30, 41, 59, 0.9);
                border-color: #3b82f6;
                box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
                color: white;
            }

            .input-group-text {
                border-color: rgba(255, 255, 255, 0.2);
            }

            option {
                background-color: #1e293b;
                color: white;
            }
        </style>
    @endpush
@endsection