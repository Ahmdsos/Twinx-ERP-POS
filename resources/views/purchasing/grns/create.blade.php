@extends('layouts.app')

@section('title', 'استلام بضاعة جديد - Twinx ERP')
@section('page-title', 'سند استلام بضاعة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('grns.index') }}">سندات الاستلام</a></li>
    <li class="breadcrumb-item active">سند جديد</li>
@endsection

@section('content')
<form action="{{ route('grns.store') }}" method="POST" id="grn-form">
    @csrf
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-9">
            <!-- Select PO if not specified -->
            @if(!$purchaseOrder)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-cart me-2"></i>اختر أمر الشراء</h5>
                    </div>
                    <div class="card-body">
                        @if($purchaseOrders->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                لا توجد أوامر شراء في انتظار الاستلام.
                                <a href="{{ route('purchase-orders.create') }}" class="alert-link">
                                    إنشاء أمر شراء جديد
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>رقم الأمر</th>
                                            <th>المورد</th>
                                            <th>تاريخ الطلب</th>
                                            <th>الإجمالي</th>
                                            <th>الحالة</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrders as $po)
                                            <tr>
                                                <td>{{ $po->po_number }}</td>
                                                <td>{{ $po->supplier?->name }}</td>
                                                <td>{{ $po->order_date?->format('Y-m-d') }}</td>
                                                <td>{{ number_format($po->total, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-info">{{ $po->status->label() }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('grns.create', ['purchase_order_id' => $po->id]) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-box-seam me-1"></i>استلام
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- PO Info -->
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cart me-2"></i>أمر الشراء: {{ $purchaseOrder->po_number }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">المورد</p>
                                <p class="fw-bold">{{ $purchaseOrder->supplier?->name }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">تاريخ الطلب</p>
                                <p>{{ $purchaseOrder->order_date?->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">الإجمالي</p>
                                <p class="fw-bold">{{ number_format($purchaseOrder->total, 2) }} ج.م</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRN Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>بيانات الاستلام</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">المستودع <span class="text-danger">*</span></label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                        name="warehouse_id" required>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" 
                                                {{ $purchaseOrder->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">تاريخ الاستلام <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('received_date') is-invalid @enderror" 
                                       name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required>
                                @error('received_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">رقم مستند المورد</label>
                                <input type="text" class="form-control" 
                                       name="supplier_delivery_note" value="{{ old('supplier_delivery_note') }}"
                                       placeholder="رقم مستند التسليم من المورد">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items to Receive -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>الأصناف المستلمة</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">المنتج</th>
                                        <th>الكمية المطلوبة</th>
                                        <th>المستلم سابقاً</th>
                                        <th>الكمية المتاحة</th>
                                        <th>الكمية للاستلام</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->lines as $index => $line)
                                        @php
                                            $remaining = $line->quantity - $line->received_quantity;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $line->product?->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $line->product?->sku }}</small>
                                                <input type="hidden" name="lines[{{ $index }}][purchase_order_line_id]" 
                                                       value="{{ $line->id }}">
                                            </td>
                                            <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                                            <td>{{ number_format($line->received_quantity, 2) }}</td>
                                            <td class="fw-bold text-info">{{ number_format($remaining, 2) }}</td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       name="lines[{{ $index }}][quantity]" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="{{ $remaining }}"
                                                       value="{{ $remaining > 0 ? $remaining : 0 }}"
                                                       {{ $remaining <= 0 ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="أي ملاحظات على الاستلام...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            @if($purchaseOrder)
                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg me-2"></i>تأكيد الاستلام
                            </button>
                            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-right me-2"></i>العودة لأمر الشراء
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="text-info"><i class="bi bi-info-circle me-1"></i>تعليمات</h6>
                        <small class="text-muted">
                            <ul class="ps-3 mb-0">
                                <li>أدخل الكميات المستلمة فعلياً</li>
                                <li>يمكن الاستلام على دفعات</li>
                                <li>سيتم تحديث المخزون تلقائياً</li>
                            </ul>
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>
@endsection
