@extends('layouts.app')

@section('title', 'مرتجع المشتريات')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-orange shadow-neon-orange">
                    <i class="bi bi-arrow-return-left fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">مرتجع المشتريات</h2>
                    <p class="mb-0 text-gray-400 small">إدارة المرتجعات للموردين</p>
                </div>
            </div>
            <a href="{{ route('purchase-returns.create') }}"
                class="btn btn-action-orange d-flex align-items-center gap-2 shadow-lg">
                <i class="bi bi-plus-lg"></i>
                <span class="fw-bold">تسجيل مرتجع جديد</span>
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-orange-400 x-small fw-bold text-uppercase tracking-wide">إجمالي
                                المرتجعات</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['total_returns'], 2) }} <small
                                    class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-orange-500 bg-opacity-10 text-orange-400">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-yellow-400 x-small fw-bold text-uppercase tracking-wide">مرتجعات الشهر</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['this_month'], 2) }} <small
                                    class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-yellow-500 bg-opacity-10 text-yellow-400">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Returns Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-orange">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">رقم الإشعار</th>
                            <th>المورد</th>
                            <th>رقم الفاتورة الأصلية</th>
                            <th>تاريخ المرتجع</th>
                            <th>القيمة</th>
                            <th>الحالة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 font-monospace text-orange-300">
                                    <a href="{{ route('purchase-returns.show', $return->id) }}"
                                        class="text-decoration-none text-orange-300 hover-text-white">
                                        {{ $return->return_number }}
                                    </a>
                                </td>
                                <td class="fw-bold text-white">{{ $return->supplier->name }}</td>
                                <td class="font-monospace text-gray-400">{{ $return->invoice->invoice_number ?? '-' }}</td>
                                <td class="text-gray-400 x-small">{{ $return->return_date->format('Y-m-d') }}</td>
                                <td class="fw-bold text-white">{{ number_format($return->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-green-500 bg-opacity-10 text-green-400">
                                        مكتمل
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('purchase-returns.show', $return->id) }}" class="btn-icon-glass"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-box-seam fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">لا توجد مرتجعات</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($returns->hasPages())
                <div class="p-4 border-top border-white-5">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-gradient-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .shadow-neon-orange {
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.4);
        }

        .text-orange-400 {
            color: #fb923c !important;
        }

        .text-orange-300 {
            color: #fdba74 !important;
        }

        .bg-orange-500 {
            background: #f97316 !important;
        }

        .border-top-gradient-orange {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #f97316, #fb923c) 1;
        }

        .btn-action-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-orange:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(249, 115, 22, 0.4);
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: #94a3b8;
            font-weight: 600;
            padding: 1rem;
        }

        .table-dark-custom td {
            padding: 1rem;
        }

        .group-hover-actions:hover .group-hover-visible {
            opacity: 1 !important;
        }

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            transition: 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
    </style>
@endsection