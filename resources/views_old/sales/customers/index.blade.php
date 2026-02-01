@extends('layouts.app')

@section('title', 'العملاء - Twinx ERP')
@section('page-title', 'إدارة العملاء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">العملاء</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>العملاء</h5>
        <div class="d-flex gap-2">
            <!-- Export Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>تصدير
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('export.customers.excel') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i>تصدير Excel</a></li>
                    <li><a class="dropdown-item" href="{{ route('export.customers.pdf') }}" target="_blank"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>تصدير PDF</a></li>
                </ul>
            </div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>عميل جديد
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Search -->
        <form action="{{ route('customers.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" 
                       placeholder="بحث بالاسم، الكود، أو البريد..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" name="active_only" value="1" 
                           id="active_only" {{ request('active_only') ? 'checked' : '' }}>
                    <label class="form-check-label" for="active_only">النشطين فقط</label>
                </div>
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
                        <th style="width: 80px;">الكود</th>
                        <th>اسم العميل</th>
                        <th>الهاتف</th>
                        <th>المدينة</th>
                        <th>حد الائتمان</th>
                        <th>الحالة</th>
                        <th style="width: 150px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><code>{{ $customer->code }}</code></td>
                            <td>
                                <strong>{{ $customer->name }}</strong>
                                @if($customer->email)
                                    <br><small class="text-muted">{{ $customer->email }}</small>
                                @endif
                            </td>
                            <td>{{ $customer->mobile ?: $customer->phone ?: '-' }}</td>
                            <td>{{ $customer->billing_city ?: '-' }}</td>
                            <td class="money">{{ number_format($customer->credit_limit, 2) }}</td>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-danger">معطل</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="حذف" 
                                                data-confirm="هل أنت متأكد من حذف هذا العميل؟">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                لا يوجد عملاء
                                <br>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="bi bi-plus-circle me-1"></i>إضافة عميل
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
