@extends('layouts.app')

@section('title', 'كشف حساب: ' . $supplier->name)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3 print-header">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('suppliers.show', $supplier->id) }}"
                    class="btn btn-outline-light btn-sm rounded-circle shadow-sm d-print-none"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">كشف حساب مورد</h2>
                    <div class="d-flex align-items-center gap-2 text-gray-400">
                        <span class="fw-bold text-cyan-400">{{ $supplier->name }}</span>
                        <span class="font-monospace">({{ $supplier->code }})</span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 d-print-none">
                <button onclick="window.print()" class="btn btn-outline-cyan">
                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}</button>
                @if($closingBalance > 0)
                <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id, 'amount' => $closingBalance]) }}" class="btn btn-action-purple">
                    <i class="bi bi-cash-stack me-2"></i>سداد الرصيد
                </a>
                @endif
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="glass-panel p-3 mb-4 d-print-none">
            <form action="{{ route('suppliers.statement', $supplier->id) }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-gray-400 x-small fw-bold">من تاريخ</label>
                    <input type="date" name="start_date" class="form-control form-control-dark focus-ring-cyan"
                        value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-gray-400 x-small fw-bold">إلى تاريخ</label>
                    <input type="date" name="end_date" class="form-control form-control-dark focus-ring-cyan"
                        value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 bg-gradient-cyan border-0">{{ __('Update') }}</button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="glass-panel p-3 text-center border-top border-4 border-gray-600">
                    <span class="text-gray-400 x-small fw-bold d-block mb-1">رصيد افتتاحي</span>
                    <h4 class="text-heading fw-bold mb-0 {{ $openingBalance > 0 ? 'text-red-400' : 'text-green-400' }}">
                        {{ number_format(abs($openingBalance), 2) }}
                        <span class="x-small text-gray-500">{{ $openingBalance > 0 ? 'له' : 'عليه' }}</span>
                    </h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-3 text-center border-top border-4 border-cyan-500">
                    <span class="text-cyan-400 x-small fw-bold d-block mb-1">إجمالي الحركات المدينة</span>
                    <h4 class="text-heading fw-bold mb-0">
                        {{ number_format($transactions->sum('debit'), 2) }}
                    </h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-3 text-center border-top border-4 border-green-500">
                    <span class="text-green-400 x-small fw-bold d-block mb-1">إجمالي الحركات الدائنة</span>
                    <h4 class="text-heading fw-bold mb-0">
                        {{ number_format($transactions->sum('credit'), 2) }}
                    </h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-3 text-center border-top border-4 border-purple-500">
                    <span class="text-purple-400 x-small fw-bold d-block mb-1">{{ __('Closing Balance') }}</span>
                    <h4 class="text-heading fw-bold mb-0 {{ $closingBalance > 0 ? 'text-red-400' : 'text-green-400' }}">
                        {{ number_format(abs($closingBalance), 2) }}
                        <span class="x-small text-gray-500">{{ $closingBalance > 0 ? 'له' : 'عليه' }}</span>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="glass-panel overflow-hidden">
            <div class="table-responsive">
                <table class="table table-dark-custom table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>نوع الحركة</th>
                            <th>المرجع #</th>
                            <th>البيان</th>
                            <th class="text-end">مدين (فواتير)</th>
                            <th class="text-end">دائن (مدفوعات)</th>
                            <th class="text-end">{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance Row -->
                        <tr class="bg-slate-800">
                            <td colspan="4" class="text-gray-400 fw-bold ps-4">
                                <i class="bi bi-play-circle me-2"></i>رصيد ما قبل الفترة
                            </td>
                            <td class="text-end"></td>
                            <td class="text-end"></td>
                            <td class="text-end fw-bold {{ $openingBalance > 0 ? 'text-red-400' : 'text-green-400' }}">
                                {{ number_format(abs($openingBalance), 2) }}
                                <small>{{ $openingBalance > 0 ? 'مدين' : 'دائن' }}</small>
                            </td>
                        </tr>

                        <!-- Transactions -->
                        @forelse($transactions as $trx)
                            <tr>
                                <td class="font-monospace text-gray-300">
                                    {{ \Carbon\Carbon::parse($trx['date'])->format('Y-m-d') }}</td>
                                <td>
                                    @if($trx['type'] == 'invoice')
                                        <span class="badge bg-cyan-500 bg-opacity-10 text-cyan-400">فاتورة</span>
                                    @else
                                        <span class="badge bg-green-500 bg-opacity-10 text-green-400">دفعة</span>
                                    @endif
                                </td>
                                <td class="font-monospace text-gray-400">{{ $trx['reference'] }}</td>
                                <td class="text-body">{{ $trx['description'] }}</td>
                                <td class="text-end text-body">{{ $trx['debit'] > 0 ? number_format($trx['debit'], 2) : '-' }}
                                </td>
                                <td class="text-end text-body">
                                    {{ $trx['credit'] > 0 ? number_format($trx['credit'], 2) : '-' }}</td>
                                <td class="text-end fw-bold {{ $trx['balance'] > 0 ? 'text-red-400' : 'text-green-400' }}">
                                    {{ number_format(abs($trx['balance']), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">لا توجد حركات خلال هذه الفترة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        .bg-gradient-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.4);
            color: var(--text-secondary);
            font-weight: 600;
        }

        .form-control-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
            }

            .glass-panel {
                background: white !important;
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                color: black !important;
            }

            .text-white {
                color: black !important;
            }

            .text-gray-400,
            .text-gray-500 {
                color: #666 !important;
            }

            .table-dark-custom {
                color: black !important;
                --bs-table-striped-bg: #f8f9fa;
            }

            .table-dark-custom th {
                background: #f1f5f9 !important;
                color: black !important;
                border-bottom: 2px solid #000;
            }

            .d-print-none {
                display: none !important;
            }

            /* Force badge colors */
            .badge {
                border: 1px solid #000;
                color: black !important;
                background: transparent !important;
            }

            .fw-bold {
                font-weight: bold !important;
            }
        }
    </style>
@endsection