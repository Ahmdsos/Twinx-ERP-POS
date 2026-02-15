@extends('layouts.app')

@section('title', 'كشف حساب - General Ledger')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-heading mb-1">
                    <i class="bi bi-journal-text me-2 text-primary"></i>
                    كشف حساب: {{ $account->name }}
                    <span class="fs-6 text-muted ms-2">({{ $account->code }})</span>
                </h4>
                <div class="text-muted small">
                    الفترة من <span class="text-body fw-bold">{{ $startDate }}</span>{{ __('To Date') }}<span
                        class="text-body fw-bold">{{ $endDate }}</span>
                </div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('reports.financial.ledger', $account->id) }}" method="GET"
                    class="d-flex gap-2 glass-card p-1 rounded">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm bg-transparent text-body border-0">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">{{ __('Update') }}</button>
                </form>
                <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="طباعة عادية A4">
                    <i class="bi bi-printer me-1"></i> طباعة عادية
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="thermalPrintLedger()" title="طباعة حرارية 80mm">
                    <i class="bi bi-receipt me-1"></i> طباعة حرارية
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-arrow-return-right"></i> عودة
                </a>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="py-3 ps-4">{{ __('Date') }}</th>
                                <th class="py-3">{{ __('Entry Number') }}</th>
                                <th class="py-3">البيان</th>
                                <th class="py-3 text-end">مدين (Debit)</th>
                                <th class="py-3 text-end">دائن (Credit)</th>
                                <th class="py-3 text-end pe-4">الرصيد (Balance)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Opening Balance -->
                            <tr class="bg-surface bg-opacity-5">
                                <td colspan="3" class="ps-4 py-2 fw-bold text-warning">رصيد افتتاحي (قبل {{ $startDate }})
                                </td>
                                <td class="text-end fw-bold text-warning">-</td>
                                <td class="text-end fw-bold text-warning">-</td>
                                <td class="text-end pe-4 fw-bold text-warning">
                                    {{ number_format($data['opening_balance'], 2) }}
                                </td>
                            </tr>

                    @php
                        $runningBalance = $data['opening_balance'];
                        $isDebitNormal = $account->type->debitIncreases();
                    @endphp

                            @forelse($data['lines'] as $line)
                                @php
                                    if ($isDebitNormal) {
                                        $runningBalance += ($line->debit - $line->credit);
                                    } else {
                                        $runningBalance += ($line->credit - $line->debit);
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        {{ \Carbon\Carbon::parse($line->journalEntry->entry_date)->format('Y-m-d') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-25 text-muted fw-normal">
                                            #{{ $line->journalEntry->entry_number }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $line->journalEntry->description }}</td>
                                    <td class="text-end font-monospace text-body">
                                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-end font-monospace text-body">
                                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                    </td>
                                    <td
                                        class="text-end pe-4 fw-bold {{ $runningBalance < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($runningBalance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">لا توجد حركات في هذه الفترة</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-surface bg-opacity-5 border-top border-secondary border-opacity-25">
                            <tr>
                                <td colspan="5" class="ps-4 py-3 fw-bold fs-6 text-body text-end">
                                    {{ __('Closing Balance') }}</td>
                                <td
                                    class="text-end pe-4 py-3 fw-bold fs-6 {{ $runningBalance < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($runningBalance, 2) }}
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
            a.btn {
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
            h4,
            .fw-bold,
            .font-monospace {
                color: black !important;
            }

            .text-success {
                color: #198754 !important;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .text-warning {
                color: #886400 !important;
            }

            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }
    </style>

    <script src="{{ asset('js/thermal-print.js') }}"></script>
    <script>
        function thermalPrintLedger() {
            const rows = [];
            @php $thermalBalance = $data['opening_balance']; @endphp
            @forelse($data['lines'] as $line)
                @php
                    if ($isDebitNormal) {
                        $thermalBalance += ($line->debit - $line->credit);
                    } else {
                        $thermalBalance += ($line->credit - $line->debit);
                    }
                @endphp
                rows.push([
                    '{{ \Carbon\Carbon::parse($line->journalEntry->entry_date)->format("m/d") }}',
                    '{{ $line->debit > 0 ? number_format($line->debit, 2) : "-" }}',
                    '{{ $line->credit > 0 ? number_format($line->credit, 2) : "-" }}',
                    '{{ number_format($thermalBalance, 2) }}'
                ]);
            @empty
            @endforelse

            printThermal({
                title: 'كشف حساب',
                subtitle: '{{ __($account->name) }} ({{ $account->code }})',
                summaryCards: [
                    { label: 'الفترة', value: '{{ $startDate }} → {{ $endDate }}' },
                    { label: 'رصيد افتتاحي', value: '{{ number_format($data["opening_balance"], 2) }}' },
                ],
                sections: [
                    {
                        title: 'الحركات',
                        headers: ['التاريخ', 'مدين', 'دائن', 'الرصيد'],
                        rows: rows,
                    }
                ],
                footerNote: {
                    label: 'الرصيد الختامي',
                    value: '{{ number_format($thermalBalance ?? $runningBalance, 2) }} ج.م'
                }
            });
        }
    </script>
@endsection