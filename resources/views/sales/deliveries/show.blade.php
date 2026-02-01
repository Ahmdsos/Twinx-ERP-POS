@extends('layouts.app')

@section('title', 'إذن صرف ' . $delivery->do_number)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="d-flex align-items-center gap-3">
                <div
                    class="icon-box-lg bg-gradient-to-br from-indigo-600 to-purple-700 rounded-circle shadow-lg text-white">
                    <i class="bi bi-box-seam fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <h2 class="fw-bold text-white mb-0">{{ $delivery->do_number }}</h2>
                        <span class="badge bg-gray-700 border border-white/20 fs-6 px-3 py-2 rounded-pill shadow-sm">
                            {{ $delivery->status->label() }}
                        </span>
                    </div>
                    <div class="d-flex gap-3 text-gray-400 small">
                        <span><i class="bi bi-calendar me-1"></i> التاريخ: <span
                                class="text-white">{{ $delivery->delivery_date->format('Y-m-d') }}</span></span>
                        @if($delivery->salesOrder)
                            <span><i class="bi bi-link-45deg me-1"></i> أمر البيع:
                                <a href="{{ route('sales-orders.show', $delivery->salesOrder->id) }}"
                                    class="text-info text-decoration-none hover:text-white transition-colors fw-bold">
                                    {{ $delivery->salesOrder->so_number }}
                                </a>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('deliveries.index') }}" class="btn btn-glass-outline rounded-pill">
                    <i class="bi bi-arrow-right me-2"></i> القائمة
                </a>

                <div class="btn-group shadow-lg rounded-pill overflow-hidden">
                    @if($delivery->status === \Modules\Sales\Enums\DeliveryStatus::READY)
                        <button type="button" class="btn btn-success fw-bold px-4 hover-scale" data-bs-toggle="modal"
                            data-bs-target="#shipModal">
                            <i class="bi bi-truck me-2"></i> تأكيد الشحن
                        </button>
                    @endif

                    @if($delivery->status === \Modules\Sales\Enums\DeliveryStatus::SHIPPED)
                        <form action="{{ route('deliveries.complete', $delivery->id) }}" method="POST"
                            onsubmit="return confirm('تأكيد تسليم الشحنة للعميل نهائياً؟')">
                            @csrf
                            <button type="submit" class="btn btn-primary fw-bold px-4 hover-scale rounded-0 h-100">
                                <i class="bi bi-check2-circle me-2"></i> تم التسليم
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Items Table -->
                <div class="glass-panel p-0 rounded-4 overflow-hidden border border-white/10 shadow-lg mb-4">
                    <div class="bg-white/5 p-4 border-bottom border-white/10 d-flex justify-content-between">
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-list-check me-2 text-info"></i> المواد المصروفة
                        </h5>
                        <span class="badge bg-white/10 text-gray-300">{{ $delivery->warehouse->name }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-gray-900/50 text-gray-400 text-uppercase small">
                                <tr>
                                    <th class="ps-4 py-3">المنتج</th>
                                    <th class="text-center py-3">الكمية المصروفة</th>
                                    <th class="text-center py-3">الوحدة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delivery->lines as $line)
                                    <tr class="hover:bg-white/5 border-bottom border-white/5">
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-white">{{ $line->product->name }}</div>
                                            <small class="text-gray-500">{{ $line->product->code ?? '-' }}</small>
                                        </td>
                                        <td class="text-center py-3">
                                            <span
                                                class="badge bg-white/10 text-white border border-white/10 rounded-pill px-4 py-2 fs-6">
                                                {{ $line->quantity + 0 }}
                                            </span>
                                        </td>
                                        <td class="text-center py-3 text-gray-400">{{ $line->product->unit->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg mb-4">
                    <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3"><i
                            class="bi bi-truck me-2 text-warning"></i> بيانات الشحن</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-gray-500 small d-block mb-1">طريقة الشحن</label>
                            <div class="text-white">{{ $delivery->shipping_method ?? 'غير محدد' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-gray-500 small d-block mb-1">العنوان</label>
                            <div class="text-white">{{ $delivery->shipping_address ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-gray-500 small d-block mb-1">اسم السائق</label>
                            <div class="text-white">{{ $delivery->driver_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-gray-500 small d-block mb-1">رقم المركبة</label>
                            <div class="text-white">{{ $delivery->vehicle_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-gray-500 small d-block mb-1">رقم التتبع</label>
                            <div class="text-info font-monospace">{{ $delivery->tracking_number ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if($delivery->notes)
                    <div class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg">
                        <h6 class="text-gray-400 fw-bold mb-2 small text-uppercase"><i class="bi bi-sticky me-2"></i> ملاحظات
                        </h6>
                        <p class="text-white mb-0 opacity-75">{{ $delivery->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Customer Card -->
                <div class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg mb-4">
                    <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3">العميل</h5>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle bg-gradient-to-br from-gray-700 to-gray-800 text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                            style="width: 50px; height: 50px;">
                            <span class="fs-5 fw-bold">{{ substr($delivery->customer->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white mb-0">{{ $delivery->customer->name }}</h5>
                            <small class="text-gray-400">{{ $delivery->customer->phone ?? 'لا يوجد هاتف' }}</small>
                        </div>
                    </div>
                    <div class="text-gray-300 small bg-white/5 p-3 rounded">
                        {{ $delivery->customer->address ?? 'لا يوجد عنوان مسجل' }}
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="glass-panel p-4 rounded-4 border border-white/10 shadow-lg">
                    <h5 class="fw-bold text-white mb-4 border-bottom border-white/10 pb-3">سجل الحركة</h5>
                    <ul class="timeline-list list-unstyled m-0 p-0">
                        <li class="position-relative pb-4 ps-4 border-start border-white/10">
                            <div class="position-absolute top-0 start-0 translate-middle bg-primary rounded-circle"
                                style="width: 10px; height: 10px;"></div>
                            <div class="fw-bold text-white">تم الإنشاء</div>
                            <div class="small text-gray-500">{{ $delivery->created_at->format('Y-m-d H:i') }}</div>
                        </li>
                        @if($delivery->shipped_date)
                            <li class="position-relative pb-4 ps-4 border-start border-white/10">
                                <div class="position-absolute top-0 start-0 translate-middle bg-warning rounded-circle"
                                    style="width: 10px; height: 10px;"></div>
                                <div class="fw-bold text-white">تم الشحن</div>
                                <div class="small text-gray-500">{{ $delivery->shipped_date->format('Y-m-d H:i') }}</div>
                            </li>
                        @endif
                        @if($delivery->status === \Modules\Sales\Enums\DeliveryStatus::DELIVERED)
                            <li class="position-relative ps-4">
                                <div class="position-absolute top-0 start-0 translate-middle bg-success rounded-circle"
                                    style="width: 10px; height: 10px;"></div>
                                <div class="fw-bold text-white">تم التسليم</div>
                                <div class="small text-gray-500">مكتمل</div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Ship Modal -->
    <div class="modal fade" id="shipModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-slate-900 border border-white/10 text-white shadow-2xl">
                <div class="modal-header border-white/10">
                    <h5 class="modal-title fw-bold">تحديث بيانات الشحن</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('deliveries.ship', $delivery->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-gray-400">اسم السائق</label>
                            <input type="text" name="driver_name" class="form-control glass-input"
                                value="{{ $delivery->driver_name }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-400">رقم المركبة</label>
                            <input type="text" name="vehicle_number" class="form-control glass-input"
                                value="{{ $delivery->vehicle_number }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-400">رقم التتبع (Tracking No)</label>
                            <input type="text" name="tracking_number" class="form-control glass-input"
                                value="{{ $delivery->tracking_number }}">
                        </div>
                    </div>
                    <div class="modal-footer border-white/10">
                        <button type="button" class="btn btn-glass-outline" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">تأكيد الشحن</button>
                    </div>
                </form>
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

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-glass-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .glass-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .glass-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
    </style>
@endsection