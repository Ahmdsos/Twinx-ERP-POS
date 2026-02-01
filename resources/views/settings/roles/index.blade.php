@extends('layouts.app')

@section('title', 'إدارة الأدوار والصلاحيات - Roles')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold"><i class="bi bi-shield-lock-fill me-2"></i> الأدوار والصلاحيات</h2>
            <p class="text-white-50">تحديد ما يمكن لكل مستخدم القيام به في النظام.</p>
        </div>
        <a href="{{ route('roles.create') }}" class="btn btn-primary shadow-lg">
            <i class="bi bi-plus-lg me-2"></i> إضافة دور وظيفي
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th class="py-3 ps-4">اسم الدور</th>
                        <th class="py-3">عدد المستخدمين</th>
                        <th class="py-3">الصلاحيات</th>
                        <th class="py-3 pe-4 text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-white fs-5">{{ ucfirst($role->name) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-white fw-normal fs-6 px-3">
                                    <i class="bi bi-people me-1"></i> {{ $role->users_count }}
                                </span>
                            </td>
                            <td>
                                @if($role->name == 'admin')
                                    <span class="badge bg-danger bg-opacity-75">صلاحيات كاملة (Super Admin)</span>
                                @else
                                    <small class="text-white-50 d-block text-wrap" style="max-width: 400px;">
                                        {{ implode(', ', $role->permissions->pluck('name')->map(fn($n) => explode('.', $n)[1] ?? $n)->take(5)->toArray()) }}
                                        {{ $role->permissions->count() > 5 ? '... و ' . ($role->permissions->count() - 5) . ' صلاحيات أخرى' : '' }}
                                    </small>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-info"
                                        title="تعديل الصلاحيات">
                                        <i class="bi bi-pencil-square"></i> تعديل
                                    </a>
                                    @if(!in_array($role->name, ['admin']))
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا الدور؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-white-50">
                                <i class="bi bi-shield-slash fs-1 d-block mb-3 opacity-50"></i>
                                لا توجد أدوار مضافة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
            <div class="p-4 border-top border-secondary">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    <style>
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }
    </style>
@endsection