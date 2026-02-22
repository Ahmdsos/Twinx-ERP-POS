@extends('layouts.app')

@section('title', 'الميزانية العمومية - Balance Sheet')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    <i class="bi bi-bank me-2 text-primary"></i>{{ __('Balance Sheet') }}<span
                        class="fs-6 text-muted ms-2">(Balance Sheet)</span>
                </h4>
                <div class="text-muted small">
                    كما في <span class="text-body fw-bold">{{ $endDate }}</span>
                </div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('reports.financial.bs') }}" method="GET" class="d-flex gap-2 glass-card p-1 rounded">
                    <input type="hidden" name="type" value="bs">
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">{{ __('Update') }}</button>
                </form>
                <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="طباعة عادية A4">
                    <i class="bi bi-printer me-1"></i> طباعة عادية
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="thermalPrintBS()" title="طباعة حرارية 80mm">
                    <i class="bi bi-receipt me-1"></i> طباعة حرارية
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 position-relative overflow-hidden">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi bi-cash-stack display-4 text-info"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">إجمالي الأصول (Assets)</div>
                        <h3 class="fw-bold text-info mb-0 text-shadow">{{ number_format($data['totals']['assets'], 2) }}
                            <small class="fs-6 text-muted">{{ __('EGP') }}</small>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 position-relative overflow-hidden">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi bi-shield-lock display-4 text-warning"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">الالتزامات (Liabilities)</div>
                        <h3 class="fw-bold text-warning mb-0 text-shadow">
                            {{ number_format($data['totals']['liabilities'], 2) }}
                            <small class="fs-6 text-muted">{{ __('EGP') }}</small>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 position-relative overflow-hidden">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi bi-pie-chart display-4 text-success"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">حقوق الملكية (Equity)</div>
                        <h3 class="fw-bold text-success mb-0 text-shadow">{{ number_format($data['totals']['equity'], 2) }}
                            <small class="fs-6 text-muted">{{ __('EGP') }}</small>
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
                            <!-- Assets Section -->
                            <tr class="bg-info bg-opacity-10">
                                <td class="ps-4 fw-bold text-info py-2" colspan="3">
                                    <i class="bi bi-plus-circle me-2"></i> الأصول (Assets)
                                </td>
                            </tr>
                            @foreach($data['assets'] as $group)
                                @foreach($group as $account)
                                    <tr>
                                        <td class="ps-5 border-start border-info border-opacity-25 border-3">
                                            <span class="fw-medium text-body">{{ __($account->name) }}</span>
                                            <span
                                                class="badge bg-secondary bg-opacity-25 text-muted ms-2 fw-normal">{{ $account->code }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-body">
                                            {{ number_format($account->period_balance, 2) }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('reports.financial.ledger', ['id' => $account->id ?? 0, 'start_date' => \Carbon\Carbon::parse($endDate)->startOfYear()->format('Y-m-d'), 'end_date' => $endDate]) }}"
                                                class="btn btn-sm btn-outline-info opacity-75 py-0 px-2 small"
                                                title="{{ __('View Details') }}">
                                                <i class="bi bi-eye"></i>{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            <!-- Liabilities Section -->
                            <tr class="bg-warning bg-opacity-10">
                                <td class="ps-4 fw-bold text-warning py-2 mt-3" colspan="3">
                                    <i class="bi bi-dash-circle me-2"></i> الالتزامات (Liabilities)
                                </td>
                            </tr>
                            @foreach($data['liabilities'] as $group)
                                @foreach($group as $account)
                                    <tr>
                                        <td class="ps-5 border-start border-warning border-opacity-25 border-3">
                                            <span class="fw-medium text-body">{{ __($account->name) }}</span>
                                            <span
                                                class="badge bg-secondary bg-opacity-25 text-muted ms-2 fw-normal">{{ $account->code }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-body">
                                            {{ number_format($account->period_balance, 2) }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('reports.financial.ledger', ['id' => $account->id ?? 0, 'start_date' => \Carbon\Carbon::parse($endDate)->startOfYear()->format('Y-m-d'), 'end_date' => $endDate]) }}"
                                                class="btn btn-sm btn-outline-warning opacity-75 py-0 px-2 small"
                                                title="{{ __('View Details') }}">
                                                <i class="bi bi-eye"></i>{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            <!-- Equity Section -->
                            <tr class="bg-success bg-opacity-10">
                                <td class="ps-4 fw-bold text-success py-2 mt-3" colspan="3">
                                    <i class="bi bi-check-circle me-2"></i> حقوق الملكية (Equity)
                                </td>
                            </tr>
                            @foreach($data['equity'] as $group)
                                @foreach($group as $account)
                                    <tr>
                                        <td class="ps-5 border-start border-success border-opacity-25 border-3">
                                            <span class="fw-medium text-body">{{ __($account->name) }}</span>
                                            <span
                                                class="badge bg-secondary bg-opacity-25 text-muted ms-2 fw-normal">{{ $account->code }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-body">
                                            {{ number_format($account->period_balance, 2) }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('reports.financial.ledger', ['id' => $account->id ?? 0, 'start_date' => \Carbon\Carbon::parse($endDate)->startOfYear()->format('Y-m-d'), 'end_date' => $endDate]) }}"
                                                class="btn btn-sm btn-outline-success opacity-75 py-0 px-2 small"
                                                title="{{ __('View Details') }}">
                                                <i class="bi bi-eye"></i>{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot class="bg-surface bg-opacity-5 border-top border-secondary border-opacity-25">
                            <tr>
                                <td class="ps-4 py-3 fw-bold fs-5 text-body">إجمالي الالتزامات وحقوق الملكية</td>
                                <td class="text-end pe-4 py-3 fw-bold fs-5 text-shadow">
                                    {{ number_format($data['totals']['liabilities'] + $data['totals']['equity'], 2) }}
                                </td>
                                <td></td>
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

            .text-info {
                color: #0dcaf0 !important;
            }

            .text-warning {
                color: #ffc107 !important;
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
        function thermalPrintBS() {
            const assetRows = [];
            @foreach($data['assets'] as $group)
                @foreach($group as $account)
                    assetRows.push(['{{ __($account->name) }}', '{{ number_format($account->period_balance, 2) }}']);
                @endforeach
            @endforeach

                        const liabilityRows = [];
            @foreach($data['liabilities'] as $group)
                @foreach($group as $account)
                    liabilityRows.push(['{{ __($account->name) }}', '{{ number_format($account->period_balance, 2) }}']);
                @endforeach
            @endforeach

                        const equityRows = [];
            @foreach($data['equity'] as $group)
                @foreach($group as $account)
                    equityRows.push(['{{ __($account->name) }}', '{{ number_format($account->period_balance, 2) }}']);
                @endforeach
            @endforeach

            printThermal({
                title: 'الميزانية العمومية',
                subtitle: 'كما في {{ $endDate }}',
                summaryCards: [
                    { label: 'إجمالي الأصول', value: '{{ number_format($data["totals"]["assets"], 2) }}' },
                    { label: 'الالتزامات', value: '{{ number_format($data["totals"]["liabilities"], 2) }}' },
                    { label: 'حقوق الملكية', value: '{{ number_format($data["totals"]["equity"], 2) }}' },
                ],
                sections: [
                    {
                        title: '◆ الأصول',
                        headers: ['الحساب', 'الرصيد'],
                        rows: assetRows,
                        footer: { label: 'إجمالي الأصول', value: '{{ number_format($data["totals"]["assets"], 2) }}' }
                    },
                    {
                        title: '◆ الالتزامات',
                        headers: ['الحساب', 'الرصيد'],
                        rows: liabilityRows,
                        footer: { label: 'إجمالي الالتزامات', value: '{{ number_format($data["totals"]["liabilities"], 2) }}' }
                    },
                    {
                        title: '◆ حقوق الملكية',
                        headers: ['الحساب', 'الرصيد'],
                        rows: equityRows,
                        footer: { label: 'إجمالي حقوق الملكية', value: '{{ number_format($data["totals"]["equity"], 2) }}' }
                    }
                ],
                footerNote: {
                    label: 'الالتزامات + الملكية',
                    value: '{{ number_format($data["totals"]["liabilities"] + $data["totals"]["equity"], 2) }} ج.م'
                }
            });
        }
    </script>
@endsection