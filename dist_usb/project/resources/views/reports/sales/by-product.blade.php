@extends('layouts.app')

@section('title', 'تحليل المبيعات - Sales Analysis')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    <i class="bi bi-box-seam me-2 text-primary"></i>
                    تحليل مبيعات الأصناف
                </h4>
                <div class="text-muted small">أداء المنتجات الأكثر مبيعاً</div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('reports.sales.by-product') }}" method="GET"
                    class="d-flex gap-2 glass-card p-1 rounded">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">{{ __('Filter') }}</button>
                </form>
                <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="طباعة عادية A4">
                    <i class="bi bi-printer me-1"></i> طباعة عادية
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="thermalPrintSalesByProduct()"
                    title="طباعة حرارية 80mm">
                    <i class="bi bi-receipt me-1"></i> طباعة حرارية
                </button>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card glass-card border-0 text-center py-3">
                    <div class="text-muted small text-uppercase">إجمالي المبيعات</div>
                    <h3 class="fw-bold text-primary mb-0 text-shadow">{{ number_format($data->sum('total_sales'), 2) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-card border-0 text-center py-3">
                    <div class="text-muted small text-uppercase">الكمية المباعة</div>
                    <h3 class="fw-bold text-heading mb-0 text-shadow">{{ number_format($data->sum('total_qty'), 0) }}</h3>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="py-3 ps-4">{{ __('Product') }}</th>
                                <th class="py-3 text-center">الكمية المباعة</th>
                                <th class="py-3 text-end">قيمة المبيعات</th>
                                <th class="py-3 text-end pe-4">نسبة المساهمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = $data->sum('total_sales'); @endphp
                            @forelse($data as $item)
                                @php $percent = $grandTotal > 0 ? ($item->total_sales / $grandTotal) * 100 : 0; @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-body">{{ $item->product_name }}</div>
                                        <div class="small text-muted font-monospace">{{ $item->sku }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-surface bg-opacity-10 text-body border border-secondary border-opacity-25">{{ number_format($item->total_qty, 0) }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-body">{{ number_format($item->total_sales, 2) }}</td>
                                    <td class="text-end pe-4" style="width: 250px;">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="small text-muted">{{ number_format($percent, 1) }}%</span>
                                            <div class="progress flex-grow-1 bg-surface bg-opacity-10"
                                                style="height: 6px; width: 80px;">
                                                <div class="progress-bar bg-primary shadow-sm" role="progressbar"
                                                    style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">لا توجد بيانات مبيعات في هذه الفترة
                                    </td>
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
                background-color: var(--text-primary);
                !important;
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

            .progress {
                border: 1px solid #ddd !important;
            }

            .progress-bar {
                background-color: #333 !important;
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
        function thermalPrintSalesByProduct() {
            const rows = [];
            @php $grandTotal = $data->sum('total_sales'); @endphp
            @foreach($data as $item)
                rows.push([
                    '{{ __($item->product_name) }}',
                    '{{ number_format($item->total_qty, 0) }}',
                    '{{ number_format($item->total_sales, 2) }}'
                ]);
            @endforeach

            printThermal({
                title: 'تحليل مبيعات الأصناف',
                subtitle: 'من {{ $startDate }} إلى {{ $endDate }}',
                summaryCards: [
                    { label: 'إجمالي المبيعات', value: '{{ number_format($grandTotal, 2) }}' },
                    { label: 'الكمية المباعة', value: '{{ number_format($data->sum("total_qty"), 0) }}' },
                ],
                sections: [
                    {
                        title: 'الأصناف',
                        headers: ['المنتج', 'الكمية', 'المبيعات'],
                        rows: rows,
                        footer: { label: 'الإجمالي', value: '{{ number_format($grandTotal, 2) }}' }
                    }
                ]
            });
        }
    </script>
@endsection