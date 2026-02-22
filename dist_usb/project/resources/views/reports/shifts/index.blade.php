@extends('layouts.app')

@section('title', __('Shift Reports'))

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-heading">
                <i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Shift Reports') }}</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="طباعة عادية A4">
                    <i class="bi bi-printer me-1"></i> طباعة عادية
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="thermalPrintShifts()" title="طباعة حرارية 80mm">
                    <i class="bi bi-receipt me-1"></i> طباعة حرارية
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4 border-0 glass-card d-print-none"
            style="background: rgba(30, 30, 40, 0.8); border: 1px solid var(--btn-glass-border);">
            <div class="card-body rounded-3">
                <form action="{{ route('reports.shifts') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">الكاشير</label>
                        <select name="cashier_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">كل الكاشيرات</option>
                            @foreach($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" {{ request('cashier_id') == $cashier->id ? 'selected' : '' }}>
                                    {{ $cashier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">{{ __('Status') }}</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="">{{ __('All') }}</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control bg-dark text-white border-secondary"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control bg-dark text-white border-secondary"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-filter me-1"></i>{{ __('Filter') }}</button>
                        <a href="{{ route('reports.shifts') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Shifts Table -->
        <div class="card shadow-sm border-0 glass-card"
            style="background: rgba(30, 30, 40, 0.8); border: 1px solid var(--btn-glass-border);">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-body">
                        <thead class="text-secondary" style="background: rgba(0,0,0,0.2);">
                            <tr>
                                <th class="ps-4"># الوردية</th>
                                <th>الكاشير</th>
                                <th>توقيت الفتح</th>
                                <th>توقيت الإغلاق</th>
                                <th>إجمالي المبيعات</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody style="border-top-color: rgba(255,255,255,0.1);">
                            @forelse($shifts as $shift)
                                <tr style="border-bottom-color: rgba(255,255,255,0.05);">
                                    <td class="ps-4 fw-bold text-body">#{{ $shift->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;">
                                                {{ substr($shift->user->name, 0, 1) }}
                                            </div>
                                            <span class="text-body">{{ $shift->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-body">{{ $shift->opened_at->format('Y-m-d') }}</div>
                                        <div class="small text-secondary">{{ $shift->opened_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        @if($shift->closed_at)
                                            <div class="text-body">{{ $shift->closed_at->format('Y-m-d') }}</div>
                                            <div class="small text-secondary">{{ $shift->closed_at->format('h:i A') }}</div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">
                                        {{ number_format($shift->total_amount, 2) }} EGP
                                    </td>
                                    <td>
                                        @if($shift->status === 'open')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-circle-fill small me-1"></i> مفتوحة
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                مغلقة
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('pos.shift.report', $shift->id) }}"
                                            class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="bi bi-eye me-1"></i> عرض التقرير
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        <div class="mb-2"><i class="bi bi-inbox fs-1 opacity-25"></i></div>
                                        لا توجد ورديات مطابقة للفلاتر
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($shifts->hasPages())
                <div class="card-footer border-top py-3 d-print-none"
                    style="background: transparent; border-top-color: rgba(255,255,255,0.1) !important;">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }

            

            .btn,
            header,
            nav,
            .sidebar,
            #sidebar-wrapper,
            .d-print-none,
            form,
            .card-footer {
                display: none !important;
            }

            .table {
                color: black !important;
                border: 1px solid #ddd !important;
                width: 100% !important;
            }

            .table th,
            .table td {
                color: black !important;
                border: 1px solid #ddd !important;
            }

            .badge {
                border: 1px solid #000 !important;
                color: black !important;
                background: transparent !important;
            }

            .text-white,
            .text-secondary,
            h2,
            .fw-bold {
                color: black !important;
            }

            .text-success {
                color: #198754 !important;
            }

            .avatar-sm {
                display: none !important;
            }

            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }
    </style>

    <script src="{{ asset('js/thermal-print.js') }}"></script>
    <script>
        function thermalPrintShifts() {
            const rows = [];
            @foreach($shifts as $shift)
                rows.push([
                    '#{{ $shift->id }} - {{ $shift->user->name }}',
                    '{{ $shift->opened_at->format("m/d H:i") }}',
                    '{{ number_format($shift->total_amount, 2) }}'
                ]);
            @endforeach

            printThermal({
                title: 'تقارير الورديات',
                subtitle: '{{ request("date_from") ? request("date_from") . " → " . request("date_to", now()->format("Y-m-d")) : now()->format("Y-m-d") }}',
                summaryCards: [
                    { label: 'عدد الورديات', value: '{{ $shifts->total() }}' },
                    { label: 'إجمالي المبيعات', value: '{{ number_format($shifts->sum("total_amount"), 2) }}' },
                ],
                sections: [
                    {
                        title: 'الورديات',
                        headers: ['الوردية / الكاشير', 'الفتح', 'المبيعات'],
                        rows: rows,
                        footer: { label: 'الإجمالي', value: '{{ number_format($shifts->sum("total_amount"), 2) }}' }
                    }
                ]
            });
        }
    </script>
@endsection