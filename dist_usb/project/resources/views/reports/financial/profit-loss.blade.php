@extends('layouts.app')

@section('title', 'قائمة الدخل - Profit & Loss')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    <i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>
                    قائمة الدخل
                    <span class="fs-6 text-muted ms-2">(Profit & Loss)</span>
                </h4>
                <div class="text-muted small">
                    الفترة من <span class="text-body fw-bold">{{ $startDate }}</span>{{ __('To Date') }}<span
                        class="text-body fw-bold">{{ $endDate }}</span>
                </div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('reports.financial.pl') }}" method="GET" class="d-flex gap-2 glass-card p-1 rounded">
                    <input type="hidden" name="type" value="pl">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">{{ __('Update') }}</button>
                </form>
                <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="طباعة عادية A4">
                    <i class="bi bi-printer me-1"></i> طباعة عادية
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="thermalPrintPL()" title="طباعة حرارية 80mm">
                    <i class="bi bi-receipt me-1"></i> طباعة حرارية
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 position-relative overflow-hidden">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi bi-graph-up-arrow display-4 text-success"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">إجمالي الإيرادات</div>
                        <h3 class="fw-bold text-success mb-0 text-shadow">{{ number_format($data['revenue']['total'], 2) }}
                            <small class="fs-6 text-muted">{{ __('EGP') }}</small>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 position-relative overflow-hidden">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi bi-graph-down-arrow display-4 text-danger"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">إجمالي المصروفات</div>
                        <h3 class="fw-bold text-danger mb-0 text-shadow">{{ number_format($data['expenses']['total'], 2) }}
                            <small class="fs-6 text-muted">{{ __('EGP') }}</small>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div
                    class="card glass-card border-0 h-100 position-relative overflow-hidden {{ $data['net_profit'] >= 0 ? 'border-success border-bottom border-3' : 'border-danger border-bottom border-3' }}">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i
                            class="bi bi-wallet2 display-4 {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">صافي الربح / الخسارة</div>
                        <h3
                            class="fw-bold {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-0 text-shadow">
                            {{ number_format($data['net_profit'], 2) }} <small
                                class="fs-6 text-muted">{{ __('EGP') }}</small>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Report -->
        <div class="card glass-card border-0">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                <h6 class="fw-bold text-heading m-0"><i class="bi bi-list-ul me-2"></i> تفاصيل الحسابات</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="py-3 ps-4" style="width: 50%">{{ __('Account') }}</th>
                                <th class="py-3 text-end" style="width: 30%">{{ __('Balance') }}</th>
                                <th class="py-3 text-end pe-4" style="width: 20%">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Revenue Section -->
                            <tr class="bg-success bg-opacity-10">
                                <td class="ps-4 fw-bold text-success py-2" colspan="3">
                                    <i class="bi bi-arrow-up-circle me-2"></i> الإيرادات (Revenues)
                                </td>
                            </tr>
                            @forelse($data['revenue']['details'] as $account)
                                <tr>
                                    <td class="ps-5 border-start border-success border-opacity-25 border-3">
                                        <span class="fw-medium text-body">{{ __($account->name) }}</span>
                                        <span
                                            class="badge bg-secondary bg-opacity-25 text-muted ms-2 fw-normal">{{ $account->code }}</span>
                                    </td>
                                    <td
                                        class="text-end fw-bold {{ $account->period_balance < 0 ? 'text-danger' : 'text-body' }}">
                                        {{ number_format($account->period_balance, 2) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('reports.financial.ledger', ['id' => $account->id ?? 0, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                            class="btn btn-sm btn-outline-info opacity-75 py-0 px-2 small"
                                            title="{{ __('View Details') }}">
                                            <i class="bi bi-eye"></i>{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3 small">لا توجد إيرادات في هذه الفترة
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Expense Section -->
                            <tr class="bg-danger bg-opacity-10">
                                <td class="ps-4 fw-bold text-danger py-2 mt-3" colspan="3">
                                    <i class="bi bi-arrow-down-circle me-2"></i> المصروفات (Expenses)
                                </td>
                            </tr>
                            @forelse($data['expenses']['details'] as $account)
                                <tr>
                                    <td class="ps-5 border-start border-danger border-opacity-25 border-3">
                                        <span class="fw-medium text-body">{{ __($account->name) }}</span>
                                        <span
                                            class="badge bg-secondary bg-opacity-25 text-muted ms-2 fw-normal">{{ $account->code }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-body">
                                        {{ number_format($account->period_balance, 2) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('reports.financial.ledger', ['id' => $account->id ?? 0, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                            class="btn btn-sm btn-outline-info opacity-75 py-0 px-2 small"
                                            title="{{ __('View Details') }}">
                                            <i class="bi bi-eye"></i>{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3 small">لا توجد مصروفات في هذه الفترة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-surface bg-opacity-5 border-top border-secondary border-opacity-25">
                            <tr>
                                <td class="ps-4 py-3 fw-bold fs-5 text-body">صافي النتيجة</td>
                                <td
                                    class="text-end pe-4 py-3 fw-bold fs-5 {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} text-shadow">
                                    {{ number_format($data['net_profit'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
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
            h6,
            .fw-bold {
                color: black !important;
            }

            .text-success {
                color: #198754 !important;
            }

            .text-danger {
                color: #dc3545 !important;
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
        function thermalPrintPL() {
            const revenueRows = [];
            @foreach($data['revenue']['details'] as $account)
                revenueRows.push(['{{ __($account->name) }}', '{{ number_format($account->period_balance, 2) }}']);
            @endforeach

                    const expenseRows = [];
            @foreach($data['expenses']['details'] as $account)
                expenseRows.push(['{{ __($account->name) }}', '{{ number_format($account->period_balance, 2) }}']);
            @endforeach

            printThermal({
                title: 'قائمة الدخل',
                subtitle: 'من {{ $startDate }} إلى {{ $endDate }}',
                summaryCards: [
                    { label: 'إجمالي الإيرادات', value: '{{ number_format($data["revenue"]["total"], 2) }}' },
                    { label: 'إجمالي المصروفات', value: '{{ number_format($data["expenses"]["total"], 2) }}' },
                ],
                sections: [
                    {
                        title: '▲ الإيرادات',
                        headers: ['الحساب', 'الرصيد'],
                        rows: revenueRows,
                        footer: { label: 'إجمالي الإيرادات', value: '{{ number_format($data["revenue"]["total"], 2) }}' }
                    },
                    {
                        title: '▼ المصروفات',
                        headers: ['الحساب', 'الرصيد'],
                        rows: expenseRows,
                        footer: { label: 'إجمالي المصروفات', value: '{{ number_format($data["expenses"]["total"], 2) }}' }
                    }
                ],
                footerNote: {
                    label: 'صافي {{ $data["net_profit"] >= 0 ? "الربح" : "الخسارة" }}',
                    value: '{{ number_format($data["net_profit"], 2) }} ج.م'
                }
            });
        }
    </script>
@endsection