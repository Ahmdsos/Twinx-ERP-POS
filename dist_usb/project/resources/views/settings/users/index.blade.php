@extends('layouts.app')

@section('title', 'إدارة المستخدمين - User Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-heading fw-bold"><i class="bi bi-people-fill me-2"></i> إدارة المستخدمين</h2>
            <p class="text-body-50">إضافة وتعديل المستخدمين وتعيين الصلاحيات.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-lg">
            <i class="bi bi-person-plus-fill me-2"></i> إضافة مستخدم جديد
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr class="text-muted border-bottom border-secondary">
                        <th class="py-3 ps-4">{{ __('User') }}</th>
                        <th class="py-3">{{ __('Email') }}</th>
                        <th class="py-3">الدور (Role)</th>
                        <th class="py-3">{{ __('Status') }}</th>
                        <th class="py-3">تاريخ التسجيل</th>
                        <th class="py-3 pe-4 text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-person text-body"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span
                                        class="badge {{ $role->name == 'admin' ? 'bg-danger' : 'bg-primary' }} bg-opacity-75 me-1">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">موقوف</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td class="pe-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-info"
                                        title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                            data-confirm="هل أنت متأكد من حذف هذا المستخدم؟">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-3 opacity-50"></i>
                                لا يوجد مستخدمين مضافين حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-top border-secondary">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <style>
        
    </style>
@endsection