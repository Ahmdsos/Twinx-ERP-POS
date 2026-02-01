@extends('layouts.app')

@section('title', 'التصنيفات - Twinx ERP')
@section('page-title', 'إدارة التصنيفات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التصنيفات</li>
@endsection

@section('content')
<div class="row">
    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-folder me-2"></i>التصنيفات</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle me-1"></i>تصنيف جديد
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الاسم</th>
                                <th>التصنيف الأب</th>
                                <th>عدد المنتجات</th>
                                <th>الحالة</th>
                                <th class="text-center" style="width: 100px;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories ?? [] as $category)
                            <tr>
                                <td>
                                    <i class="bi bi-folder{{ $category->children_count ? '' : '-fill' }} text-warning me-2"></i>
                                    {{ $category->name }}
                                </td>
                                <td>{{ $category->parent?->name ?? '-' }}</td>
                                <td>{{ $category->products_count ?? 0 }}</td>
                                <td>
                                    <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                        {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editCategory({{ $category->id }})" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف؟')">
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
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-folder fs-1 d-block mb-3"></i>
                                    <h5>لا توجد تصنيفات</h5>
                                    <p class="mb-3">ابدأ بإضافة تصنيف جديد</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                        <i class="bi bi-plus-circle me-1"></i>إضافة تصنيف
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
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">التصنيف الأب</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" name="parent_id">
                            <option value="">بدون (تصنيف رئيسي)</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="2">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">تصنيف نشط</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>حفظ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة تصنيف جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التصنيف الأب</label>
                        <select class="form-select" name="parent_id">
                            <option value="">بدون (تصنيف رئيسي)</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
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
function editCategory(id) {
    // TODO: Implement edit modal or redirect
    window.location.href = '/categories/' + id + '/edit';
}
</script>
@endpush