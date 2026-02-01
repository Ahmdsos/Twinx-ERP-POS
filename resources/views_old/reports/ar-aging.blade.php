@extends('layouts.app')

@section('title', 'تقرير أعمار ديون العملاء')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير أعمار ديون العملاء</h1>
                <p class="text-muted mb-0">AR Aging Report</p>
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
                        <label class="form-label">حتى تاريخ</label>
                        <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate->format('Y-m-d') }}">
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

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-success text-white">
                    <div class="card-body text-center">
                        <div class="text-uppercase small opacity-75">جاري (0-30 يوم)</div>
                        <h3 class="mb-0">{{ number_format($totals['current'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 bg-warning text-dark">
                    <div class="card-body text-center">
                        <div class="text-uppercase small">31-60 يوم</div>
                        <h4 class="mb-0">{{ number_format($totals['days_31_60'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 bg-orange text-white" style="background-color: #fd7e14;">
                    <div class="card-body text-center">
                        <div class="text-uppercase small">61-90 يوم</div>
                        <h4 class="mb-0">{{ number_format($totals['days_61_90'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 bg-danger text-white">
                    <div class="card-body text-center">
                        <div class="text-uppercase small">+90 يوم</div>
                        <h4 class="mb-0">{{ number_format($totals['over_90'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-dark text-white">
                    <div class="card-body text-center">
                        <div class="text-uppercase small">إجمالي المديونية</div>
                        <h3 class="mb-0">{{ number_format($totals['total'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aging Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">تفصيل الديون حسب العميل</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>كود العميل</th>
                            <th>اسم العميل</th>
                            <th class="text-end bg-success">0-30 يوم</th>
                            <th class="text-end bg-warning text-dark">31-60 يوم</th>
                            <th class="text-end" style="background-color: #fd7e14; color: white;">61-90 يوم</th>
                            <th class="text-end bg-danger">+90 يوم</th>
                            <th class="text-end">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agingData as $row)
                            <tr>
                                <td><code>{{ $row['customer_code'] }}</code></td>
                                <td>
                                    <a href="{{ route('customers.statement', $row['customer_id']) }}"
                                        class="text-decoration-none">
                                        {{ $row['customer_name'] }}
                                    </a>
                                </td>
                                <td class="text-end">{{ number_format($row['buckets']['current'], 2) }}</td>
                                <td class="text-end">{{ number_format($row['buckets']['days_31_60'], 2) }}</td>
                                <td class="text-end">{{ number_format($row['buckets']['days_61_90'], 2) }}</td>
                                <td class="text-end text-danger fw-bold">
                                    {{ $row['buckets']['over_90'] > 0 ? number_format($row['buckets']['over_90'], 2) : '-' }}
                                </td>
                                <td class="text-end fw-bold">{{ number_format($row['buckets']['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                                    لا توجد ديون مستحقة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">الإجمالي:</td>
                            <td class="text-end">{{ number_format($totals['current'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['days_31_60'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['days_61_90'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['over_90'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
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