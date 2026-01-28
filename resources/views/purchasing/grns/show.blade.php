@extends('layouts.app')

@section('title', $grn->grn_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل سند الاستلام')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('grns.index') }}">سندات الاستلام</a></li>
    <li class="breadcrumb-item active">{{ $grn->grn_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- GRN Header Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>
                        {{ $grn->grn_number }}
                    </h5>
                    @php
                        $statusColors = [
                            'draft' => 'secondary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$grn->status->value] ?? 'secondary' }} fs-6">
                        {{ $grn->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">المورد</td>
                                    <td>
                                        <strong>{{ $grn->supplier?->name ?? '-' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">أمر الشراء</td>
                                    <td>
                                        @if($grn->purchaseOrder)
                                            <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}">
                                                {{ $grn->purchaseOrder->po_number }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المستودع</td>
                                    <td>{{ $grn->warehouse?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">تاريخ الاستلام</td>
                                    <td>{{ $grn->received_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">مستند المورد</td>
                                    <td>{{ $grn->supplier_delivery_note ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">مستلم بواسطة</td>
                                    <td>{{ $grn->receiver?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRN Lines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>الأصناف المستلمة</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>سعر الوحدة</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grn->lines as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $line->product?->name ?? '-' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->product?->sku }}</small>
                                        </td>
                                        <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                                        <td>{{ number_format($line->unit_price ?? 0, 2) }}</td>
                                        <td class="fw-bold">{{ number_format($line->line_total ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="table-primary">
                                    <td colspan="4" class="text-start"><strong>إجمالي القيمة</strong></td>
                                    <td><strong>{{ number_format($grn->getTotalValue(), 2) }} ج.م</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($grn->notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $grn->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>الإجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($grn->purchaseOrder)
                            <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}" class="btn btn-outline-primary">
                                <i class="bi bi-cart me-2"></i>عرض أمر الشراء
                            </a>
                        @endif

                        @if($grn->canEdit())
                            <form action="{{ route('grns.cancel', $grn) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('هل أنت متأكد من إلغاء هذا السند؟')">
                                    <i class="bi bi-x-lg me-2"></i>إلغاء السند
                                </button>
                            </form>
                        @endif

                        <hr>
                        <a href="{{ route('grns.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Document Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>معلومات المستند</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">تاريخ الإنشاء</td>
                            <td>{{ $grn->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">آخر تحديث</td>
                            <td>{{ $grn->updated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection