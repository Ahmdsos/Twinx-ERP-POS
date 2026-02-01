@extends('layouts.app')

@section('title', 'تعديل بند مصروف')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ route('expense-categories.update', $expenseCategory) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-white mb-0">تعديل بند مصروف: {{ $expenseCategory->name }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('expense-categories.index') }}" class="btn btn-glass-outline">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ التعديلات
                        </button>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <label class="form-label text-gray-300">اسم البند <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-transparent text-white border-secondary"
                                value="{{ $expenseCategory->name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-gray-300">الكود (اختياري)</label>
                            <input type="text" name="code" class="form-control bg-transparent text-white border-secondary"
                                value="{{ $expenseCategory->code }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-gray-300">الحساب المحاسبي المرتبط</label>
                            <select name="account_id" class="form-select bg-transparent text-white border-secondary">
                                <option value="">-- اختر حساب المصروف --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $expenseCategory->account_id == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ $account->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-white-50 small">عند اختيار هذا البند في المصروفات، سيتم التوجيه لهذا
                                الحساب تلقائياً.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-gray-300">وصف إضافي</label>
                            <textarea name="description" class="form-control bg-transparent text-white border-secondary"
                                rows="3">{{ $expenseCategory->description }}</textarea>
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

            option {
                background-color: #1e293b;
                color: white;
            }
        </style>
    @endpush
@endsection