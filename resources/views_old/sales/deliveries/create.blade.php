@extends('layouts.app')

@section('title', 'أمر تسليم جديد - Twinx ERP')
@section('page-title', 'إنشاء أمر تسليم')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">أوامر التسليم</a></li>
    <li class="breadcrumb-item active">أمر جديد</li>
@endsection

@section('content')
<form action="{{ route('deliveries.store') }}" method="POST" id="delivery-form">
    @csrf
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Select Sales Order (if not preselected) -->
            @if(!$salesOrder)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cart me-2"></i>اختيار أمر البيع</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select" name="sales_order_id" id="sales_order_select" required>
                            <option value="">اختر أمر بيع...</option>
                            @foreach($salesOrders as $so)
                                <option value="{{ $so->id }}" 
                                        data-customer="{{ $so->customer?->name }}"
                                        data-warehouse="{{ $so->warehouse_id }}">
                                    {{ $so->so_number }} - {{ $so->customer?->name }} ({{ number_format($so->total, 2) }} ج.م)
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-2 text-muted">
                            <small>يتم عرض أوامر البيع المؤكدة فقط والجاهزة للتسليم</small>
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="sales_order_id" value="{{ $salesOrder->id }}">
            @endif

            <!-- Delivery Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-truck me-2"></i>
                        معلومات التسليم
                        @if($salesOrder)
                            <span class="badge bg-primary ms-2">{{ $salesOrder->so_number }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">المستودع <span class="text-danger">*</span></label>
                            <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                    name="warehouse_id" id="warehouse_id" required>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ ($salesOrder?->warehouse_id ?? old('warehouse_id')) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاريخ التسليم <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                   name="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">طريقة الشحن</label>
                            <input type="text" class="form-control" 
                                   name="shipping_method" value="{{ old('shipping_method', $salesOrder?->shipping_method) }}" 
                                   placeholder="مثال: توصيل محلي">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">اسم السائق</label>
                            <input type="text" class="form-control" 
                                   name="driver_name" value="{{ old('driver_name') }}" 
                                   placeholder="اسم المندوب">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">رقم المركبة</label>
                            <input type="text" class="form-control" 
                                   name="vehicle_number" value="{{ old('vehicle_number') }}" 
                                   placeholder="رقم السيارة">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">عنوان التسليم</label>
                            <input type="text" class="form-control" 
                                   name="shipping_address" value="{{ old('shipping_address', $salesOrder?->shipping_address) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items to Deliver -->
            @if($salesOrder)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>الأصناف المطلوب تسليمها</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المنتج</th>
                                        <th style="width: 15%;">الكمية المطلوبة</th>
                                        <th style="width: 15%;">تم تسليمها</th>
                                        <th style="width: 15%;">المتبقي</th>
                                        <th style="width: 15%;">كمية التسليم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesOrder->lines as $index => $line)
                                        @php
                                            $remaining = $line->quantity - $line->delivered_quantity;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $line->product?->name ?? '-' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $line->product?->sku ?? '' }}</small>
                                                <input type="hidden" name="lines[{{ $index }}][sales_order_line_id]" value="{{ $line->id }}">
                                            </td>
                                            <td>{{ number_format($line->quantity, 2) }}</td>
                                            <td>
                                                @if($line->delivered_quantity > 0)
                                                    <span class="text-success">{{ number_format($line->delivered_quantity, 2) }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="{{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                                                    {{ number_format($remaining, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control delivery-qty" 
                                                       name="lines[{{ $index }}][quantity]" 
                                                       value="{{ $remaining }}"
                                                       min="0" 
                                                       max="{{ $remaining }}"
                                                       step="0.01"
                                                       {{ $remaining <= 0 ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    اختر أمر بيع أولاً لعرض الأصناف المطلوب تسليمها
                </div>
            @endif

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="notes" rows="3" 
                              placeholder="ملاحظات التسليم...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            @if($salesOrder)
                <!-- Customer Info -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>معلومات العميل</h6>
                    </div>
                    <div class="card-body">
                        <h6>{{ $salesOrder->customer?->name }}</h6>
                        <p class="mb-1 text-muted">{{ $salesOrder->customer?->code }}</p>
                        @if($salesOrder->customer?->phone)
                            <p class="mb-0"><i class="bi bi-phone me-1"></i>{{ $salesOrder->customer?->phone }}</p>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-cart me-2"></i>ملخص الأمر</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>أمر البيع:</span>
                            <strong>{{ $salesOrder->so_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>تاريخ الأمر:</span>
                            <strong>{{ $salesOrder->order_date->format('Y-m-d') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>إجمالي الأمر:</span>
                            <strong>{{ number_format($salesOrder->total, 2) }} ج.م</strong>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" {{ $salesOrder ? '' : 'disabled' }}>
                            <i class="bi bi-save me-2"></i>إنشاء أمر التسليم
                        </button>
                        <a href="{{ route('deliveries.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@if(!$salesOrder)
@push('scripts')
<script>
document.getElementById('sales_order_select').addEventListener('change', function() {
    if (this.value) {
        window.location.href = '{{ route("deliveries.create") }}?sales_order_id=' + this.value;
    }
});
</script>
@endpush
@endif
