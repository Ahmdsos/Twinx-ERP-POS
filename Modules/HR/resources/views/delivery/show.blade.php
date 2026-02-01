@extends('layouts.app')

@section('title', 'أداء السائق: ' . $driver->employee->full_name)

@section('content')
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('hr.delivery.index') }}"
                class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 mb-3">
                <i class="bi bi-arrow-right"></i>
                <span>العودة للقائمة</span>
            </a>
            <h3 class="fw-bold text-white mb-0">تقرير أداء: {{ $driver->employee->full_name }}</h3>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-secondary small mb-1">طلبات مكتملة</h6>
                    <h2 class="text-white fw-bold mb-0">{{ $performance['total_completed'] }}</h2>
                    <div class="text-secondary small mt-2">من إجمالي {{ $driver->total_deliveries }} طلب تاريخياً</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-secondary small mb-1">في الطريق حالياً</h6>
                    <h2 class="text-white fw-bold mb-0">{{ $performance['total_shipped'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-secondary small mb-1">آخر عملية توصيل</h6>
                    <h4 class="text-white fw-bold mb-0">
                        {{ $performance['last_delivery'] ? $performance['last_delivery']->format('Y-m-d') : '---' }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-header bg-secondary bg-opacity-5 border-0 py-3 px-4">
            <h5 class="mb-0 text-white fw-bold">آخر 10 عمليات شحن</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-secondary bg-opacity-10 text-secondary">
                        <tr>
                            <th class="ps-4 py-3">رقم الطلب (DO)</th>
                            <th class="py-3">التاريخ</th>
                            <th class="py-3">العميل</th>
                            <th class="py-3 text-center">الحالة</th>
                            <th class="pe-4 py-3 text-end">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driver->shipments as $shipment)
                            <tr>
                                <td class="ps-4 fw-bold text-white">{{ $shipment->do_number }}</td>
                                <td class="text-secondary small">{{ $shipment->delivery_date->format('Y-m-d') }}</td>
                                <td class="text-white small">{{ $shipment->customer->name ?? '---' }}</td>
                                <td class="text-center">
                                    @php
                                        $statusClasses = [
                                            'draft' => 'bg-secondary',
                                            'ready' => 'bg-info',
                                            'shipped' => 'bg-warning text-dark',
                                            'delivered' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                        ];
                                        $c = $statusClasses[$shipment->status->value] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $c }} rounded-pill px-3">{{ $shipment->status->value }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="#" class="btn btn-sm btn-icon text-primary opacity-50"><i
                                            class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">لا توجد شحنات مسجلة لهذا السائق</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection