@extends('layouts.app')

@section('title', 'أمر بيع ' . $salesOrder->so_number)

@section('content')
    <div class="container-fluid p-0">

        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box-lg bg-gradient-to-br from-blue-600 to-indigo-700 rounded-circle shadow-lg text-white">
                    <i class="bi bi-cart-check fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <h2 class="fw-bold text-white mb-0">{{ $salesOrder->so_number }}</h2>
                        <span
                            class="badge {{ $salesOrder->status->badgeClass() }} border border-white/20 fs-6 px-3 py-2 rounded-pill shadow-sm">
                            {{ $salesOrder->status->label() }}
                        </span>
                    </div>
                    <div class="d-flex gap-3 text-gray-400 small">
                        <span><i class="bi bi-calendar me-1"></i> تاريخ الطلب: <span
                                class="text-white">{{ $salesOrder->order_date->format('Y-m-d') }}</span></span>
                        <span class="text-info"><i class="bi bi-clock-history me-1"></i> التسليم المتوقع: <span
                                class="text-white fw-bold">{{ $salesOrder->expected_date ? $salesOrder->expected_date->format('Y-m-d') : 'غير محدد' }}</span></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('sales-orders.index') }}" class="btn btn-glass-outline rounded-pill">
                    <i class="bi bi-arrow-right me-2"></i> القائمة
                </a>

                <div class="btn-group shadow-lg rounded-pill overflow-hidden">

                    {{-- Actions for DRAFT status --}}
                    @if($salesOrder->status === \Modules\Sales\Enums\SalesOrderStatus::DRAFT)
                        <a href="{{ route('sales-orders.edit', $salesOrder->id) }}"
                            class="btn btn-dark border-start border-white/10 text-warning hover-bg-warning-dark">
                            <i class="bi bi-pencil me-2"></i> تعديل
                        </a>

                        <form action="{{ route('sales-orders.confirm', $salesOrder->id) }}" method="POST" class="d-inline"
                            data-confirm="هل أنت متأكد من تأكيد أمر البيع؟ لن يمكنك التعديل بعد ذلك.">
                            @csrf
                            <button type="submit" class="btn btn-success fw-bold px-4 hover-scale">
                                <i class="bi bi-check-circle-fill me-2"></i> تأكيد الأمر
                            </button>
                        </form>
                    @endif

                    {{-- Actions for CONFIRMED status --}}
                    @if($salesOrder->status === \Modules\Sales\Enums\SalesOrderStatus::CONFIRMED)
                        <form action="{{ route('sales-orders.deliver', $salesOrder->id) }}" method="POST" class="d-inline"
                            data-confirm="تأكيد صرف المخزون؟">
                            @csrf
                            <button type="submit" class="btn btn-warning fw-bold px-4 hover-scale text-black">
                                <i class="bi bi-box-seam me-2"></i> إنشاء إذن صرف
                            </button>
                        </form>
                    @endif

                    {{-- Actions for Invoicing (Confirmed, Partial, Delivered) --}}
                    @if(in_array($salesOrder->status, [\Modules\Sales\Enums\SalesOrderStatus::CONFIRMED, \Modules\Sales\Enums\SalesOrderStatus::PARTIAL, \Modules\Sales\Enums\SalesOrderStatus::DELIVERED]))
                        <form action="{{ route('sales-orders.invoice', $salesOrder->id) }}" method="POST" class="d-inline"
                            data-confirm="إنشاء فاتورة مبيعات؟">
                            @csrf
                            <button type="submit" class="btn btn-primary fw-bold px-4 hover-scale">
                                <i class="bi bi-receipt me-2"></i> إصدار فاتورة
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('sales-orders.print', $salesOrder->id) }}" target="_blank"
                        class="btn btn-dark border-start border-white/10 text-info hover-bg-info-dark">
                        <i class="bi bi-printer-fill me-2"></i> طباعة
                    </a>
                </div>

                {{-- Dropdown for Secondary Actions --}}
                <div class="dropdown">
                    <button class="btn btn-glass-outline rounded-pill dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-gear-fill me-2"></i> إجراءات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-2xl border border-white/10">
                        <li>
                            <a class="dropdown-item" href="#"><i class="bi bi-envelope me-2"></i> إرسال بالبريد</a>
                        </li>

                        @if($salesOrder->status === \Modules\Sales\Enums\SalesOrderStatus::DRAFT)
                            <li>
                                <hr class="dropdown-divider border-white/10">
                            </li>
                            <li>
                                <form action="{{ route('sales-orders.cancel', $salesOrder->id) }}" method="POST"
                                    data-confirm="هل أنت متأكد من إلغاء الأمر؟">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> إلغاء
                                        الأمر</button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('sales-orders.destroy', $salesOrder->id) }}" method="POST"
                                    data-confirm="حذف نهائي؟">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> حذف</button>
                                </form>
                            </li>
                        @endif

                        @if($salesOrder->canCancel() && $salesOrder->status !== \Modules\Sales\Enums\SalesOrderStatus::DRAFT)
                            <li>
                                <hr class="dropdown-divider border-white/10">
                            </li>
                            <li>
                                <form action="{{ route('sales-orders.cancel', $salesOrder->id) }}" method="POST"
                                    data-confirm="هل أنت متأكد من إلغاء هذا الطلب؟">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> إلغاء
                                        الأمر</button>
                                </form>
                            </li>
                        @endif
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
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-list-check me-2 text-info"></i> تفاصيل الطلب
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-gray-900/50 text-gray-400 text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">المنتج</th>
                                    <th class="text-center py-3">الكمية</th>
                                    <th class="text-center py-3">تم التسليم</th>
                                    <th class="text-center py-3">السعر</th>
                                    <th class="text-center py-3">الخصم</th>
                                    <th class="text-end pe-4 py-3">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesOrder->lines as $line)
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
                                        <td class="text-center py-3">
                                            <span
                                                class="badge {{ $line->delivered_quantity >= $line->quantity ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning' }} border border-white/10 rounded-pill px-3">
                                                {{ $line->delivered_quantity + 0 }}
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
                                    <td colspan="4"></td>
                                    <td class="text-end py-3 text-gray-400 small text-uppercase">المجموع الفرعي</td>
                                    <td class="text-end pe-4 py-3 fw-bold text-white">
                                        {{ number_format($salesOrder->subtotal, 2) }}
                                    </td>
                                </tr>
                                @if($salesOrder->discount_amount > 0)
                                    <tr>
                                        <td colspan="4"></td>
                                        <td class="text-end py-2 text-warning small">خصم إضافي</td>
                                        <td class="text-end pe-4 py-2 text-warning">
                                            -{{ number_format($salesOrder->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end py-2 text-gray-400 small">الضريبة</td>
                                    <td class="text-end pe-4 py-2 text-gray-300">
                                        {{ number_format($salesOrder->tax_amount, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end py-4 text-white fs-5 fw-bold">الإجمالي النهائي</td>
                                    <td class="text-end pe-4 py-4 text-success fs-4 fw-bold text-glow">
                                        {{ number_format($salesOrder->total, 2) }}
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
                            <p class="text-white mb-0 small opacity-75">{{ $salesOrder->notes ?: 'لا توجد ملاحظات' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-panel p-4 rounded-4 h-100 border border-white/10">
                            <h6 class="text-gray-400 fw-bold mb-3 small text-uppercase"><i class="bi bi-truck me-2"></i>
                                معلومات الشحن</h6>
                            <p class="text-white mb-2 small"><strong>العنوان:</strong>
                                {{ $salesOrder->shipping_address ?: 'نفس عنوان العميل' }}</p>
                            <p class="text-white mb-0 small"><strong>الطريقة:</strong>
                                {{ $salesOrder->shipping_method ?: 'استلام من المخزن' }}</p>
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
                            <span class="fs-5 fw-bold">{{ substr($salesOrder->customer->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white mb-0">{{ $salesOrder->customer->name }}</h5>
                            <small class="text-gray-400">{{ $salesOrder->customer->phone ?? 'لا يوجد هاتف' }}</small>
                        </div>
                    </div>

                    <div class="vstack gap-3 text-gray-300 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-500">العنوان:</span>
                            <span class="text-end">{{ $salesOrder->customer->address ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-500">النوع:</span>
                            <span
                                class="badge bg-white/5 border border-white/10">{{ $salesOrder->customer->type_label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg">
                    <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3">معلومات النظام</h5>
                    <div class="vstack gap-3 small">
                        <div class="d-flex justify-content-between text-gray-400">
                            <span>المخزن:</span>
                            <span class="text-white">{{ $salesOrder->warehouse->name ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-gray-400">
                            <span>المرجع (عرض سعر):</span>
                            <span
                                class="text-white">{{ $salesOrder->quotation_id ? 'QT-' . $salesOrder->quotation_id : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-gray-400">
                            <span>تاريخ الإنشاء:</span>
                            <span class="text-white">{{ $salesOrder->created_at->format('Y-m-d H:i') }}</span>
                        </div>
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
    </style>
@endsection