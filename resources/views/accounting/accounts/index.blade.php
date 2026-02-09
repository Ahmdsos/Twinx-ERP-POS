@extends('layouts.app')

@section('title', 'دليل الحسابات')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-white mb-1">دليل الحسابات</h4>
            <div class="text-white-50 small">عرض قائمة الحسابات</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.tree') }}" class="btn btn-glass-outline">
                <i class="bi bi-diagram-3 me-1"></i> عرض الشجرة
            </a>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary shadow-lg fw-bold px-4 py-2">
                <i class="bi bi-plus-lg me-1"></i> حساب جديد
            </a>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle text-white mb-0 custom-table">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-white-50 fw-normal">رقم الحساب</th>
                        <th class="py-4 text-white-50 fw-normal">اسم الحساب</th>
                        <th class="py-4 text-white-50 fw-normal">النوع</th>
                        <th class="py-4 text-white-50 fw-normal">طبيعة الحساب</th>
                        <th class="py-4 text-white-50 fw-normal">الرصيد الحالي</th>
                        <th class="py-4 text-center text-white-50 fw-normal">الحالة</th>
                        <th class="px-4 py-4 text-end text-white-50 fw-normal">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr class="table-row-hover">
                            <td class="px-4 py-3 font-monospace text-info fs-5">{{ $account->code }}</td>
                            <td class="py-3">
                                <div class="fw-bold fs-5">{{ $account->display_name }}</div>
                                @if($account->parent)
                                    <div class="small text-white-50">يندرج تحت:
                                        {{ $account->parent->display_name ?? $account->parent->name }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span
                                    class="badge bg-white bg-opacity-10 text-white fw-normal px-3 py-1 rounded-pill border border-white border-opacity-10">
                                    {{ $account->type->label() }}
                                </span>
                            </td>
                            <td class="py-3 text-white-50 small">
                                {{ $account->type->debitIncreases() ? 'مدين (Debit)' : 'دائن (Credit)' }}
                            </td>
                            <td class="py-3 fw-bold fs-5 {{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($account->balance, 2) }}
                            </td>
                            <td class="text-center py-3">
                                @if($account->is_active)
                                    <div
                                        class="d-inline-flex align-items-center text-success bg-success bg-opacity-10 px-3 py-1 rounded-pill border border-success border-opacity-10">
                                        <span class="small fw-bold">نشط</span>
                                    </div>
                                @else
                                    <div
                                        class="d-inline-flex align-items-center text-danger bg-danger bg-opacity-10 px-3 py-1 rounded-pill border border-danger border-opacity-10">
                                        <span class="small fw-bold">موقف</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 text-end py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('accounts.show', $account) }}"
                                        class="btn btn-sm btn-glass text-info shadow-sm" title="كشف حساب">
                                        <i class="bi bi-eye fs-6"></i>
                                    </a>
                                    <a href="{{ route('accounts.edit', $account) }}"
                                        class="btn btn-sm btn-glass text-warning shadow-sm" title="تعديل">
                                        <i class="bi bi-pencil fs-6"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center py-5 opacity-50">
                                    <i class="bi bi-safe2 display-1 mb-4"></i>
                                    <h4 class="text-white-50">لا توجد حسابات</h4>
                                    <a href="{{ route('accounts.create') }}"
                                        class="btn btn-outline-light px-4 py-2 rounded-pill">إضافة حساب جديد</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent border-top border-white border-opacity-10 py-4">
            {{ $accounts->links('partials.pagination') }}
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-row-hover td {
            border: none;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
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

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.2s;
        }

        .btn-glass-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }
    </style>
@endsection