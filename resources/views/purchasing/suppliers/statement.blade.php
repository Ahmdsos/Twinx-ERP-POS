@extends('layouts.app')

@section('title', 'كشف حساب ' . $supplier->name . ' - Twinx ERP')
@section('page-title', 'كشف حساب المورد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">الموردين</a></li>
    <li class="breadcrumb-item"><a href="{{ route('suppliers.show', $supplier) }}">{{ $supplier->name }}</a></li>
    <li class="breadcrumb-item active">كشف الحساب</li>
@endsection

@push('styles')
    <style>
        @media print {

            .btn,
            form,
            .sidebar,
            .navbar,
            .breadcrumb {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .print-header {
                display: block !important;
            }
        }

        .print-header {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <div>
                <h1 class="h3 mb-0">كشف حساب: {{ $supplier->name }}</h1>
                <p class="text-muted mb-0">{{ $supplier->code }}</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>
                طباعة
            </button>
        </div>

        <!-- Print Header (only visible when printing) -->
        <div class="print-header text-center mb-4">
            <h2>كشف حساب المورد</h2>
            <h4>{{ $supplier->name }} ({{ $supplier->code }})</h4>
            <p>الفترة من: {{ $startDate }} إلى: {{ $endDate }}</p>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4 d-print-none">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>كشف الحساب</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>التاريخ</th>
                                <th>المرجع</th>
                                <th>البيان</th>
                                <th class="text-end">مدين (مستحق للمورد)</th>
                                <th class="text-end">دائن (مدفوع)</th>
                                <th class="text-end">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Opening Balance -->
                            <tr class="table-secondary">
                                <td>{{ $startDate }}</td>
                                <td>-</td>
                                <td><strong>رصيد أول المدة</strong></td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end"><strong>{{ number_format($openingBalance, 2) }}</strong></td>
                            </tr>

                            @forelse($transactions as $tx)
                                <tr>
                                    <td>{{ is_object($tx['date']) ? $tx['date']->format('Y-m-d') : $tx['date'] }}</td>
                                    <td>{{ $tx['reference'] }}</td>
                                    <td>{{ $tx['description'] }}</td>
                                    <td class="text-end {{ $tx['debit'] > 0 ? 'text-danger' : '' }}">
                                        {{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '-' }}
                                    </td>
                                    <td class="text-end {{ $tx['credit'] > 0 ? 'text-success' : '' }}">
                                        {{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '-' }}
                                    </td>
                                    <td class="text-end">{{ number_format($tx['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد معاملات في هذه الفترة
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Closing Balance -->
                            <tr class="table-primary">
                                <td>{{ $endDate }}</td>
                                <td>-</td>
                                <td><strong>رصيد آخر المدة</strong></td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end"><strong>{{ number_format($closingBalance, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">إجمالي المستحق للمورد</h6>
                        <h4 class="text-danger mb-0">{{ number_format($transactions->sum('debit'), 2) }} ج.م</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">إجمالي المدفوع</h6>
                        <h4 class="text-success mb-0">{{ number_format($transactions->sum('credit'), 2) }} ج.م</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-{{ $closingBalance > 0 ? 'danger' : 'success' }} text-white">
                    <div class="card-body text-center">
                        <h6 class="opacity-75 mb-1">الرصيد الختامي</h6>
                        <h4 class="mb-0">{{ number_format($closingBalance, 2) }} ج.م</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection