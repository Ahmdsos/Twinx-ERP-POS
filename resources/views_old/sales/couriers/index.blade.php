@extends('layouts.app')

@section('title', 'شركات الشحن')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">شركات الشحن</h1>
                <p class="text-muted mb-0">إدارة شركات الشحن والتوصيل</p>
            </div>
            <a href="{{ route('couriers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                شركة شحن جديدة
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                                <i class="bi bi-truck text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-0">إجمالي الشركات</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-0">نشط</h6>
                                <h3 class="mb-0">{{ $stats['active'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-secondary bg-opacity-10 rounded p-3">
                                <i class="bi bi-pause-circle text-secondary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-0">غير نشط</h6>
                                <h3 class="mb-0">{{ $stats['inactive'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="بحث بالاسم، الكود، أو الهاتف..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">جميع الحالات</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Couriers Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>الكود</th>
                            <th>اسم الشركة</th>
                            <th>جهة الاتصال</th>
                            <th>الهاتف</th>
                            <th>عدد الشحنات</th>
                            <th>الحالة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($couriers as $courier)
                            <tr>
                                <td>
                                    <a href="{{ route('couriers.show', $courier) }}" class="fw-bold text-decoration-none">
                                        {{ $courier->code }}
                                    </a>
                                </td>
                                <td>{{ $courier->name }}</td>
                                <td>{{ $courier->contact_person ?? '-' }}</td>
                                <td>{{ $courier->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $courier->shipments_count }} شحنة</span>
                                </td>
                                <td>
                                    @if($courier->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('couriers.show', $courier) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('couriers.edit', $courier) }}" class="btn btn-outline-secondary"
                                            title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('couriers.toggle-status', $courier) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-warning"
                                                title="{{ $courier->is_active ? 'تعطيل' : 'تفعيل' }}">
                                                <i class="bi bi-{{ $courier->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد شركات شحن
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($couriers->hasPages())
                <div class="card-footer">
                    {{ $couriers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection