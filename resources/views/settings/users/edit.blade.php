@extends('layouts.app')

@section('title', 'تعديل بيانات المستخدم')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-white fw-bold"><i class="bi bi-person-gear me-2"></i> تعديل المستخدم: {{ $user->name }}</h2>
                <a href="{{ route('users.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-right me-2"></i> رجوع
                </a>
            </div>

            <div class="glass-card p-4">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-white-50">الاسم بالكامل <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-transparent text-white" required value="{{ old('name', $user->name) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white-50">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-transparent text-white" required value="{{ old('email', $user->email) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white-50">كلمة المرور (اتركها فارغة إذا لم تكن تريد التغيير)</label>
                            <input type="password" name="password" class="form-control bg-transparent text-white" minlength="8">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white-50">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control bg-transparent text-white">
                        </div>
                    </div>

                    <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">الصلاحيات والأدوار</h5>
                    
                    <div class="mb-4">
                        <label class="form-label text-white-50 d-block mb-3">اختر دور المستخدم:</label>
                        <div class="d-flex gap-3 flex-wrap">
                            @foreach($roles as $role)
                                <div class="form-check form-check-inline custom-radio-card">
                                    <input class="form-check-input" type="checkbox" name="roles[]" 
                                           id="role_{{ $role->id }}" value="{{ $role->name }}"
                                           {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        <div class="fw-bold">{{ ucfirst($role->name) }}</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                        <label class="form-check-label text-white" for="is_active">حساب نشط (يمكنه تسجيل الدخول)</label>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-save me-2"></i> حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }

        .custom-radio-card {
            margin: 0;
        }

        .custom-radio-card .form-check-input {
            display: none;
        }

        .custom-radio-card .form-check-label {
            display: block;
            padding: 10px 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.2s;
            background: rgba(255, 255, 255, 0.05);
        }

        .custom-radio-card .form-check-input:checked + .form-check-label {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
            color: white;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
        }
    </style>
@endsection
