@extends('layouts.app')

@section('title', 'تحليل المشتريات - Purchase Analysis')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1">
                    <i class="bi bi-truck me-2 text-warning"></i>
                    تحليل مشتريات الموردين
                </h4>
                <div class="text-white-50 small">حجم التعاملات والمديونيات</div>
            </div>

            <form action="{{ route('reports.purchases.by-supplier') }}" method="GET"
                class="d-flex gap-2 glass-card p-1 rounded">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="form-control form-control-sm bg-transparent text-white border-0">
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="form-control form-control-sm bg-transparent text-white border-0">
                <button type="submit" class="btn btn-sm btn-warning text-dark px-3 fw-bold">تصفية</button>
            </form>
        </div>

        <!-- Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card glass-card border-0 text-center py-3">
                    <div class="text-white-50 small text-uppercase">إجمالي المشتريات</div>
                    <h3 class="fw-bold text-white mb-0 text-shadow">{{ number_format($data->sum('total_purchases'), 2) }}
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-card border-0 text-center py-3">
                    <div class="text-white-50 small text-uppercase">مستحقات الموردين</div>
                    <h3 class="fw-bold text-danger mb-0 text-shadow">{{ number_format($data->sum('total_due'), 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-white-50 small text-uppercase">
                                <th class="py-3 ps-4">المورد</th>
                                <th class="py-3 text-center">عدد الفواتير</th>
                                <th class="py-3 text-end">إجمالي الشراء</th>
                                <th class="py-3 text-end pe-4">المستحق له</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $supplier)
                                <tr>
                                    <td class="ps-4 fw-bold text-white">{{ $supplier->supplier_name }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $supplier->invoice_count }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-white">{{ number_format($supplier->total_purchases, 2) }}
                                    </td>
                                    <td class="text-end pe-4 text-danger fw-bold">
                                        {{ number_format($supplier->total_due, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-white-50">لا توجد بيانات</td>
                                </tr>
                            @endforelse
                        </tbody>
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