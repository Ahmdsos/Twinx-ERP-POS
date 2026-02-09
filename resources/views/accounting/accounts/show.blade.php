@extends('layouts.app')

@section('title', 'كشف حساب: ' . $account->display_name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-white mb-1">كشف حساب <span class="text-info">{{ $account->display_name }}</span></h4>
            <div class="text-white-50 small font-monospace">Code: {{ $account->code }} | Type: {{ $account->type->label() }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.index') }}" class="btn btn-glass-outline">عودة للقائمة</a>
            <button onclick="window.print()" class="btn btn-glass-outline">
                <i class="bi bi-printer me-2"></i> طباعة
            </button>
        </div>
    </div>

    <!-- Date Filter Form -->
    <div class="glass-card p-3 mb-4">
        <form action="{{ route('accounts.show', $account) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="text-white-50 small mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="form-control bg-transparent text-white border-secondary">
            </div>
            <div class="col-md-4">
                <label class="text-white-50 small mb-1">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="form-control bg-transparent text-white border-secondary">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">تحديث</button>
            </div>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle text-white mb-0 custom-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-white-50 fw-normal">التاريخ</th>
                        <th class="py-3 text-white-50 fw-normal">رقم القيد</th>
                        <th class="py-3 text-white-50 fw-normal">البيان / الوصف</th>
                        <th class="py-3 text-white-50 fw-normal text-end">مدين (Debit)</th>
                        <th class="py-3 text-white-50 fw-normal text-end">دائن (Credit)</th>
                        <th class="px-4 py-3 text-white-50 fw-normal text-end">الرصيد (Balance)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance -->
                    <tr class="bg-white bg-opacity-5">
                        <td colspan="3" class="px-4 py-3 fw-bold text-info">رصيد افتتاحي (قبل {{ $startDate }})</td>
                        <td class="text-end text-white-50">-</td>
                        <td class="text-end text-white-50">-</td>
                        <td class="px-4 text-end fw-bold fs-5">{{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @forelse($ledgerEntries as $entry)
                        <tr class="table-row-hover">
                            <td class="px-4 py-3">{{ $entry['date']->format('Y-m-d') }}</td>
                            <td class="py-3">
                                <a href="{{ route('journal-entries.show', $entry['journal_entry_id']) }}"
                                    class="text-info text-decoration-none font-monospace">
                                    #{{ $entry['entry_number'] }}
                                </a>
                            </td>
                            <td class="py-3 text-white-50 small">{{ Str::limit($entry['description'], 60) }}</td>
                            <td
                                class="py-3 text-end font-monospace {{ $entry['debit'] > 0 ? 'text-white' : 'text-white-50 opacity-25' }}">
                                {{ number_format($entry['debit'], 2) }}
                            </td>
                            <td
                                class="py-3 text-end font-monospace {{ $entry['credit'] > 0 ? 'text-white' : 'text-white-50 opacity-25' }}">
                                {{ number_format($entry['credit'], 2) }}
                            </td>
                            <td
                                class="px-4 text-end fw-bold font-monospace {{ $entry['balance'] < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($entry['balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-white-50">لا توجد حركات خلال هذه الفترة</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-white bg-opacity-10 border-top border-white border-opacity-20">
                    <tr>
                        <td colspan="3" class="px-4 py-4 fw-bold text-white fs-5">الإجمالي (الرصيد الختامي)</td>
                        <td class="py-4 text-end fw-bold text-info fs-5">{{ number_format($totalDebit, 2) }}</td>
                        <td class="py-4 text-end fw-bold text-info fs-5">{{ number_format($totalCredit, 2) }}</td>
                        <td class="px-4 py-4 text-end fw-bold text-warning fs-4 font-monospace">
                            {{ number_format($closingBalance, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .custom-table thead th {
            background-color: rgba(255, 255, 255, 0.03);
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-row-hover:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .table-row-hover td {
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
    </style>
@endsection