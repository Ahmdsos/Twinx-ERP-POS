@extends('layouts.app')

@section('title', __('Expenses'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-heading mb-1">{{ __('Expenses') }}</h4>
            <div class="text-muted small">إدارة وتسجيل المصروفات اليومية</div>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary shadow-lg fw-bold px-4 py-2">
            <i class="bi bi-plus-lg me-1"></i> تسجيل مصروف
        </a>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle text-body mb-0 custom-table">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-secondary-50 fw-normal">رقم المرجع</th>
                        <th class="py-4 text-secondary-50 fw-normal">{{ __('Date') }}</th>
                        <th class="py-4 text-secondary-50 fw-normal">البند / التصنيف</th>
                        <th class="py-4 text-secondary-50 fw-normal">المستفيد</th>
                        <th class="py-4 text-secondary-50 fw-normal">حساب الدفع</th>
                        <th class="py-4 text-secondary-50 fw-normal text-end">{{ __('Amount') }}</th>
                        <th class="px-4 py-4 text-secondary-50 fw-normal text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr class="table-row-hover">
                            <td class="px-4 py-3 font-monospace text-info fs-5">{{ $expense->reference_number }}</td>
                            <td class="py-3 fs-6">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="py-3">
                                <div class="fw-bold fs-5 mb-1">{{ $expense->category->name }}</div>
                                @if($expense->notes)
                                    <div class="small text-muted" style="max-width: 300px; line-height: 1.4;">
                                        {{ Str::limit($expense->notes, 50) }}</div>
                                @endif
                            </td>
                            <td class="py-3 fs-6">{{ $expense->payee ?? '-' }}</td>
                            <td class="py-3">
                                <span
                                    class="badge bg-surface bg-opacity-10 text-body fw-normal px-3 py-2 rounded-pill border border-secondary border-opacity-10 border-opacity-10">
                                    {{ $expense->paymentAccount->name }}
                                </span>
                            </td>
                            <td class="text-end fw-bold fs-5 text-warning">{{ number_format($expense->total_amount, 2) }}</td>
                            <td class="px-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                     <!-- View/Edit -->
                                    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-glass text-info" title="التفاصيل">
                                        <i class="bi bi-eye fs-6"></i>
                                    </a>
                                    <!-- Print/Journal -->
                                    @if($expense->journal_entry_id)
                                    <a href="{{ route('journal-entries.show', $expense->journal_entry_id) }}" class="btn btn-sm btn-glass text-warning" title="القيد المحاسبي">
                                        <i class="bi bi-receipt fs-6"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center py-5 opacity-50">
                                    <i class="bi bi-wallet2 display-1 mb-4"></i>
                                    <h4 class="text-heading-50">لا توجد مصروفات مسجلة حتى الآن</h4>
                                    <p class="mb-4">ابدأ بتسجيل أول مصروف للنظام</p>
                                    <a href="{{ route('expenses.create') }}"
                                        class="btn btn-outline-light px-4 py-2 rounded-pill">تسجيل مصروف جديد</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 border-opacity-10 py-4">
            {{ $expenses->links('partials.pagination') }}
        </div>
    </div>

    <style>
        

        .custom-table thead th {
            background-color: rgba(255, 255, 255, 0.03);
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-row-hover {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .table-row-hover:hover {
            background-color: var(--table-head-bg);
            /* Highlight on hover */
            transform: translateY(-1px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            /* Subtle lift */
        }

        .table-row-hover td {
            border: none;
        }

        .btn-glass {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            border-radius: 8px;
            /* Softer corners */
            width: 36px;
            height: 36px;
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