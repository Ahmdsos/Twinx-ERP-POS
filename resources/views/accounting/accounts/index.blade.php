@extends('layouts.app')

@section('title', 'دليل الحسابات - Twinx ERP')
@section('page-title', 'دليل الحسابات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">دليل الحسابات</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>دليل الحسابات</h5>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>حساب جديد
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('accounts.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="بحث بالكود أو الاسم..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">كل الأنواع</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-search me-1"></i>بحث
                    </button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 100px;">الكود</th>
                            <th>اسم الحساب</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td><code>{{ $account->code }}</code></td>
                                <td>
                                    @if($account->parent_id)
                                        <span class="text-muted">—</span>
                                    @endif
                                    {{ $account->name }}
                                </td>
                                <td><span class="badge bg-secondary">{{ $account->type->label() }}</span></td>
                                <td>
                                    @if($account->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-outline-primary"
                                            title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="حذف"
                                                data-confirm="هل أنت متأكد من حذف هذا الحساب؟">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد حسابات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>
@endsection