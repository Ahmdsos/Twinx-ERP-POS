@extends('layouts.app')

@section('title', 'تحليل العملاء - Customer Analysis')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    <i class="bi bi-people me-2 text-info"></i>
                    تحليل مبيعات العملاء
                </h4>
                <div class="text-muted small">أهم العملاء وحجم التعاملات</div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('reports.sales.by-customer') }}" method="GET"
                    class="d-flex gap-2 glass-card p-1 rounded">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <button type="submit" class="btn btn-sm btn-info text-white px-3 fw-bold">{{ __('Filter') }}</button>
                </form>
                <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="طباعة عادية A4">
                    <i class="bi bi-printer me-1"></i> طباعة عادية
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="thermalPrintByCustomer()" title="طباعة حرارية 80mm">
                    <i class="bi bi-receipt me-1"></i> طباعة حرارية
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card glass-card border-0 text-center py-3">
                    <div class="text-muted small text-uppercase">إجمالي المبيعات</div>
                    <h3 class="fw-bold text-info mb-0 text-shadow">{{ number_format($data->sum('total_sales'), 2) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-card border-0 text-center py-3">
                    <div class="text-muted small text-uppercase">إجمالي المديونيات</div>
                    <h3 class="fw-bold text-danger mb-0 text-shadow">{{ number_format($data->sum('total_due'), 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="py-3 ps-4">{{ __('Customer') }}</th>
                                <th class="py-3 text-center">عدد الفواتير</th>
                                <th class="py-3 text-end">إجمالي الشراء</th>
                                <th class="py-3 text-end pe-4">المديونية الحالية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $customer)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-body">{{ $customer->customer_name }}</div>
                                        <div class="small text-muted">{{ $customer->phone }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $customer->invoice_count }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-body">{{ number_format($customer->total_sales, 2) }}</td>
                                    <td class="text-end pe-4">
                                        <span
                                            class="{{ $customer->total_due > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                            {{ number_format($customer->total_due, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">{{ __('No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        

        .table-transparent {
            --bs-table-bg: transparent;
            --bs-table-color: #e0e0e0;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03);
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

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
            form {
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

            .table-dark {
                color: black !important;
                background-color: var(--text-primary); !important;
            }

            .badge {
                border: 1px solid #000 !important;
                color: black !important;
                background: transparent !important;
            }

            .text-white,
            .text-muted,
            h3,
            h4,
            .fw-bold {
                color: black !important;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .text-success {
                color: #198754 !important;
            }

            .card {
                border: 1px solid #ddd !important;
            }

            .text-shadow {
                text-shadow: none !important;
            }

            @page {
                margin: 1cm;
                size: A4 portrait;
            }
        }
    </style>

    <script src="{{ asset('js/thermal-print.js') }}"></script>
    <script>
        function thermalPrintByCustomer() {
            const rows = [];
            @foreach($data as $customer)
                rows.push([
                    '{{ $customer->customer_name }}',
                    '{{ $customer->invoice_count }}',
                    '{{ number_format($customer->total_sales, 2) }}'
                ]);
            @endforeach

            printThermal({
                title: 'تحليل مبيعات العملاء',
                subtitle: 'من {{ $startDate }} إلى {{ $endDate }}',
                summaryCards: [
                    { label: 'إجمالي المبيعات', value: '{{ number_format($data->sum("total_sales"), 2) }}' },
                    { label: 'إجمالي المديونيات', value: '{{ number_format($data->sum("total_due"), 2) }}' },
                ],
                sections: [
                    {
                        title: 'العملاء',
                        headers: ['العميل', 'فواتير', 'المبيعات'],
                        rows: rows,
                        footer: { label: 'الإجمالي', value: '{{ number_format($data->sum("total_sales"), 2) }}' }
                    }
                ]
            });
        }
    </script>
@endsection