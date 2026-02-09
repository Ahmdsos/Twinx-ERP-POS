@extends('layouts.app')

@section('title', 'إضافة حساب جديد')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-white mb-0">إضافة حساب جديد</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounts.index') }}" class="btn btn-glass-outline">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ الحساب
                        </button>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">رقم الحساب (Code) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="code"
                                class="form-control bg-transparent text-white border-secondary font-monospace"
                                placeholder="مثال: 1101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">اسم الحساب (English) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-transparent text-white border-secondary"
                                placeholder="Example: Sales" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-info">اسم الحساب (عربي) <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control bg-transparent text-white border-info"
                                placeholder="مثال: المبيعات" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-300">نوع الحساب <span class="text-danger">*</span></label>
                            <select name="type" class="form-select bg-transparent text-white border-secondary" required>
                                <option value="">-- اختر نوع الحساب --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">الحساب الرئيسي (Parent Application)</label>
                            <select name="parent_id" class="form-select bg-transparent text-white border-secondary">
                                <option value="">-- حساب رئيسي (Root) --</option>
                                @foreach($parentAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-white-50 small">اتركه فارغاً إذا كان حساب رئيسي</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-gray-300">الوصف</label>
                            <textarea name="description" class="form-control bg-transparent text-white border-secondary"
                                rows="3"></textarea>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                    checked>
                                <label class="form-check-label text-white" for="isActive">حساب نشط (Active)</label>
                            </div>
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