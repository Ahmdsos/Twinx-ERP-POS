@extends('layouts.app')

@section('title', 'الموردين - Twinx ERP')
@section('page-title', 'إدارة الموردين')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الموردين</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-truck me-2"></i>الموردين</h5>
            <div class="d-flex gap-2">
                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-1"></i>تصدير
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('export.suppliers.excel') }}"><i
                                    class="bi bi-file-earmark-excel text-success me-2"></i>تصدير Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('export.suppliers.pdf') }}" target="_blank"><i
                                    class="bi bi-file-earmark-pdf text-danger me-2"></i>تصدير PDF</a></li>
                    </ul>
                </div>
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>مورد جديد
                </a>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="card-body border-bottom">
            <form action="{{ route('suppliers.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="بحث بالاسم أو الكود..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">كل الحالات</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search me-1"></i>بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الهاتف</th>
                            <th>شروط الدفع</th>
                            <th>الرصيد المستحق</th>
                            <th>الحالة</th>
                            <th class="text-center" style="width: 120px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers ?? [] as $supplier)
                            <tr>
                                <td><strong>{{ $supplier->code }}</strong></td>
                                <td>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-decoration-none">
                                        {{ $supplier->name }}
                                    </a>
                                </td>
                                <td dir="ltr" class="text-end">{{ $supplier->phone ?? '-' }}</td>
                                <td>{{ $supplier->payment_terms }} يوم</td>
                                <td class="{{ ($supplier->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($supplier->balance ?? 0, 2) }} ج.م
                                </td>
                                <td>
                                    <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                                        {{ $supplier->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-secondary"
                                            title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المورد؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-truck fs-1 d-block mb-3"></i>
                                    <h5>لا يوجد موردين</h5>
                                    <p class="mb-3">ابدأ بإضافة مورد جديد</p>
                                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>إضافة مورد
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($suppliers ?? collect(), 'links'))
            <div class="card-footer">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
@endsection