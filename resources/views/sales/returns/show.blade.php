@extends('layouts.app')

@section('title', 'تفاصيل مرتجع المبيعات')

@section('content')
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    مرتجع مبيعات <span class="text-info font-monospace">#{{ $salesReturn->return_number }}</span>
                </h4>
                <div class="text-muted small">
                    <i class="bi bi-calendar-event me-1"></i> {{ $salesReturn->return_date->format('Y-m-d') }}
                    <span class="mx-2">|</span>
                    <i class="bi bi-person me-1"></i> {{ $salesReturn->customer->name }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('sales-returns.index') }}" class="btn btn-glass-outline">عودة للقائمة</a>
                <button onclick="window.print()" class="btn btn-glass-outline">
                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}</button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-md-8">
            <div class="glass-card p-4 h-100">
                <div
                    class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-3">
                    <h5 class="text-heading mb-0">الأصناف المرتجعة</h5>
                    <span
                        class="badge bg-{{ $salesReturn->status->color() }} bg-opacity-20 text-{{ $salesReturn->status->color() }} px-3 py-2 rounded-pill border border-{{ $salesReturn->status->color() }} border-opacity-20">
                        {{ $salesReturn->status->label() }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle text-body mb-0">
                        <thead class="text-secondary-50 small border-bottom border-secondary border-opacity-10 border-opacity-10">
                            <tr>
                                <th class="py-3">{{ __('Product') }}</th>
                                <th class="py-3 text-center">{{ __('Status') }}</th>
                                <th class="py-3 text-center">{{ __('Quantity') }}</th>
                                <th class="py-3 text-end">{{ __('Unit Price') }}</th>
                                <th class="py-3 text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesReturn->lines as $line)
                                <tr class="border-bottom border-secondary border-opacity-10 border-opacity-5">
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $line->product->name }}</div>
                                        <div class="small text-muted font-monospace">{{ $line->product->code }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($line->item_condition == 'resalable')
                                            <span class="badge bg-success bg-opacity-10 text-success">صالح للبيع</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger">تالف</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace">{{ $line->quantity }}</td>
                                    <td class="text-end font-monospace">{{ number_format($line->unit_price, 2) }}</td>
                                    <td class="text-end font-monospace fw-bold text-info">
                                        {{ number_format($line->line_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top border-secondary border-opacity-10 border-opacity-10 bg-surface bg-opacity-5">
                            <tr>
                                <td colspan="4" class="text-end py-3 text-muted">الإجمالي (قبل الضريبة)</td>
                                <td class="text-end py-3 font-monospace">{{ number_format($salesReturn->subtotal, 2) }}</td>
                            </tr>
                            @if($salesReturn->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end py-2 text-muted">{{ __('Tax') }}</td>
                                    <td class="text-end py-2 font-monospace">{{ number_format($salesReturn->tax_amount, 2) }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end py-3 fw-bold text-body fs-5">{{ __('Grand Total') }}</td>
                                <td class="text-end py-3 fw-bold text-info fs-5 font-monospace">
                                    {{ number_format($salesReturn->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($salesReturn->notes)
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 border-opacity-10">
                        <label class="text-muted small mb-2">ملاحظات:</label>
                        <p class="text-body bg-surface bg-opacity-5 p-3 rounded-3 mb-0">{{ $salesReturn->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-md-4">
            <div class="glass-card p-4 mb-4">
                <h6 class="text-heading-50 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-2 mb-3">معلومات إضافية</h6>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-gray-400">المخزن المستلم</span>
                    <span class="text-body fw-medium">{{ $salesReturn->warehouse->name }}</span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-gray-400">{{ __('Created At') }}</span>
                    <span class="text-body">{{ $salesReturn->created_at->format('Y-m-d h:i A') }}</span>
                </div>

                @if($salesReturn->salesInvoice)
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-gray-400">مرتبط بالفاتورة</span>
                        <a href="{{ route('sales-invoices.show', $salesReturn->salesInvoice->id) }}"
                            class="text-info text-decoration-none">
                            #{{ $salesReturn->salesInvoice->invoice_number }}
                        </a>
                    </div>
                @endif
            </div>

            @if($salesReturn->status === \Modules\Sales\Enums\SalesReturnStatus::DRAFT)
                <div class="glass-card p-4 text-center">
                    <h6 class="text-heading mb-3">إجراءات الاعتماد</h6>
                    <p class="text-body-50 small mb-4">يمكنك اعتماد المرتجع ليتم التأثير على المخزون والحسابات.</p>

                    <div class="d-grid gap-2">
                        <form action="{{ route('sales-returns.approve', $salesReturn) }}" method="POST"
                            data-confirm="هل أنت متأكد من اعتماد المرتجع؟ سيتم إعادة الكميات الصالحة للمخزون.">
                            @csrf
                            <button type="submit" class="btn btn-success fw-bold w-100">
                                <i class="bi bi-check-circle me-2"></i> اعتماد المرتجع
                            </button>
                        </form>
                        <!-- <button class="btn btn-outline-danger">رفض</button> -->
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }

        .text-gray-300 {
            color: #cbd5e1;
        }

        .text-gray-400 {
            color: var(--text-secondary);
        }
    </style>
@endsection