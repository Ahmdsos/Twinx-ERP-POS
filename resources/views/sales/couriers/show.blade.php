@extends('layouts.app')

@section('title', $courier->name)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('couriers.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h1 class="h3 mb-0">{{ $courier->name }}</h1>
                    <p class="text-muted mb-0">
                        <span class="badge {{ $courier->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $courier->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                        <span class="ms-2">{{ $courier->code }}</span>
                    </p>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ route('couriers.edit', $courier) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>
                    تعديل
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Courier Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">بيانات الشركة</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="35%">الكود</td>
                                <td class="fw-bold">{{ $courier->code }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">الاسم</td>
                                <td>{{ $courier->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">جهة الاتصال</td>
                                <td>{{ $courier->contact_person ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">الهاتف</td>
                                <td>{{ $courier->phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">البريد</td>
                                <td>{{ $courier->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">العنوان</td>
                                <td>{{ $courier->address ?? '-' }}</td>
                            </tr>
                            @if($courier->tracking_url_template)
                                <tr>
                                    <td class="text-muted">رابط التتبع</td>
                                    <td><small class="text-break">{{ $courier->tracking_url_template }}</small></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Stats -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">إحصائيات</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ $courier->shipments_count ?? 0 }}</h3>
                                <small class="text-muted">شحنة</small>
                            </div>
                            <div class="col-6">
                                <h3 class="mb-0">{{ $courier->delivery_orders_count ?? 0 }}</h3>
                                <small class="text-muted">أمر توصيل</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($courier->notes)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">ملاحظات</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $courier->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Shipments -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">آخر الشحنات</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>رقم الشحنة</th>
                                    <th>رقم التتبع</th>
                                    <th>العميل</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentShipments as $shipment)
                                    <tr>
                                        <td>{{ $shipment->shipment_number }}</td>
                                        <td>
                                            @if($shipment->tracking_number)
                                                @if($trackingUrl = $shipment->getTrackingUrl())
                                                    <a href="{{ $trackingUrl }}" target="_blank">
                                                        {{ $shipment->tracking_number }}
                                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                    </a>
                                                @else
                                                    {{ $shipment->tracking_number }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $shipment->deliveryOrder?->customer?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $shipment->status->color() }}">
                                                <i class="bi {{ $shipment->status->icon() }} me-1"></i>
                                                {{ $shipment->status->label() }}
                                            </span>
                                        </td>
                                        <td>{{ $shipment->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            لا توجد شحنات حتى الآن
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection