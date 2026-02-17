@extends('layouts.app')

@section('title', 'فاتورة مبيعات #' . $salesInvoice->invoice_number)

@section('content')
    <div class="container-fluid p-0">

        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box-lg bg-gradient-to-br from-green-600 to-teal-700 rounded-circle shadow-lg text-white">
                    <i class="bi bi-receipt-cutoff fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <h2 class="fw-bold text-heading mb-0">{{ $salesInvoice->invoice_number }}</h2>
                        <span
                            class="badge {{ $salesInvoice->status == \Modules\Sales\Enums\SalesInvoiceStatus::PAID ? 'bg-success' : ($salesInvoice->isOverdue() ? 'bg-danger' : 'bg-warning') }} border border-secondary border-opacity-10/20 fs-6 px-3 py-2 rounded-pill shadow-sm">
                            {{ $salesInvoice->status->label() }}
                        </span>
                    </div>
                    <div class="d-flex gap-3 text-gray-400 small">
                        <span><i class="bi bi-calendar me-1"></i> التاريخ: <span
                                class="text-body">{{ $salesInvoice->invoice_date->format('Y-m-d') }}</span></span>
                        <span class="{{ $salesInvoice->isOverdue() ? 'text-danger fw-bold' : '' }}"><i
                                class="bi bi-clock-history me-1"></i> الاستحقاق: <span
                                class="text-body">{{ $salesInvoice->due_date->format('Y-m-d') }}</span></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('sales-invoices.index') }}" class="btn btn-glass-outline rounded-pill">
                    <i class="bi bi-arrow-right me-2"></i> القائمة
                </a>

                <div class="btn-group shadow-lg rounded-pill">
                    <a href="{{ route('sales-invoices.print', $salesInvoice->id) }}" target="_blank"
                        class="btn btn-dark border-start border-secondary border-opacity-10/10 text-info hover-bg-info-dark">
                        <i class="bi bi-printer-fill me-2"></i>{{ __('Print') }}</a>

                    <a href="{{ route('sales-invoices.edit', $salesInvoice->id) }}"
                        class="btn btn-dark border-start border-secondary border-opacity-10/10 text-warning hover-bg-warning-dark">
                        <i class="bi bi-pencil-square me-2"></i>{{ __('Edit') }}</a>

                    @if($salesInvoice->balance_due > 0)
                        <a href="{{ route('customer-payments.create', ['customer_id' => $salesInvoice->customer_id, 'invoice_id' => $salesInvoice->id]) }}"
                            class="btn btn-success fw-bold px-4 hover-scale text-white">
                            <i class="bi bi-cash-stack me-2"></i> تسجيل دفع
                        </a>
                    @endif

                    <button type="button" class="btn btn-dark border-start border-secondary border-opacity-10/10 text-gray-400"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border border-secondary border-opacity-10/10">

                        <li><a class="dropdown-item text-danger"
                                href="{{ route('sales-returns.create', ['invoice_id' => $salesInvoice->id, 'customer_id' => $salesInvoice->customer_id]) }}">
                                <i class="bi bi-arrow-return-left me-2"></i> مرتجع مبيعات
                            </a></li>
                    </ul>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
                        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                            return new bootstrap.Dropdown(dropdownToggleEl)
                        })
                    });
                </script>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Items Table -->
            <div class="glass-panel p-0 rounded-4 overflow-hidden border border-secondary border-opacity-10/10 shadow-lg mb-4">
                <div class="bg-surface/5 p-4 border-bottom border-secondary border-opacity-10/10 d-flex justify-content-between">
                    <h5 class="fw-bold text-heading mb-0"><i class="bi bi-list-check me-2 text-info"></i> بنود الفاتورة
                    </h5>
                    @if($salesInvoice->deliveryOrder)
                        <span class="badge bg-surface/10 text-gray-300">أمر تسليم:
                            {{ $salesInvoice->deliveryOrder->delivery_number }}</span>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="bg-gray-900/50 text-gray-400 text-uppercase small">
                            <tr>
                                <th class="ps-4 py-3">{{ __('Product') }}</th>
                                <th class="text-center py-3">{{ __('Quantity') }}</th>
                                <th class="text-center py-3">{{ __('Price') }}</th>
                                <th class="text-center py-3">{{ __('Discount') }}</th>
                                <th class="text-end pe-4 py-3">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesInvoice->lines as $line)
                                <tr class="hover:bg-surface/5 border-bottom border-secondary border-opacity-10/5">
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-body">{{ $line->description }}</div>
                                        <small class="text-gray-500">{{ $line->product->code ?? '-' }}</small>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge bg-surface/10 text-body border border-secondary border-opacity-10/10 rounded-pill px-3">
                                            {{ $line->quantity + 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center py-3 text-gray-300">{{ number_format($line->unit_price, 2) }}
                                    </td>
                                    <td class="text-center py-3 text-danger fs-small">
                                        {{ $line->discount_amount > 0 ? '-' . number_format($line->discount_amount, 2) : '-' }}
                                    </td>
                                    <td class="text-end pe-4 py-3 fw-bold text-body">
                                        {{ number_format($line->line_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-900/50">
                            <tr>
                                <td colspan="3"></td>
                                <td class="text-end py-3 text-gray-400 small text-uppercase">{{ __('Subtotal') }}</td>
                                <td class="text-end pe-4 py-3 fw-bold text-body">
                                    {{ number_format($salesInvoice->subtotal, 2) }}
                                </td>
                            </tr>
                            @if($salesInvoice->discount_amount > 0)
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-end py-2 text-warning small">خصم إضافي</td>
                                    <td class="text-end pe-4 py-2 text-warning">
                                        -{{ number_format($salesInvoice->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3"></td>
                                <td class="text-end py-2 text-gray-400 small">{{ __('Tax') }}</td>
                                <td class="text-end pe-4 py-2 text-gray-300">
                                    {{ number_format($salesInvoice->tax_amount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td class="text-end py-4 text-body fs-5 fw-bold">الإجمالي المستحق</td>
                                <td class="text-end pe-4 py-4 text-success fs-4 fw-bold text-glow">
                                    {{ number_format($salesInvoice->total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="glass-panel p-4 rounded-4 border border-secondary border-opacity-10/10 shadow-lg mb-4 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-green-500/10"></div>
                <h5 class="fw-bold text-heading mb-4 border-bottom border-secondary border-opacity-10/10 pb-3">ملخص السداد</h5>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-gray-400">{{ __('Paid Amount') }}</span>
                        <span class="text-success fw-bold">{{ number_format($salesInvoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="progress bg-surface/10" style="height: 8px;">
                        @php $percent = $salesInvoice->total > 0 ? min(100, ($salesInvoice->paid_amount / $salesInvoice->total) * 100) : 0; @endphp
                        <div class="progress-bar {{ $salesInvoice->balance_due <= 0 ? 'bg-success' : 'bg-warning' }}"
                            role="progressbar" style="width: {{ $percent }}%"></div>
                    </div>
                </div>

                @if($salesInvoice->paid_amount > $salesInvoice->total)
                    <div
                        class="alert alert-warning bg-warning/10 border-warning/20 text-warning d-flex align-items-center mb-0">
                        <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                        <div>
                            <div class="fw-bold">يوجد رصيد زيادة</div>
                            <div class="small opacity-75">المبلغ الزائد:
                                {{ number_format($salesInvoice->paid_amount - $salesInvoice->total, 2) }}</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-gray-400">{{ __('Balance Due') }}</span>
                        <span class="text-body fw-bold">{{ number_format($salesInvoice->balance_due, 2) }}</span>
                    </div>
                @endif
            </div>

            <!-- Customer Card -->
            <div class="glass-panel p-4 rounded-4 border border-secondary border-opacity-10/10 shadow-lg mb-4">
                <h5 class="fw-bold text-heading mb-4 border-bottom border-secondary border-opacity-10/10 pb-3">معلومات العميل</h5>

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="avatar-circle bg-gradient-to-br from-gray-700 to-gray-800 text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                        style="width: 50px; height: 50px;">
                        <span class="fs-5 fw-bold">{{ substr(optional($salesInvoice->customer)->name ?? '?', 0, 1) }}</span>
                    </div>
                    <div>
                        <h5 class="fw-bold text-heading mb-0">
                            {{ optional($salesInvoice->customer)->name ?? 'Deleted Customer' }}
                        </h5>
                        <small
                            class="text-gray-400">{{ optional($salesInvoice->customer)->phone ?? 'لا يوجد هاتف' }}</small>
                    </div>
                </div>
            </div>

            <!-- Meta Info -->
            <div class="glass-panel p-4 rounded-4 border border-secondary border-opacity-10/10 shadow-lg">
                <h5 class="fw-bold text-heading mb-4 border-bottom border-secondary border-opacity-10/10 pb-3">معلومات داخلية</h5>
                <div class="vstack gap-3 small">
                    <div class="d-flex justify-content-between text-gray-400">
                        <span>المنشئ:</span>
                        <span class="text-body">Admin User</span>
                    </div>
                    <div class="d-flex justify-content-between text-gray-400">
                        <span>تاريخ الإنشاء:</span>
                        <span class="text-body">{{ $salesInvoice->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <style>
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
        }

        .icon-box-lg {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
        }

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            color: var(--text-primary);
        }

        .btn-glass-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .hover-bg-warning-dark:hover {
            background-color: #78350f !important;
            color: #fbbf24 !important;
        }

        .hover-bg-info-dark:hover {
            background-color: #0c4a6e !important;
            color: #38bdf8 !important;
        }
    </style>
@endsection