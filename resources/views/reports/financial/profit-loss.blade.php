@extends('layouts.app')

@section('title', 'قائمة الدخل - Profit & Loss')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1">
                    <i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>
                    قائمة الدخل
                    <span class="fs-6 text-white-50 ms-2">(Profit & Loss)</span>
                </h4>
                <div class="text-white-50 small">
                    الفترة من <span class="text-white fw-bold">{{ $startDate }}</span> إلى <span
                        class="text-white fw-bold">{{ $endDate }}</span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <form action="{{ route('reports.financial.pl') }}" method="GET" class="d-flex gap-2 glass-card p-1 rounded">
                    <input type="hidden" name="type" value="pl">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="form-control form-control-sm bg-transparent text-white border-0">
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="form-control form-control-sm bg-transparent text-white border-0">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">تحديث</button>
                </form>
                <button class="btn btn-sm btn-outline-light glass-hover" onclick="window.print()">
                    <i class="bi bi-printer"></i>
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
                        <div class="text-white-50 small text-uppercase fw-bold mb-2">إجمالي الإيرادات</div>
                        <h3 class="fw-bold text-success mb-0 text-shadow">{{ number_format($data['revenue']['total'], 2) }}
                            <small class="fs-6 text-white-50">ج.م</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 position-relative overflow-hidden">
                    <div class="position-absolute end-0 top-0 p-3 opacity-10">
                        <i class="bi bi-graph-down-arrow display-4 text-danger"></i>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-white-50 small text-uppercase fw-bold mb-2">إجمالي المصروفات</div>
                        <h3 class="fw-bold text-danger mb-0 text-shadow">{{ number_format($data['expenses']['total'], 2) }}
                            <small class="fs-6 text-white-50">ج.م</small></h3>
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
                        <div class="text-white-50 small text-uppercase fw-bold mb-2">صافي الربح / الخسارة</div>
                        <h3
                            class="fw-bold {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-0 text-shadow">
                            {{ number_format($data['net_profit'], 2) }} <small class="fs-6 text-white-50">ج.م</small>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Report -->
        <div class="card glass-card border-0">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                <h6 class="fw-bold text-white m-0"><i class="bi bi-list-ul me-2"></i> تفاصيل الحسابات</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-white-50 small text-uppercase">
                                <th class="py-3 ps-4" style="width: 60%">الحساب</th>
                                <th class="py-3 text-end pe-4">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Revenue Section -->
                            <tr class="bg-success bg-opacity-10">
                                <td class="ps-4 fw-bold text-success py-2" colspan="2">
                                    <i class="bi bi-arrow-up-circle me-2"></i> الإيرادات (Revenues)
                                </td>
                            </tr>
                            @forelse($data['revenue']['details'] as $account)
                                <tr>
                                    <td class="ps-5 border-start border-success border-opacity-25 border-3">
                                        <span class="fw-medium text-white">{{ $account->name }}</span>
                                        <span
                                            class="badge bg-secondary bg-opacity-25 text-white-50 ms-2 fw-normal">{{ $account->code }}</span>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-white">
                                        {{ number_format($account->period_balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-white-50 py-3 small">لا توجد إيرادات في هذه الفترة
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Expense Section -->
                            <tr class="bg-danger bg-opacity-10">
                                <td class="ps-4 fw-bold text-danger py-2 mt-3" colspan="2">
                                    <i class="bi bi-arrow-down-circle me-2"></i> المصروفات (Expenses)
                                </td>
                            </tr>
                            @forelse($data['expenses']['details'] as $account)
                                <tr>
                                    <td class="ps-5 border-start border-danger border-opacity-25 border-3">
                                        <span class="fw-medium text-white">{{ $account->name }}</span>
                                        <span
                                            class="badge bg-secondary bg-opacity-25 text-white-50 ms-2 fw-normal">{{ $account->code }}</span>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-white">
                                        {{ number_format($account->period_balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-white-50 py-3 small">لا توجد مصروفات في هذه الفترة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-white bg-opacity-5 border-top border-secondary border-opacity-25">
                            <tr>
                                <td class="ps-4 py-3 fw-bold fs-5 text-white">صافي النتيجة</td>
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
        .glass-card {
            background: rgba(30, 30, 40, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }

        .table-transparent {
            --bs-table-bg: transparent;
            --bs-table-color: #e0e0e0;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03);
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
@endsection