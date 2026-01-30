@extends('layouts.app')

@section('title', 'تقرير المبيعات اليومية')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير المبيعات اليومية</h1>
                <p class="text-muted mb-0">Daily Sales Report</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>
                    طباعة
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
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
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">عدد الفواتير</h6>
                        <h3 class="text-primary">{{ $summary['total_invoices'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">إجمالي المبيعات</h6>
                        <h3 class="text-success">{{ number_format($summary['total_sales'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">إجمالي المحصل</h6>
                        <h3 class="text-info">{{ number_format($summary['total_paid'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted">متوسط اليوم</h6>
                        <h3 class="text-warning">{{ number_format($summary['average_per_day'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">تفاصيل المبيعات اليومية</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>التاريخ</th>
                            <th class="text-center">عدد الفواتير</th>
                            <th class="text-end">الإجمالي</th>
                            <th class="text-end">المحصل</th>
                            <th class="text-center">النسبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyData as $day)
                            <tr>
                                <td>{{ $day->date }}</td>
                                <td class="text-center">{{ $day->invoices }}</td>
                                <td class="text-end fw-bold">{{ number_format($day->total, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($day->paid, 2) }}</td>
                                <td class="text-center">
                                    @php $pct = $day->total > 0 ? ($day->paid / $day->total) * 100 : 0; @endphp
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($pct, 1) }}%</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">لا توجد مبيعات في هذه الفترة</td>
                            </tr>
                        @endforelse
                    </tbody>
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