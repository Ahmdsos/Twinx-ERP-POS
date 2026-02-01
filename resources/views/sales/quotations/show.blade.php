@extends('layouts.app')

@section('title', 'عرض سعر ' . $quotation->quotation_number)

@section('content')
    <div class="container-fluid p-0">

        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-3">
                <div
                    class="icon-box-lg bg-gradient-to-br from-purple-500 to-indigo-600 rounded-circle shadow-lg text-white">
                    <i class="bi bi-file-earmark-text fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <h2 class="fw-bold text-white mb-0">{{ $quotation->quotation_number }}</h2>
                        <span
                            class="badge {{ $quotation->status->badgeClass() }} border border-white/20 fs-6 px-3 py-2 rounded-pill shadow-sm">
                            {{ $quotation->status->label() }}
                        </span>
                    </div>
                    <div class="d-flex gap-3 text-gray-400 small">
                        <span><i class="bi bi-calendar me-1"></i> تاريخ الإصدار: <span
                                class="text-white">{{ $quotation->quotation_date->format('Y-m-d') }}</span></span>
                        <span class="text-warning"><i class="bi bi-hourglass-split me-1"></i> صالح حتى: <span
                                class="text-white fw-bold">{{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : 'غير محدد' }}</span></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('quotations.index') }}" class="btn btn-glass-outline rounded-pill">
                    <i class="bi bi-arrow-right me-2"></i> القائمة
                </a>

                <div class="btn-group shadow-lg rounded-pill overflow-hidden">
                    {{-- Edit (if editable) --}}
                    @if($quotation->status->canEdit())
                        <a href="{{ route('quotations.edit', $quotation->id) }}"
                            class="btn btn-dark border-start border-white/10 text-warning hover-bg-warning-dark">
                            <i class="bi bi-pencil-fill me-2"></i> تعديل
                        </a>
                    @endif

                    {{-- Send (if Draft) --}}
                    @if($quotation->status === \Modules\Sales\Enums\QuotationStatus::DRAFT)
                        <form action="{{ route('quotations.send', $quotation->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit"
                                class="btn btn-dark border-start border-white/10 text-primary hover-bg-primary-dark">
                                <i class="bi bi-send-fill me-2"></i> إرسال
                            </button>
                        </form>
                    @endif

                    {{-- Accept/Reject (If Draft or Sent) --}}
                    @if(in_array($quotation->status, [\Modules\Sales\Enums\QuotationStatus::DRAFT, \Modules\Sales\Enums\QuotationStatus::SENT]))
                        <form action="{{ route('quotations.accept', $quotation->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit"
                                class="btn btn-dark border-start border-white/10 text-success hover-bg-success-dark">
                                <i class="bi bi-check-lg me-2"></i> قبول
                            </button>
                        </form>
                    @endif

                    {{-- Print --}}
                    <a href="{{ route('quotations.print', $quotation->id) }}" target="_blank"
                        class="btn btn-dark border-start border-white/10 text-info hover-bg-info-dark">
                        <i class="bi bi-printer-fill me-2"></i> طباعة
                    </a>

                    {{-- Convert to Order (if Accepted) --}}
                    @if($quotation->status === \Modules\Sales\Enums\QuotationStatus::ACCEPTED)
                        <form action="{{ route('quotations.convert', $quotation->id) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('تحويل العرض إلى أمر بيع؟')">
                            @csrf
                            <button type="submit" class="btn btn-success fw-bold px-4 hover-scale">
                                <i class="bi bi-box-seam me-2"></i> تحويل لأمر بيع
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Status Actions Dropdown if needed, or simple buttons -->
                <div class="dropdown">
                    <button class="btn btn-glass-outline rounded-pill dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-gear-fill me-2"></i> إجراءات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-2xl border border-white/10">
                        @if(in_array($quotation->status, [\Modules\Sales\Enums\QuotationStatus::DRAFT, \Modules\Sales\Enums\QuotationStatus::SENT]))
                            <li>
                                <form action="{{ route('quotations.reject', $quotation->id) }}" method="POST"
                                    onsubmit="return confirm('رفض العرض؟')">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> رفض
                                        العرض</button>
                                </form>
                            </li>
                        @endif
                        <li>
                            <hr class="dropdown-divider border-white/10">
                        </li>
                        <li>
                            <form action="{{ route('quotations.destroy', $quotation->id) }}" method="POST"
                                onsubmit="return confirm('حذف نهائي؟')">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> حذف</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Items Table -->
                <div class="glass-panel p-0 rounded-4 overflow-hidden border border-white/10 shadow-lg mb-4">
                    <div class="bg-white/5 p-4 border-bottom border-white/10">
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-list-check me-2 text-info"></i> تفاصيل العرض
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-gray-900/50 text-gray-400 text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">المنتج</th>
                                    <th class="text-center py-3">الكمية</th>
                                    <th class="text-center py-3">السعر</th>
                                    <th class="text-center py-3">الخصم</th>
                                    <th class="text-end pe-4 py-3">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->lines as $line)
                                    <tr class="hover:bg-white/5 border-bottom border-white/5">
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-white">{{ $line->description }}</div>
                                            <small class="text-gray-500">{{ $line->product->code ?? '-' }}</small>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="badge bg-white/10 text-white border border-white/10 rounded-pill px-3">
                                                {{ $line->quantity + 0 }} {{ $line->unit->name ?? '' }}
                                            </span>
                                        </td>
                                        <td class="text-center py-3 text-gray-300">{{ number_format($line->unit_price, 2) }}
                                        </td>
                                        <td class="text-center py-3 text-gray-300">{{ $line->discount_percent + 0 }}%</td>
                                        <td class="text-end pe-4 py-3 fw-bold text-white">
                                            {{ number_format($line->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-900/50">
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-end py-3 text-gray-400 small text-uppercase">المجموع الفرعي</td>
                                    <td class="text-end pe-4 py-3 fw-bold text-white">
                                        {{ number_format($quotation->subtotal, 2) }}
                                    </td>
                                </tr>
                                @if($quotation->discount_amount > 0)
                                    <tr>
                                        <td colspan="3"></td>
                                        <td class="text-end py-2 text-warning small">خصم إضافي</td>
                                        <td class="text-end pe-4 py-2 text-warning">
                                            -{{ number_format($quotation->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-end py-2 text-gray-400 small">الضريبة</td>
                                    <td class="text-end pe-4 py-2 text-gray-300">
                                        {{ number_format($quotation->tax_amount, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-end py-4 text-white fs-5 fw-bold">الإجمالي النهائي</td>
                                    <td class="text-end pe-4 py-4 text-success fs-4 fw-bold text-glow">
                                        {{ number_format($quotation->total, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes & Terms -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="glass-panel p-4 rounded-4 h-100 border border-white/10">
                            <h6 class="text-gray-400 fw-bold mb-3 small text-uppercase"><i
                                    class="bi bi-chat-left-text me-2"></i> ملاحظات</h6>
                            <p class="text-white mb-0 small opacity-75">{{ $quotation->notes ?: 'لا توجد ملاحظات' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-panel p-4 rounded-4 h-100 border border-white/10">
                            <h6 class="text-gray-400 fw-bold mb-3 small text-uppercase"><i
                                    class="bi bi-shield-check me-2"></i> الشروط والأحكام</h6>
                            <p class="text-white mb-0 small opacity-75">{{ $quotation->terms ?: 'لا توجد شروط خاصة' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <!-- Customer Card -->
                <div
                    class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg mb-4 position-relative overflow-hidden">
                    <div class="absolute-glow top-0 end-0 bg-blue-500/10"></div>
                    <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3">معلومات العميل</h5>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar-circle bg-gradient-to-br from-gray-700 to-gray-800 text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                            style="width: 50px; height: 50px;">
                            <span class="fs-5 fw-bold">{{ substr($quotation->customer->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white mb-0">{{ $quotation->customer->name }}</h5>
                            <small class="text-gray-400">{{ $quotation->customer->phone ?? 'لا يوجد هاتف' }}</small>
                        </div>
                    </div>

                    <div class="vstack gap-3 text-gray-300 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-500">العنوان:</span>
                            <span class="text-end">{{ $quotation->customer->address ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-500">النوع:</span>
                            <span
                                class="badge bg-white/5 border border-white/10">{{ $quotation->customer->type_label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg">
                    <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3">معلومات النظام</h5>
                    <div class="vstack gap-3 small">
                        <div class="d-flex justify-content-between text-gray-400">
                            <span>بواسطة:</span>
                            <span class="text-white">{{ $quotation->creator->name ?? 'System' }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-gray-400">
                            <span>تاريخ الإنشاء:</span>
                            <span class="text-white">{{ $quotation->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($quotation->approved_by)
                            <div class="d-flex justify-content-between text-gray-400">
                                <span>تم الاعتماد بواسطة:</span>
                                <span class="text-success">{{ $quotation->approver->name ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-gray-400">
                                <span>تاريخ الاعتماد:</span>
                                <span class="text-success">{{ $quotation->approved_at->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
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

        .hover-bg-primary-dark:hover {
            background-color: #0c4a6e !important;
            color: #38bdf8 !important;
        }

        .hover-bg-success-dark:hover {
            background-color: #064e3b !important;
            color: #34d399 !important;
        }
    </style>
@endsection