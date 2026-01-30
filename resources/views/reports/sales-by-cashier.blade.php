@extends('layouts.app')

@section('title', 'تقرير المبيعات حسب الكاشير')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير المبيعات حسب الكاشير</h1>
                <p class="text-muted mb-0">Sales by Cashier Report</p>
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

        <!-- Sales by Cashier Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">أداء الكاشيرين</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الكاشير</th>
                            <th class="text-center">عدد الفواتير</th>
                            <th class="text-end">إجمالي المبيعات</th>
                            <th class="text-center">النسبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalSales = $data->sum('total'); @endphp
                        @forelse($data as $index => $cashier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="bi bi-person-circle me-2"></i>
                                    {{ $cashier->cashier }}
                                </td>
                                <td class="text-center">{{ $cashier->invoices }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($cashier->total, 2) }}</td>
                                <td class="text-center">
                                    @php $pct = $totalSales > 0 ? ($cashier->total / $totalSales) * 100 : 0; @endphp
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small>{{ number_format($pct, 1) }}%</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td colspan="2">الإجمالي</td>
                            <td class="text-center">{{ $data->sum('invoices') }}</td>
                            <td class="text-end">{{ number_format($totalSales, 2) }}</td>
                            <td class="text-center">100%</td>
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