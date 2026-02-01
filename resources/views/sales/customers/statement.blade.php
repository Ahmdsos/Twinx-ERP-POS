@extends('layouts.app')

@section('title', 'كشف حساب عميل: ' . $customer->name)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-light rounded-circle p-2" style="width: 40px; height: 40px;">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h3 class="fw-bold text-white mb-0">كشف حساب</h3>
                    <p class="text-gray-400 mb-0 small">{{ $customer->name }} ({{ $customer->code }})</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-light d-flex align-items-center gap-2">
                    <i class="bi bi-printer"></i> طباعة
                </button>
                <div class="bg-slate-800 rounded px-3 py-1 border border-slate-700">
                    <small class="text-gray-400 d-block">الرصيد الحالي</small>
                    <span class="fw-bold fs-5 {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-success' : 'text-white') }}">
                        {{ number_format(abs($balance), 2) }} {{ $balance > 0 ? 'مدين' : ($balance < 0 ? 'دائن' : '') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-slate-900 bg-opacity-50 border border-white-5 rounded-4 p-4 mb-4 dont-print">
            <form action="{{ route('customers.statement', $customer->id) }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-gray-400 small">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control bg-dark border-secondary text-white" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-gray-400 small">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control bg-dark border-secondary text-white" value="{{ $toDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> عرض
                    </button>
                </div>
            </form>
        </div>

        <!-- Statement Table -->
        <div class="card bg-slate-900 border-slate-800 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="bg-slate-800">
                            <tr>
                                <th class="p-3">التاريخ</th>
                                <th class="p-3">نوع الحركة</th>
                                <th class="p-3">المرجع</th>
                                <th class="p-3">البيان</th>
                                <th class="p-3 text-end text-danger">مدين (لنا)</th>
                                <th class="p-3 text-end text-success">دائن (لكم)</th>
                                <th class="p-3 text-end">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Opening Balance -->
                            <tr class="bg-slate-800 bg-opacity-50">
                                <td colspan="4" class="p-3 fw-bold">الرصيد الافتتاحي (قبل {{ $fromDate }})</td>
                                <td class="p-3 text-end fw-bold">{{ $openingBalance > 0 ? number_format($openingBalance, 2) : '-' }}</td>
                                <td class="p-3 text-end fw-bold">{{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '-' }}</td>
                                <td class="p-3 text-end fw-bold">{{ number_format($openingBalance, 2) }}</td>
                            </tr>

                            @php $runningBalance = $openingBalance; @endphp
                            @forelse($transactions as $trans)
                                @php 
                                    $debit = $trans->debit ?? 0;
                                    $credit = $trans->credit ?? 0;
                                    $runningBalance += ($debit - $credit);
                                @endphp
                                <tr>
                                    <td class="p-3">{{ \Carbon\Carbon::parse($trans->date)->format('Y-m-d') }}</td>
                                    <td class="p-3">
                                        @if($trans->type == 'invoice')
                                            <span class="badge bg-purple-500 bg-opacity-20 text-purple-300">فاتورة مبيعات</span>
                                        @elseif($trans->type == 'payment')
                                            <span class="badge bg-green-500 bg-opacity-20 text-green-300">سداد</span>
                                        @else
                                            <span class="badge bg-gray-500 bg-opacity-20 text-gray-300">{{ $trans->type }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 font-monospace">{{ $trans->reference }}</td>
                                    <td class="p-3 text-gray-400">{{ $trans->description }}</td>
                                    <td class="p-3 text-end">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                                    <td class="p-3 text-end">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                                    <td class="p-3 text-end fw-bold">{{ number_format($runningBalance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-gray-500">لا توجد حركات خلال هذه الفترة</td>
                                </tr>
                            @endforelse

                            <!-- Totals -->
                            <tr class="bg-slate-800 fw-bold border-top border-secondary">
                                <td colspan="4" class="p-3 text-end">المجاميع</td>
                                <td class="p-3 text-end text-danger">{{ number_format($totalInvoices, 2) }}</td>
                                <td class="p-3 text-end text-success">{{ number_format($totalPayments, 2) }}</td>
                                <td class="p-3 text-end">{{ number_format($balance, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .dont-print { display: none !important; }
            body, .container-fluid { background: #fff !important; color: #000 !important; }
            .table-dark { color: #000 !important; --bs-table-bg: #fff !important; border-color: #000 !important; }
            .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
            a { text-decoration: none; color: #000 !important; }
        }
    </style>
@endsection
