@extends('layouts.app')

@section('title', $quotation->quotation_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل عرض السعر')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}">عروض الأسعار</a></li>
    <li class="breadcrumb-item active">{{ $quotation->quotation_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        {{ $quotation->quotation_number }}
                    </h5>
                    <span class="badge bg-{{ $quotation->status->color() }} fs-6">
                        <i class="bi {{ $quotation->status->icon() }} me-1"></i>
                        {{ $quotation->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">العميل</td>
                                    <td><strong>{{ $quotation->customer?->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">كود العميل</td>
                                    <td>{{ $quotation->customer?->code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">تاريخ العرض</td>
                                    <td>{{ $quotation->quotation_date?->format('Y-m-d') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">صالح حتى</td>
                                    <td>
                                        {{ $quotation->valid_until?->format('Y-m-d') }}
                                        @if($quotation->isExpired())
                                            <span class="badge bg-warning text-dark">منتهي</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">بواسطة</td>
                                    <td>{{ $quotation->creator?->name ?? '-' }}</td>
                                </tr>
                                @if($quotation->salesOrder)
                                    <tr>
                                        <td class="text-muted">أمر البيع</td>
                                        <td>
                                            <a href="{{ route('sales-orders.show', $quotation->salesOrder) }}">
                                                {{ $quotation->salesOrder->order_number }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lines Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>الأصناف</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الصنف</th>
                                    <th>الكمية</th>
                                    <th>سعر الوحدة</th>
                                    <th>الخصم %</th>
                                    <th class="text-start">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->lines as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $line->product?->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->product?->sku }}</small>
                                        </td>
                                        <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                                        <td>{{ number_format($line->unit_price ?? 0, 2) }}</td>
                                        <td>{{ $line->discount_percent }}%</td>
                                        <td class="text-start fw-bold">{{ number_format($line->line_total ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-start"><strong>الإجمالي الفرعي</strong></td>
                                    <td class="text-start">{{ number_format($quotation->subtotal, 2) }} ج.م</td>
                                </tr>
                                @if($quotation->tax_amount > 0)
                                    <tr>
                                        <td colspan="5" class="text-start">الضريبة</td>
                                        <td class="text-start">{{ number_format($quotation->tax_amount, 2) }} ج.م</td>
                                    </tr>
                                @endif
                                @if($quotation->discount_amount > 0)
                                    <tr>
                                        <td colspan="5" class="text-start">الخصم</td>
                                        <td class="text-start text-danger">-{{ number_format($quotation->discount_amount, 2) }}
                                            ج.م</td>
                                    </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="5" class="text-start"><strong>الإجمالي</strong></td>
                                    <td class="text-start"><strong>{{ number_format($quotation->total, 2) }} ج.م</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes & Terms -->
            @if($quotation->notes || $quotation->terms)
                <div class="row">
                    @if($quotation->notes)
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0 text-muted">{{ $quotation->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($quotation->terms)
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>الشروط والأحكام</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0 text-muted">{{ $quotation->terms }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
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
                        @if($quotation->status->canEdit())
                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-secondary">
                                <i class="bi bi-pencil me-2"></i>تعديل العرض
                            </a>
                        @endif

                        @if($quotation->status->value === 'draft')
                            <form action="{{ route('quotations.send', $quotation) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="bi bi-send me-2"></i>إرسال للعميل
                                </button>
                            </form>
                        @endif

                        @if(in_array($quotation->status->value, ['draft', 'sent']))
                            <form action="{{ route('quotations.accept', $quotation) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>قبول العرض
                                </button>
                            </form>
                            <form action="{{ route('quotations.reject', $quotation) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('رفض عرض السعر؟')">
                                    <i class="bi bi-x-lg me-2"></i>رفض العرض
                                </button>
                            </form>
                        @endif

                        @if($quotation->status->canConvert())
                            <hr>
                            <form action="{{ route('quotations.convert', $quotation) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100"
                                    onclick="return confirm('تحويل العرض إلى أمر بيع؟')">
                                    <i class="bi bi-arrow-right-circle me-2"></i>تحويل لأمر بيع
                                </button>
                            </form>
                        @endif

                        <hr>
                        <a href="{{ route('quotations.print', $quotation) }}" class="btn btn-outline-primary"
                            target="_blank">
                            <i class="bi bi-printer me-2"></i>طباعة
                        </a>
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
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
                            <td>{{ $quotation->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if($quotation->approved_at)
                            <tr>
                                <td class="text-muted">تاريخ القبول</td>
                                <td>{{ $quotation->approved_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">تم القبول بواسطة</td>
                                <td>{{ $quotation->approver?->name ?? '-' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection