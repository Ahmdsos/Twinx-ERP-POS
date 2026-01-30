@extends('layouts.app')

@section('title', $product->name . ' - Twinx ERP')
@section('page-title', 'تفاصيل المنتج')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">المنتجات</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Product Info -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box me-2"></i>معلومات المنتج</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                    <i class="bi bi-pencil me-2"></i>تعديل
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-trash me-2"></i>حذف
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded p-4 mb-3">
                            <i class="bi bi-box-seam text-primary" style="font-size: 4rem;"></i>
                        </div>

                        <h4 class="mb-1">{{ $product->name }}</h4>
                        <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }} me-1">
                            {{ $product->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                        <span class="badge bg-info">
                            {{ $product->type == 'goods' ? 'بضاعة' : 'خدمة' }}
                        </span>

                        <!-- Action Buttons -->
                        <div class="mt-3 d-flex justify-content-center gap-2">
                            <a href="{{ route('barcodes.print', $product) }}" class="btn btn-outline-primary btn-sm"
                                target="_blank">
                                <i class="bi bi-upc-scan me-1"></i>طباعة الباركود
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil me-1"></i>تعديل
                            </a>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 40%;">SKU</td>
                            <td><strong>{{ $product->sku }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">الباركود</td>
                            <td dir="ltr" class="text-end">{{ $product->barcode ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">التصنيف</td>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الوحدة</td>
                            <td>{{ $product->unit?->name ?? '-' }}</td>
                        </tr>
                    </table>

                    <hr>

                    <h6 class="text-muted mb-3">التسعير</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">سعر التكلفة</td>
                            <td>{{ number_format($product->cost_price, 2) }} ج.م</td>
                        </tr>
                        <tr>
                            <td class="text-muted">سعر البيع</td>
                            <td class="fw-bold text-success">{{ number_format($product->selling_price, 2) }} ج.م</td>
                        </tr>
                        <tr>
                            <td class="text-muted">نسبة الضريبة</td>
                            <td>{{ $product->tax_rate }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stock Info -->
        <div class="col-lg-8">
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">إجمالي المخزون</p>
                                    <h4 class="mb-0">{{ number_format($totalStock ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-boxes fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">قيمة المخزون</p>
                                    <h4 class="mb-0">{{ number_format($stockValue ?? 0, 2) }}</h4>
                                </div>
                                <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div
                        class="card bg-{{ ($totalStock ?? 0) < $product->min_stock ? 'danger' : 'secondary' }} text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">حد إعادة الطلب</p>
                                    <h4 class="mb-0">{{ number_format($product->min_stock) }}</h4>
                                </div>
                                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock by Warehouse -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>المخزون حسب المستودع</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>المستودع</th>
                                    <th>الكمية</th>
                                    <th>المحجوز</th>
                                    <th>المتاح</th>
                                    <th>متوسط التكلفة</th>
                                    <th>القيمة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->stock ?? [] as $stockItem)
                                    <tr>
                                        <td>{{ $stockItem->warehouse?->name ?? 'غير محدد' }}</td>
                                        <td>{{ number_format($stockItem->quantity, 2) }}</td>
                                        <td>{{ number_format($stockItem->reserved_quantity, 2) }}</td>
                                        <td class="fw-bold">
                                            {{ number_format($stockItem->quantity - $stockItem->reserved_quantity, 2) }}
                                        </td>
                                        <td>{{ number_format($stockItem->average_cost, 4) }}</td>
                                        <td>{{ number_format($stockItem->quantity * $stockItem->average_cost, 2) }} ج.م</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا يوجد مخزون في أي مستودع
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>آخر حركات المخزون</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الرقم</th>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>الكمية</th>
                                    <th>المستودع</th>
                                    <th>المرجع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMovements ?? [] as $movement)
                                    <tr>
                                        <td>{{ $movement->movement_number }}</td>
                                        <td>{{ $movement->movement_date->format('Y-m-d') }}</td>
                                        <td>
                                            @php
                                                $typeLabels = [
                                                    'in' => ['label' => 'وارد', 'class' => 'success'],
                                                    'out' => ['label' => 'صادر', 'class' => 'danger'],
                                                    'transfer_in' => ['label' => 'تحويل وارد', 'class' => 'info'],
                                                    'transfer_out' => ['label' => 'تحويل صادر', 'class' => 'warning'],
                                                    'adjustment' => ['label' => 'تسوية', 'class' => 'secondary'],
                                                ];
                                                $type = $typeLabels[$movement->type->value] ?? ['label' => $movement->type->value, 'class' => 'secondary'];
                                            @endphp
                                            <span class="badge bg-{{ $type['class'] }}">{{ $type['label'] }}</span>
                                        </td>
                                        <td
                                            class="{{ in_array($movement->type->value, ['out', 'transfer_out']) ? 'text-danger' : 'text-success' }}">
                                            {{ in_array($movement->type->value, ['out', 'transfer_out']) ? '-' : '+' }}{{ number_format($movement->quantity, 2) }}
                                        </td>
                                        <td>{{ $movement->warehouse?->name ?? '-' }}</td>
                                        <td>{{ $movement->reference ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد حركات مخزون حتى الآن
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection