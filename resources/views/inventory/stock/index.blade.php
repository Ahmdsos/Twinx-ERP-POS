@extends('layouts.app')

@section('title', 'حركات المخزون - Twinx ERP')
@section('page-title', 'إدارة حركات المخزون')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">حركات المخزون</li>
@endsection

@section('content')
    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group">
                <a href="{{ route('stock.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>استلام مخزون
                </a>
                <a href="{{ route('stock.adjust') }}" class="btn btn-warning">
                    <i class="bi bi-sliders me-1"></i>تسوية مخزون
                </a>
                <a href="{{ route('stock.transfer') }}" class="btn btn-info">
                    <i class="bi bi-arrow-left-right me-1"></i>تحويل مخزون
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('stock.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">المنتج</label>
                    <select class="form-select" name="product_id">
                        <option value="">كل المنتجات</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">المستودع</label>
                    <select class="form-select" name="warehouse_id">
                        <option value="">كل المستودعات</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">نوع الحركة</label>
                    <select class="form-select" name="type">
                        <option value="">الكل</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>سجل الحركات</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الرقم</th>
                            <th>التاريخ</th>
                            <th>المنتج</th>
                            <th>المستودع</th>
                            <th>النوع</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                            <th>المرجع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td><code>{{ $movement->movement_number }}</code></td>
                                <td>{{ $movement->movement_date->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('products.show', $movement->product_id) }}">
                                        {{ $movement->product?->name ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $movement->warehouse?->name ?? '-' }}</td>
                                <td>
                                    @php
                                        $typeClass = match($movement->type->value) {
                                            'purchase', 'adjustment_in', 'initial', 'transfer_in' => 'success',
                                            'sale', 'adjustment_out', 'transfer_out' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $typeClass }}">{{ $movement->type->label() }}</span>
                                </td>
                                <td class="{{ in_array($movement->type->value, ['sale', 'adjustment_out', 'transfer_out']) ? 'text-danger' : 'text-success' }}">
                                    {{ in_array($movement->type->value, ['sale', 'adjustment_out', 'transfer_out']) ? '-' : '+' }}{{ number_format($movement->quantity, 2) }}
                                </td>
                                <td>{{ number_format($movement->unit_cost, 2) }}</td>
                                <td>{{ number_format($movement->total_cost, 2) }} ج.م</td>
                                <td>{{ $movement->reference ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد حركات مخزون
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movements->hasPages())
            <div class="card-footer">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
@endsection
