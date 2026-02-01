@extends('layouts.app')

@section('title', 'حركات الخزينة والبنوك')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-white mb-1">الخزينة والبنوك</h4>
            <div class="text-white-50 small">سندات الصرف والقبض</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('treasury.create-receipt') }}" class="btn btn-success shadow-lg fw-bold px-4 py-2">
                <i class="bi bi-arrow-down-circle me-1"></i> سند قبض (Cash In)
            </a>
            <a href="{{ route('treasury.create-payment') }}" class="btn btn-danger shadow-lg fw-bold px-4 py-2">
                <i class="bi bi-arrow-up-circle me-1"></i> سند صرف (Cash Out)
            </a>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle text-white mb-0 custom-table">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-white-50 fw-normal">رقم السند</th>
                        <th class="py-4 text-white-50 fw-normal">التاريخ</th>
                        <th class="py-4 text-white-50 fw-normal">النوع</th>
                        <th class="py-4 text-white-50 fw-normal">الخزينة / البنك</th>
                        <th class="py-4 text-white-50 fw-normal">الحساب المقابل</th>
                        <th class="py-4 text-white-50 fw-normal text-end">المبلغ</th>
                        <th class="px-4 py-4 text-end text-white-50 fw-normal">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="table-row-hover">
                            <td class="px-4 py-3 font-monospace text-info">#{{ $transaction->id }}</td>
                            <td class="py-3">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-3">
                                @if($transaction->type == 'receipt')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">
                                        <i class="bi bi-arrow-down me-1"></i> قبض
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">
                                        <i class="bi bi-arrow-up me-1"></i> صرف
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 fw-bold">{{ $transaction->treasuryAccount->name }}</td>
                            <td class="py-3">{{ $transaction->counterAccount->name }}</td>
                            <td
                                class="text-end fw-bold font-monospace fs-5 {{ $transaction->type == 'receipt' ? 'text-success' : 'text-danger' }}">
                                {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-4 text-end py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('treasury.show', $transaction) }}"
                                        class="btn btn-sm btn-glass text-info shadow-sm" title="التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('journal-entries.show', $transaction->journal_entry_id) }}"
                                        class="btn btn-sm btn-glass text-warning shadow-sm" title="القيد المحاسبي">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center py-5 opacity-50">
                                    <i class="bi bi-wallet2 display-1 mb-4"></i>
                                    <h4 class="text-white-50">لا توجد حركات</h4>
                                    <div class="d-flex gap-2 mt-3">
                                        <a href="{{ route('treasury.create-receipt') }}"
                                            class="btn btn-outline-success rounded-pill px-4">سند قبض جديد</a>
                                        <a href="{{ route('treasury.create-payment') }}"
                                            class="btn btn-outline-danger rounded-pill px-4">سند صرف جديد</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent border-top border-white border-opacity-10 py-4">
            {{ $transactions->links('partials.pagination') }}
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
            min-height: 400px;
        }

        .custom-table thead th {
            background-color: rgba(255, 255, 255, 0.03);
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-row-hover {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .table-row-hover:hover {
            background-color: rgba(255, 255, 255, 0.05);
            transform: translateY(-1px);
        }

        .table-row-hover td {
            border: none;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
    </style>
@endsection