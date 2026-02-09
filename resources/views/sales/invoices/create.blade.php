@extends('layouts.app')

@section('title', 'إنشاء فاتورة مبيعات')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-gradient-cyan shadow-neon-cyan rounded-circle text-white">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-0">إنشاء فاتورة مبيعات</h2>
                    <p class="text-gray-400 mb-0 x-small">إصدار فاتورة لأمر تسليم منتهي</p>
                </div>
            </div>
            <a href="{{ route('sales-invoices.index') }}" class="btn btn-glass-outline px-4 fw-bold rounded-pill">
                <i class="bi bi-arrow-right me-2"></i> القائمة
            </a>
        </div>

        <!-- Instructions -->
        <div class="glass-card p-3 mb-4 border-start border-4 border-info">
            <div class="d-flex gap-3">
                <i class="bi bi-info-circle-fill text-info fs-4"></i>
                <div>
                    <h6 class="text-white fw-bold mb-1">تعليمات الإصدار</h6>
                    <p class="text-gray-400 mb-0 small">
                        يتم إنشاء فواتير المبيعات حصراً بناءً على <strong>أوامر التسليم (Delivery Orders)</strong> التي تم
                        تسليمها بالفعل (SHIPPED/DELIVERED) ولم يتم فوترتها مسبقاً.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Form -->
            <div class="col-md-8">
                <div class="glass-panel p-4 h-100 position-relative text-white">
                    <div class="absolute-glow top-0 start-0 bg-cyan-500/10"></div>

                    <form action="{{ route('sales-invoices.store') }}" method="POST" id="invoiceForm">
                        @csrf

                        <div class="mb-4">
                            <label class="section-label mb-2 text-cyan-400">اختر أمر التسليم <span
                                    class="text-danger">*</span></label>
                            <div class="position-relative">
                                <i
                                    class="bi bi-box-seam position-absolute top-50 start-0 translate-middle-y ms-3 text-cyan-400"></i>
                                <select name="delivery_order_id" class="form-select glass-select ps-5 h-50px" required
                                    onchange="window.location.href='{{ route('sales-invoices.create') }}?delivery_order_id=' + this.value">
                                    <option value="" class="text-gray-500">-- اختر أمر التسليم --</option>
                                    @foreach($deliveredOrders as $order)
                                        <option value="{{ $order->id }}" {{ (isset($deliveryOrder) && $deliveryOrder->id == $order->id) ? 'selected' : '' }} class="text-white bg-gray-900">
                                            #{{ $order->delivery_number }} | {{ optional($order->salesOrder)->customer->name ?? $order->customer->name ?? 'Unknown' }} |
                                            {{ $order->delivery_date->format('Y-m-d') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text text-gray-500 x-small mt-2"><i class="bi bi-funnel me-1"></i> القائمة تعرض
                                فقط الأوامر المنتهية غير المفوترة.</div>
                        </div>

                        @if(isset($deliveryOrder))
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="section-label mb-2">تاريخ الفاتورة</label>
                                    <div class="position-relative">
                                        <i
                                            class="bi bi-calendar-event position-absolute top-50 start-0 translate-middle-y ms-3 text-cyan-400"></i>
                                        <input type="date" name="invoice_date" class="form-control glass-input ps-5 h-50px"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="section-label mb-2">تاريخ الاستحقاق</label>
                                    <div class="position-relative">
                                        <i
                                            class="bi bi-calendar-check position-absolute top-50 start-0 translate-middle-y ms-3 text-cyan-400"></i>
                                        <input type="date" name="due_date" class="form-control glass-input ps-5 h-50px"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <label class="section-label mb-2">ملاحظات إضافية</label>
                                    <textarea name="notes" class="form-control glass-textarea" rows="3"
                                        placeholder="أي ملاحظات تظهر على الفاتورة..."></textarea>
                                </div>

                                <div class="col-12 mt-4 pt-4 border-top border-white/10">
                                    <button type="submit"
                                        class="btn btn-action-cyan w-100 py-3 fw-bold shadow-neon-cyan hover-scale">
                                        <i class="bi bi-check-circle-fill me-2"></i> تأكيد وإصدار الفاتورة
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 text-gray-500 opacity-50">
                                <i class="bi bi-arrow-up-circle fs-1 mb-2 d-block"></i>
                                <p>يرجى اختيار أمر تسليم للمتابعة</p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Preview Selection -->
            <div class="col-md-4">
                @if(isset($deliveryOrder))
                    <div class="glass-card p-4 h-100 border border-white/10 position-relative overflow-hidden">
                        <div class="absolute-glow top-0 end-0 bg-purple-500/10"></div>
                        <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3">
                            <i class="bi bi-file-earmark-text me-2 text-purple-400"></i> ملخص الأمر
                            {{ $deliveryOrder->delivery_number }}
                        </h5>

                        <div class="mb-4">
                            <label class="text-gray-500 x-small fw-bold text-uppercase d-block mb-1">العميل</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-xs bg-purple-500 rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                    style="width: 40px; height: 40px;">
                                    {{ substr(optional($deliveryOrder->salesOrder)->customer->name ?? $deliveryOrder->customer->name ?? '?', 0, 1) }}
                                </div>
                                <h6 class="text-white mb-0">{{ optional($deliveryOrder->salesOrder)->customer->name ?? $deliveryOrder->customer->name ?? 'Unknown Customer' }}</h6>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-gray-500 x-small fw-bold text-uppercase d-block mb-2">الأصناف المسلمة</label>
                            <div class="vstack gap-2">
                                @foreach($deliveryOrder->lines as $line)
                                    <div
                                        class="d-flex justify-content-between align-items-center p-2 rounded bg-white/5 border border-white/5">
                                        <span class="text-gray-300 small">{{ $line->product->name }}</span>
                                        <span class="badge bg-purple-500/20 text-purple-300">{{ $line->quantity }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="alert alert-warning bg-warning/10 border-warning/20 text-warning x-small mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i> سيتم إنشاء الفاتورة بناءً على الكميات المسلمة فقط.
                        </div>
                    </div>
                @else
                    <div
                        class="glass-card p-4 h-100 d-flex flex-column align-items-center justify-content-center text-center opacity-50 border-dashed border-white/20">
                        <div class="icon-circle bg-gray-800 text-gray-500 mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-basket fs-1"></i>
                        </div>
                        <h5 class="text-gray-400">معاينة الطلب</h5>
                        <p class="text-gray-600 small">تفاصيل الطلب ستظهر هنا بعد الاختيار</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .glass-panel,
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }

        .glass-select,
        .glass-input,
        .glass-textarea {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            transition: all 0.3s ease;
        }

        .glass-select:focus,
        .glass-input:focus,
        .glass-textarea:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #22d3ee !important;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.1);
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        .h-50px {
            height: 50px;
        }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(6, 182, 212, 0.4);
        }

        .shadow-neon-cyan {
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
        }

        .bg-gradient-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .absolute-glow {
            position: absolute;
            width: 150px;
            height: 150px;
            filter: blur(40px);
            pointer-events: none;
        }
    </style>
@endsection