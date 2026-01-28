@extends('layouts.app')

@section('title', $warehouse->name . ' - Twinx ERP')
@section('page-title', 'تفاصيل المستودع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">المستودعات</a></li>
    <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Warehouse Info -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>معلومات المستودع</h5>
                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-building"></i>
                        </div>
                        <h4 class="mb-1">{{ $warehouse->name }}</h4>
                        <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'secondary' }} me-1">
                            {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                        @if($warehouse->is_default)
                            <span class="badge bg-primary">الافتراضي</span>
                        @endif
                    </div>

                    <hr>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 35%;">الكود</td>
                            <td><strong>{{ $warehouse->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">العنوان</td>
                            <td>{{ $warehouse->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الهاتف</td>
                            <td>{{ $warehouse->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">البريد الإلكتروني</td>
                            <td>{{ $warehouse->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stock Summary -->
        <div class="col-lg-8">
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">عدد الأصناف</p>
                                    <h4 class="mb-0">{{ $warehouse->stocks_count ?? 0 }}</h4>
                                </div>
                                <i class="bi bi-box-seam fs-1 opacity-50"></i>
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
                                    <h4 class="mb-0">{{ number_format($totalValue ?? 0, 2) }}</h4>
                                </div>
                                <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">إجمالي الكمية</p>
                                    <h4 class="mb-0">{{ number_format($stocks->sum('quantity') ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-boxes fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Items -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-boxes me-2"></i>الأصناف في المستودع</h5>
                    <input type="text" class="form-control form-control-sm" style="width: 200px;" placeholder="بحث..."
                        id="stockSearch">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="stocksTable">
                            <thead class="table-light">
                                <tr>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>المحجوز</th>
                                    <th>المتاح</th>
                                    <th>متوسط التكلفة</th>
                                    <th>القيمة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks ?? [] as $stock)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $stock->product) }}"
                                                class="text-decoration-none">
                                                {{ $stock->product->name }}
                                            </a>
                                            <br><small class="text-muted">{{ $stock->product->sku }}</small>
                                        </td>
                                        <td>{{ number_format($stock->quantity, 2) }}</td>
                                        <td>{{ number_format($stock->reserved_quantity, 2) }}</td>
                                        <td class="fw-bold">{{ number_format($stock->available_quantity, 2) }}</td>
                                        <td>{{ number_format($stock->average_cost, 4) }}</td>
                                        <td>{{ number_format($stock->quantity * $stock->average_cost, 2) }} ج.م</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد أصناف في هذا المستودع
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(method_exists($stocks ?? collect(), 'links'))
                    <div class="card-footer">
                        {{ $stocks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('stockSearch').addEventListener('input', function (e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#stocksTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });
    </script>
@endpush