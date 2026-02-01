@extends('layouts.app')

@section('title', 'فاتورة شراء جديدة - Twinx ERP')
@section('page-title', 'إنشاء فاتورة شراء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-invoices.index') }}">فواتير الشراء</a></li>
    <li class="breadcrumb-item active">فاتورة جديدة</li>
@endsection

@section('content')
<form action="{{ route('purchase-invoices.store') }}" method="POST" id="invoice-form">
    @csrf
    
    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-9">
            <!-- Select GRN if not specified -->
            @if(!$grn)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>اختر سند الاستلام</h5>
                    </div>
                    <div class="card-body">
                        @if($grns->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                لا توجد سندات استلام متاحة للفوترة.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>رقم السند</th>
                                            <th>المورد</th>
                                            <th>تاريخ الاستلام</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($grns as $grnItem)
                                            <tr>
                                                <td>{{ $grnItem->grn_number }}</td>
                                                <td>{{ $grnItem->supplier?->name }}</td>
                                                <td>{{ $grnItem->received_date?->format('Y-m-d') }}</td>
                                                <td>
                                                    <a href="{{ route('purchase-invoices.create', ['grn_id' => $grnItem->id]) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-file-earmark me-1"></i>إنشاء فاتورة
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
                <!-- GRN Info -->
                <input type="hidden" name="grn_id" value="{{ $grn->id }}">
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>سند الاستلام: {{ $grn->grn_number }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">المورد</p>
                                <p class="fw-bold">{{ $grn->supplier?->name }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">تاريخ الاستلام</p>
                                <p>{{ $grn->received_date?->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">أمر الشراء</p>
                                <p>{{ $grn->purchaseOrder?->po_number ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark me-2"></i>بيانات الفاتورة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">رقم فاتورة المورد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('supplier_invoice_number') is-invalid @enderror" 
                                       name="supplier_invoice_number" value="{{ old('supplier_invoice_number') }}" required
                                       placeholder="رقم الفاتورة من المورد">
                                @error('supplier_invoice_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                       name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">تاريخ الاستحقاق <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                       name="due_date" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>بنود الفاتورة</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">المنتج</th>
                                        <th>الكمية</th>
                                        <th>السعر</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach($grn->lines as $line)
                                        @php 
                                            $lineTotal = $line->quantity * ($line->unit_price ?? 0);
                                            $subtotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $line->product?->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $line->product?->sku }}</small>
                                            </td>
                                            <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                                            <td>{{ number_format($line->unit_price ?? 0, 2) }}</td>
                                            <td class="fw-bold">{{ number_format($lineTotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="table-primary">
                                        <td colspan="3" class="text-start"><strong>الإجمالي</strong></td>
                                        <td><strong>{{ number_format($subtotal, 2) }} ج.م</strong></td>
                                    </tr>
                                </tfoot>
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
                                  placeholder="ملاحظات على الفاتورة...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            @if($grn)
                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg me-2"></i>إنشاء الفاتورة
                            </button>
                            <a href="{{ route('purchase-invoices.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x me-2"></i>إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>
@endsection
