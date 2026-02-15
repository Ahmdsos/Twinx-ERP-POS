@extends('layouts.app')

@section('title', 'ملف السائق: ' . ($driver->employee->full_name ?? 'سائق'))
@section('header', 'تفاصيل السائق والعمليات اللوجستية')

@section('content')
    <div class="container-fluid pb-5">
        <!-- Header with Profile Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="glass-card rounded-4 p-4 border-secondary border-opacity-10 border-opacity-10 shadow-lg text-heading"
                    style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-auto text-center mb-3 mb-md-0">
                            <div class="avatar-xl rounded-circle bg-primary bg-opacity-20 d-flex align-items-center justify-content-center mx-auto border border-primary border-opacity-25"
                                style="width: 100px; height: 100px; font-size: 2.5rem;">
                                <i class="bi bi-person-badge-fill text-primary"></i>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                <h2 class="fw-black mb-0">{{ $driver->employee->full_name }}</h2>
                                <span
                                    class="badge bg-{{ $driver->is_in_field ? 'warning' : 'success' }} bg-opacity-10 text-{{ $driver->is_in_field ? 'warning' : 'success' }} border border-{{ $driver->is_in_field ? 'warning' : 'success' }} border-opacity-25 rounded-pill px-3">
                                    {{ $driver->is_in_field ? 'في الميدان بمهام نشطة' : 'متاح للعمل' }}
                                </span>
                            </div>
                            <p class="text-heading-50 mb-0 d-flex gap-4 flex-wrap">
                                <span><i class="bi bi-hash me-1"></i> كود الموظف:
                                    {{ $driver->employee->employee_code }}</span>
                                <span><i class="bi bi-telephone me-1"></i>
                                    {{ $driver->employee->phone ?? 'بدون تليفون' }}</span>
                                <span><i class="bi bi-truck me-1"></i> المركبة:
                                    {{ $driver->vehicle_info ?? 'غير محددة' }}</span>
                            </p>
                        </div>
                        <div class="col-md-auto mt-3 mt-md-0 d-flex gap-2">
                            <a href="{{ route('hr.delivery.edit', $driver) }}"
                                class="btn btn-outline-light rounded-pill px-4 btn-sm">تعديل البيانات</a>
                            <a href="{{ route('hr.delivery.index') }}"
                                class="btn btn-light rounded-pill px-4 btn-sm fw-bold">الرجوع للأسطول</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm h-100">
                    <span class="text-secondary small fw-bold d-block mb-1">نسبة النجاح (Success Rate)</span>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="fw-black mb-0">{{ $driver->success_rate }}%</h2>
                        <i class="bi bi-graph-up-arrow text-success"></i>
                    </div>
                    <div class="progress mt-3 bg-surface-secondary bg-opacity-10" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $driver->success_rate }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm h-100">
                    <span class="text-secondary small fw-bold d-block mb-1">إنجاز اليوم (التسليمات)</span>
                    <h2 class="fw-black mb-0">{{ $stats['delivered_today'] }}</h2>
                    <div class="text-success small mt-1 fw-bold"><i class="bi bi-check2-all me-1"></i> مهام مكتملة</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm h-100">
                    <span class="text-secondary small fw-bold d-block mb-1">مرتجع اليوم</span>
                    <h2 class="fw-black mb-0">{{ $stats['returned_today'] }}</h2>
                    <div class="text-danger small mt-1 fw-bold"><i class="bi bi-arrow-counterclockwise me-1"></i> مهام
                        مرفوضة</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4 rounded-4 shadow-sm h-100">
                    <span class="text-secondary small fw-bold d-block mb-1">إجمالي المهام</span>
                    <h2 class="fw-black mb-0">{{ $stats['total_all_time'] }}</h2>
                    <div class="text-primary small mt-1 fw-bold"><i class="bi bi-archive me-1"></i> منذ التسجيل</div>
                </div>
            </div>
        </div>

        <!-- Active Missions & History -->
        <div class="row">
            <!-- Active Section -->
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-warning bg-opacity-10 border-0 p-4">
                        <h5 class="fw-black mb-0 text-warning"><i class="bi bi-lightning-charge-fill me-2"></i> المهام
                            النشطة حالياً في عهدته</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 small fw-bold text-secondary">
                                    <tr>
                                        <th class="ps-4 py-3">رقم المهمة</th>
                                        <th>العميل</th>
                                        <th>المبلغ</th>
                                        <th>تاريخ التكليف</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="pe-4 text-end">التفاصيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeMissions as $mission)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-bold">{{ $mission->do_number }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $mission->customer->name }}</div>
                                                <div class="x-small text-muted text-truncate" style="max-width: 250px;">
                                                    {{ $mission->shipping_address }}</div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    {{ number_format($mission->salesOrder->total ?? $mission->salesInvoice->total_amount ?? 0, 2) }}
                                                    ج.م
                                                </span>
                                            </td>
                                            <td>{{ $mission->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3">
                                                    {{ $mission->status->label() }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <a href="{{ route('mission.control', ['search' => $mission->do_number]) }}"
                                                    class="btn btn-sm btn-icon btn-light rounded-circle shadow-none">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i
                                                    class="bi bi-emoji-smile text-secondary opacity-25 display-6 mb-2 d-block"></i>
                                                <p class="text-secondary mb-0">لا توجد مهام نشطة حالياً. السائق متاح للأوردرات
                                                    الجديدة.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-surface-secondary bg-opacity-10 border-0 p-4">
                        <h5 class="fw-black mb-0"><i class="bi bi-clock-history me-2"></i> سجل العمليات السابقة (آخر 50
                            مهمة)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 small fw-bold text-secondary">
                                    <tr>
                                        <th class="ps-4 py-3">رقم المهمة</th>
                                        <th>العميل</th>
                                        <th>المبلغ</th>
                                        <th>التاريخ</th>
                                        <th class="text-center">الحالة النهائية</th>
                                        <th class="pe-4 text-end">ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->shipments->sortByDesc('created_at')->take(50) as $shipment)
                                        @if(!in_array($shipment->status->value, ['shipped', 'ready']))
                                            <tr class="opacity-75">
                                                <td class="ps-4"><span class="small">{{ $shipment->do_number }}</span></td>
                                                <td>
                                                    <div class="small fw-medium">{{ $shipment->customer->name }}</div>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        {{ number_format($shipment->salesOrder->total ?? $shipment->salesInvoice->total_amount ?? 0, 2) }}
                                                        ج.م</div>
                                                </td>
                                                <td>
                                                    <div class="x-small">{{ $shipment->updated_at->format('Y-m-d H:i') }}</div>
                                                </td>
                                                <td class="text-center">
                                                    @php $cls = ($shipment->status->value == 'delivered' ? 'success' : 'danger'); @endphp
                                                    <span
                                                        class="badge bg-{{ $cls }} bg-opacity-10 text-{{ $cls }} rounded-pill px-2 py-1 x-small border border-{{ $cls }} border-opacity-25">
                                                        {{ $shipment->status->label() }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <div class="x-small text-muted text-truncate" style="max-width: 150px;">
                                                        {{ $shipment->notes ?? '---' }}</div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
        }

        .fw-black {
            font-weight: 900 !important;
        }

        .x-small {
            font-size: 0.7rem !important;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-card h2 {
            color: #f1f5f9;
        }

        .card {
            background-color: #1e293b !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        .table {
            color: #cbd5e1 !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .text-dark {
            color: #f1f5f9 !important;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
@endsection