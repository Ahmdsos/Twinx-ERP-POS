@extends('layouts.app')

@section('title', __('Expense Categories'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-heading mb-1">{{ __('Expense Categories') }}</h4>
            <div class="text-muted small">إدارة تصنيفات المصروفات وحساباتها</div>
        </div>
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary shadow-lg fw-bold px-4 py-2">
            <i class="bi bi-plus-lg me-1"></i> بند جديد
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle text-body mb-0 custom-table">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-secondary-50 fw-normal">الكود</th>
                        <th class="py-4 text-secondary-50 fw-normal">اسم البند</th>
                        <th class="py-4 text-secondary-50 fw-normal">المواصفات</th>
                        <th class="py-4 text-secondary-50 fw-normal">حساب المصروف المرتبط</th>
                        <th class="py-4 text-center text-secondary-50 fw-normal">{{ __('Status') }}</th>
                        <th class="px-4 py-4 text-end text-secondary-50 fw-normal">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="table-row-hover">
                            <td class="px-4 py-3 font-monospace text-info fs-5">{{ $category->code ?? '-' }}</td>
                            <td class="py-3 fw-bold fs-5">{{ $category->name }}</td>
                            <td class="py-3 text-muted">{{ $category->description ?? '-' }}</td>
                            <td class="py-3">
                                @if($category->account)
                                    <span class="badge bg-primary bg-opacity-20 text-white border border-primary border-opacity-25 fw-normal px-3 py-2 rounded-pill">
                                        {{ $category->account->name }} 
                                        <span class="font-monospace ms-2 opacity-50">{{ $category->account->code }}</span>
                                    </span>
                                @else
                                    <span class="text-warning small d-flex align-items-center">
                                        <i class="bi bi-exclamation-triangle me-2"></i> 
                                        غير مرتبط بحساب
                                    </span>
                                @endif
                            </td>
                            <td class="text-center py-3">
                                @if($category->is_active)
                                    <div class="d-inline-flex align-items-center text-success bg-success bg-opacity-10 px-3 py-1 rounded-pill border border-success border-opacity-10">
                                        <i class="bi bi-check-circle-fill me-2 small"></i> <span class="small fw-bold">{{ __('Active') }}</span>
                                    </div>
                                @else
                                    <div class="d-inline-flex align-items-center text-secondary bg-secondary bg-opacity-10 px-3 py-1 rounded-pill border border-secondary border-opacity-10">
                                        <i class="bi bi-dash-circle-fill me-2 small"></i> <span class="small fw-bold">موقف</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 text-end py-3">
                                 <div class="d-flex justify-content-end gap-2">
                                     <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-sm btn-glass text-warning" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil-square fs-6"></i>
                                    </a>
                                    <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" data-confirm="هل أنت متأكد من حذف هذا التصنيف؟">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-glass text-danger hover-damn" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </form>
                                 </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                 <div class="d-flex flex-column align-items-center justify-content-center py-5 opacity-50">
                                    <i class="bi bi-tags display-1 mb-4"></i>
                                    <h4 class="text-heading-50">لا توجد بنود مصروفات</h4>
                                    <p class="mb-4">ابدأ بإضافة الأصناف والتصنيفات</p>
                                    <a href="{{ route('expense-categories.create') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">إضافة بند جديد</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 border-opacity-10 py-4">
            {{ $categories->links('partials.pagination') }}
        </div>
    </div>

    <style>
        

        .custom-table thead th {
            background-color: rgba(255, 255, 255, 0.03);
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table-row-hover {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .table-row-hover:hover {
            background-color: var(--table-head-bg); 
            transform: translateY(-1px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        
        .table-row-hover td {
            border: none;
        }

        .btn-glass {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            border-radius: 8px;
            width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        .hover-damn:hover {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.5);
        }
    </style>
@endsection