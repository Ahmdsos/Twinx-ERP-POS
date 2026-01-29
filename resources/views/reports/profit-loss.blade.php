@extends('layouts.app')

@section('title', 'قائمة الدخل')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">قائمة الدخل</h1>
                <p class="text-muted mb-0">Profit & Loss Statement</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>
                طباعة
            </button>
        </div>

        <!-- Date Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Revenue Section -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-up-circle me-2"></i>الإيرادات</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>الحساب</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenues as $account)
                                    <tr>
                                        <td>
                                            <code class="me-2">{{ $account['code'] }}</code>
                                            {{ $account['name'] }}
                                        </td>
                                        <td class="text-end text-success fw-bold">
                                            {{ number_format($account['balance'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">لا توجد إيرادات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-success">
                                <tr class="fw-bold">
                                    <td>إجمالي الإيرادات</td>
                                    <td class="text-end">{{ number_format($totalRevenue, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expenses Section -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-down-circle me-2"></i>المصروفات</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>الحساب</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $account)
                                    <tr>
                                        <td>
                                            <code class="me-2">{{ $account['code'] }}</code>
                                            {{ $account['name'] }}
                                        </td>
                                        <td class="text-end text-danger fw-bold">
                                            {{ number_format($account['balance'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">لا توجد مصروفات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-danger">
                                <tr class="fw-bold">
                                    <td>إجمالي المصروفات</td>
                                    <td class="text-end">{{ number_format($totalExpenses, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit/Loss -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <h5 class="text-muted mb-3">صافي الربح / الخسارة</h5>
                <h1 class="display-4 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format(abs($netProfit), 2) }}
                    <small class="fs-4">ج.م</small>
                </h1>
                @if($netProfit >= 0)
                    <span class="badge bg-success fs-6"><i class="bi bi-graph-up me-1"></i>ربح</span>
                @else
                    <span class="badge bg-danger fs-6"><i class="bi bi-graph-down me-1"></i>خسارة</span>
                @endif
                <p class="text-muted mt-3 mb-0">
                    للفترة من {{ $startDate->format('Y-m-d') }} إلى {{ $endDate->format('Y-m-d') }}
                </p>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {

                .btn,
                form,
                .sidebar,
                .navbar {
                    display: none !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endpush
@endsection