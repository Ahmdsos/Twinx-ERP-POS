@extends('layouts.app')

@section('title', 'المنتجات - Twinx ERP')
@section('page-title', 'إدارة المنتجات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المنتجات</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>المنتجات</h5>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>منتج جديد
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('products.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="بحث بالاسم، SKU، أو الباركود..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category_id">
                        <option value="">كل التصنيفات</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
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
                            <th style="width: 100px;">SKU</th>
                            <th>اسم المنتج</th>
                            <th>التصنيف</th>
                            <th>سعر التكلفة</th>
                            <th>سعر البيع</th>
                            <th>الوحدة</th>
                            <th>الحالة</th>
                            <th style="width: 150px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->barcode)
                                        <br><small class="text-muted">{{ $product->barcode }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td class="money">{{ number_format($product->cost_price, 2) }}</td>
                                <td class="money text-success">{{ number_format($product->selling_price, 2) }}</td>
                                <td>{{ $product->unit?->abbreviation ?? '-' }}</td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary"
                                            title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="حذف"
                                                data-confirm="هل أنت متأكد من حذف هذا المنتج؟">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                    لا توجد منتجات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection