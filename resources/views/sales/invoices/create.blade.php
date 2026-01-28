@extends('layouts.app')

@section('title', 'فاتورة جديدة - Twinx ERP')
@section('page-title', 'إنشاء فاتورة بيع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales-invoices.index') }}">فواتير البيع</a></li>
    <li class="breadcrumb-item active">فاتورة جديدة</li>
@endsection

@section('content')
<form action="{{ route('sales-invoices.store') }}" method="POST" id="invoice-form">
    @csrf
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Select Delivery Order (if not preselected) -->
            @if(!$deliveryOrder)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>اختيار أمر التسليم</h5>
                    </div>
                    <div class="card-body">
                        @if($deliveredOrders->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                لا توجد أوامر تسليم مكتملة بدون فواتير
                            </div>
                        @else
                            <select class="form-select" name="delivery_order_id" id="delivery_order_select" required>
                                <option value="">اختر أمر تسليم...</option>
                                @foreach($deliveredOrders as $do)
                                    <option value="{{ $do->id }}">
                                        {{ $do->do_number }} - {{ $do->salesOrder?->so_number }} 
                                        ({{ $do->salesOrder?->customer?->name }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2 text-muted">
                                <small>يتم عرض أوامر التسليم المكتملة التي لم يتم فوترتها بعد</small>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <input type="hidden" name="delivery_order_id" value="{{ $deliveryOrder->id }}">
            @endif

            <!-- Invoice Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        معلومات الفاتورة
                        @if($deliveryOrder)
                            <span class="badge bg-info ms-2">{{ $deliveryOrder->do_number }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                   name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">تاريخ الاستحقاق <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                   name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">شروط الدفع</label>
                            <textarea class="form-control" name="terms" rows="2" 
                                      placeholder="مثال: الدفع خلال 30 يوم من تاريخ الفاتورة">{{ old('terms') }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Lines Preview -->
            @if($deliveryOrder)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>أصناف الفاتورة</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحدة</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach($deliveryOrder->lines as $line)
                                        @php 
                                            $soLine = $deliveryOrder->salesOrder?->lines->where('product_id', $line->product_id)->first();
                                            $price = $soLine?->unit_price ?? 0;
                                            $lineTotal = $line->quantity * $price;
                                            $subtotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $line->product?->name ?? '-' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $line->product?->sku ?? '' }}</small>
                                            </td>
                                            <td>{{ number_format($line->quantity, 2) }}</td>
                                            <td>{{ number_format($price, 2) }}</td>
                                            <td><strong>{{ number_format($lineTotal, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fs-5">
                                        <td colspan="3" class="text-start"><strong>الإجمالي</strong></td>
                                        <td class="text-primary"><strong>{{ number_format($subtotal, 2) }} ج.م</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            @if($deliveryOrder)
                <!-- Customer Info -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>معلومات العميل</h6>
                    </div>
                    <div class="card-body">
                        <h6>{{ $deliveryOrder->salesOrder?->customer?->name }}</h6>
                        <p class="mb-1 text-muted">{{ $deliveryOrder->salesOrder?->customer?->code }}</p>
                        @if($deliveryOrder->salesOrder?->customer?->phone)
                            <p class="mb-1"><i class="bi bi-phone me-1"></i>{{ $deliveryOrder->salesOrder?->customer?->phone }}</p>
                        @endif
                        @if($deliveryOrder->salesOrder?->customer?->email)
                            <p class="mb-0"><i class="bi bi-envelope me-1"></i>{{ $deliveryOrder->salesOrder?->customer?->email }}</p>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-cart me-2"></i>المستندات المرتبطة</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>أمر البيع:</span>
                            <a href="{{ route('sales-orders.show', $deliveryOrder->sales_order_id) }}">
                                <strong>{{ $deliveryOrder->salesOrder?->so_number }}</strong>
                            </a>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>أمر التسليم:</span>
                            <a href="{{ route('deliveries.show', $deliveryOrder) }}">
                                <strong>{{ $deliveryOrder->do_number }}</strong>
                            </a>
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
                        <button type="submit" class="btn btn-primary btn-lg" {{ $deliveryOrder ? '' : 'disabled' }}>
                            <i class="bi bi-save me-2"></i>إنشاء الفاتورة
                        </button>
                        <a href="{{ route('sales-invoices.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@if(!$deliveryOrder)
@push('scripts')
<script>
document.getElementById('delivery_order_select')?.addEventListener('change', function() {
    if (this.value) {
        window.location.href = '{{ route("sales-invoices.create") }}?delivery_order_id=' + this.value;
    }
});
</script>
@endpush
@endif
