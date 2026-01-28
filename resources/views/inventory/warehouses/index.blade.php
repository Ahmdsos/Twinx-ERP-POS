@extends('layouts.app')

@section('title', 'المستودعات - Twinx ERP')
@section('page-title', 'إدارة المستودعات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المستودعات</li>
@endsection

@section('content')
<div class="row">
    <!-- Warehouses List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>المستودعات</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                    <i class="bi bi-plus-circle me-1"></i>مستودع جديد
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الكود</th>
                                <th>الاسم</th>
                                <th>الموقع</th>
                                <th>عدد الأصناف</th>
                                <th>قيمة المخزون</th>
                                <th>الحالة</th>
                                <th class="text-center" style="width: 100px;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses ?? [] as $warehouse)
                            <tr>
                                <td><strong>{{ $warehouse->code }}</strong></td>
                                <td>
                                    <i class="bi bi-building text-primary me-2"></i>
                                    {{ $warehouse->name }}
                                </td>
                                <td>{{ $warehouse->address ?? '-' }}</td>
                                <td>{{ $warehouse->stocks_count ?? 0 }}</td>
                                <td>{{ number_format($warehouse->stock_value ?? 0, 2) }} ج.م</td>
                                <td>
                                    <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'secondary' }}">
                                        {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                    @if($warehouse->is_default)
                                        <span class="badge bg-primary">الافتراضي</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-outline-primary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary" onclick="editWarehouse({{ $warehouse->id }})" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if(!$warehouse->is_default)
                                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستودع؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-building fs-1 d-block mb-3"></i>
                                    <h5>لا توجد مستودعات</h5>
                                    <p class="mb-3">ابدأ بإضافة مستودع جديد</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                                        <i class="bi bi-plus-circle me-1"></i>إضافة مستودع
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Add Form -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة سريعة</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('warehouses.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">كود المستودع <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               name="code" value="{{ old('code') }}" placeholder="WH-001" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">اسم المستودع <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" 
                               name="address" value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    

                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">مستودع نشط</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>حفظ
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Stock Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>ملخص المخزون</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">إجمالي المستودعات</span>
                    <strong>{{ count($warehouses ?? []) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">إجمالي الأصناف</span>
                    <strong>{{ $totalItems ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">إجمالي القيمة</span>
                    <strong class="text-success">{{ number_format($totalValue ?? 0, 2) }} ج.م</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Warehouse Modal -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مستودع جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">كود المستودع <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="WH-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم المستودع <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" class="form-control" name="address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_default" id="modal_is_default" value="1">
                        <label class="form-check-label" for="modal_is_default">تعيين كمستودع افتراضي</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editWarehouse(id) {
    window.location.href = '/warehouses/' + id + '/edit';
}
</script>
@endpush