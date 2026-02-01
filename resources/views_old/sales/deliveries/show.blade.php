@extends('layouts.app')

@section('title', $delivery->do_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل أمر التسليم')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">أوامر التسليم</a></li>
    <li class="breadcrumb-item active">{{ $delivery->do_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Delivery Header Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-truck me-2"></i>
                        {{ $delivery->do_number }}
                    </h5>
                    @php
                        $statusClass = match ($delivery->status->value) {
                            'draft' => 'secondary',
                            'ready' => 'info',
                            'shipped' => 'warning',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $statusClass }} fs-6">
                        {{ $delivery->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">أمر البيع</td>
                                    <td>
                                        <a href="{{ route('sales-orders.show', $delivery->sales_order_id) }}">
                                            <strong>{{ $delivery->salesOrder?->so_number ?? '-' }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">العميل</td>
                                    <td>{{ $delivery->salesOrder?->customer?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المستودع</td>
                                    <td>{{ $delivery->warehouse?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">تاريخ التسليم</td>
                                    <td>{{ $delivery->delivery_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">تاريخ الشحن</td>
                                    <td>{{ $delivery->shipped_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">طريقة الشحن</td>
                                    <td>{{ $delivery->shipping_method ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($delivery->driver_name || $delivery->vehicle_number || $delivery->tracking_number)
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">السائق:</small>
                                <p class="mb-0">{{ $delivery->driver_name ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">رقم المركبة:</small>
                                <p class="mb-0">{{ $delivery->vehicle_number ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">رقم التتبع:</small>
                                <p class="mb-0">{{ $delivery->tracking_number ?? '-' }}</p>
                            </div>
                        </div>
                    @endif

                    @if($delivery->shipping_address)
                        <hr>
                        <div>
                            <small class="text-muted">عنوان التسليم:</small>
                            <p class="mb-0">{{ $delivery->shipping_address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Lines -->
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
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>الوحدة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delivery->lines as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $line->product?->name ?? '-' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->product?->sku ?? '' }}</small>
                                        </td>
                                        <td><strong>{{ number_format($line->quantity, 2) }}</strong></td>
                                        <td>{{ $line->product?->unit?->abbreviation ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($delivery->notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $delivery->notes }}</p>
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
                        @if($delivery->status->value === 'ready')
                            <!-- Ship Modal Trigger -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#shipModal">
                                <i class="bi bi-truck me-2"></i>شحن الطلب
                            </button>
                        @endif

                        @if(in_array($delivery->status->value, ['ready', 'shipped']))
                            <form action="{{ route('deliveries.complete', $delivery) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>تأكيد التسليم
                                </button>
                            </form>
                        @endif

                        @if(!in_array($delivery->status->value, ['delivered', 'cancelled']))
                            <form action="{{ route('deliveries.cancel', $delivery) }}" method="POST"
                                onsubmit="return confirm('هل أنت متأكد من إلغاء أمر التسليم؟')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-lg me-2"></i>إلغاء
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('deliveries.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>مسار الحالة</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item {{ $delivery->status->value !== 'cancelled' ? 'completed' : '' }}">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>تم الإنشاء</strong>
                                <small class="text-muted d-block">{{ $delivery->created_at->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>

                        @if($delivery->status->value !== 'cancelled')
                            <div
                                class="timeline-item {{ in_array($delivery->status->value, ['shipped', 'delivered']) ? 'completed' : '' }}">
                                <div
                                    class="timeline-marker {{ in_array($delivery->status->value, ['shipped', 'delivered']) ? 'bg-success' : 'bg-secondary' }}">
                                </div>
                                <div class="timeline-content">
                                    <strong>تم الشحن</strong>
                                    @if($delivery->shipped_date)
                                        <small class="text-muted d-block">{{ $delivery->shipped_date->format('Y-m-d') }}</small>
                                    @endif
                                </div>
                            </div>

                            <div class="timeline-item {{ $delivery->status->value === 'delivered' ? 'completed' : '' }}">
                                <div
                                    class="timeline-marker {{ $delivery->status->value === 'delivered' ? 'bg-success' : 'bg-secondary' }}">
                                </div>
                                <div class="timeline-content">
                                    <strong>تم التسليم</strong>
                                </div>
                            </div>
                        @else
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <strong>ملغي</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ship Modal -->
    @if($delivery->status->value === 'ready')
        <div class="modal fade" id="shipModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('deliveries.ship', $delivery) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">شحن الطلب</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">اسم السائق</label>
                                <input type="text" class="form-control" name="driver_name" value="{{ $delivery->driver_name }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم المركبة</label>
                                <input type="text" class="form-control" name="vehicle_number"
                                    value="{{ $delivery->vehicle_number }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم التتبع</label>
                                <input type="text" class="form-control" name="tracking_number"
                                    value="{{ $delivery->tracking_number }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-truck me-1"></i>شحن
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        .timeline {
            position: relative;
            padding-right: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
            padding-right: 20px;
            border-right: 2px solid #dee2e6;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
            border-right-color: transparent;
        }

        .timeline-item.completed {
            border-right-color: #198754;
        }

        .timeline-marker {
            position: absolute;
            right: -8px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #dee2e6;
        }
    </style>
@endsection