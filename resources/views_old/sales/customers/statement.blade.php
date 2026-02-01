@extends('layouts.app')

@section('title', 'كشف حساب ' . $customer->name . ' - Twinx ERP')
@section('page-title', 'كشف حساب العميل')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></li>
    <li class="breadcrumb-item active">كشف الحساب</li>
@endsection

@section('content')
    <!-- Customer Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-0">{{ $customer->code }} | {{ $customer->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-inline-block text-end">
                        <small class="text-muted d-block">الرصيد الحالي</small>
                        <h3 class="mb-0 {{ ($balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($balance ?? 0, 2) }} ج.م
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('customers.statement', $customer) }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date"
                        value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" name="to_date"
                        value="{{ request('to_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>عرض
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="#" class="btn btn-outline-secondary w-100" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>طباعة
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statement Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>حركات الحساب</h5>
            <span class="badge bg-secondary">{{ count($transactions ?? []) }} حركة</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>المستند</th>
                            <th>البيان</th>
                            <th class="text-success">مدين (له)</th>
                            <th class="text-danger">دائن (عليه)</th>
                            <th>الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance Row -->
                        <tr class="table-secondary">
                            <td>{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}</td>
                            <td>-</td>
                            <td><strong>رصيد أول المدة</strong></td>
                            <td>-</td>
                            <td>-</td>
                            <td><strong>{{ number_format($openingBalance ?? 0, 2) }}</strong></td>
                        </tr>

                        @php $runningBalance = $openingBalance ?? 0; @endphp

                        @forelse($transactions ?? [] as $transaction)
                            @php
                                if ($transaction->type === 'invoice') {
                                    $runningBalance += $transaction->amount;
                                } else {
                                    $runningBalance -= $transaction->amount;
                                }
                            @endphp
                            <tr>
                                <td>{{ $transaction->date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="#">{{ $transaction->reference }}</a>
                                </td>
                                <td>{{ $transaction->description }}</td>
                                <td class="text-success">
                                    @if($transaction->type === 'payment')
                                        {{ number_format($transaction->amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-danger">
                                    @if($transaction->type === 'invoice')
                                        {{ number_format($transaction->amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="{{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    <strong>{{ number_format($runningBalance, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد حركات في هذه الفترة
                                </td>
                            </tr>
                        @endforelse

                        <!-- Closing Balance Row -->
                        <tr class="table-primary">
                            <td>{{ request('to_date', now()->format('Y-m-d')) }}</td>
                            <td>-</td>
                            <td><strong>رصيد آخر المدة</strong></td>
                            <td>-</td>
                            <td>-</td>
                            <td><strong
                                    class="{{ ($balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balance ?? 0, 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">إجمالي الفواتير</h6>
                    <h4 class="text-danger mb-0">{{ number_format($totalInvoices ?? 0, 2) }} ج.م</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">إجمالي المدفوعات</h6>
                    <h4 class="text-success mb-0">{{ number_format($totalPayments ?? 0, 2) }} ج.م</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">صافي الرصيد</h6>
                    <h4 class="{{ ($balance ?? 0) > 0 ? 'text-danger' : 'text-success' }} mb-0">
                        {{ number_format($balance ?? 0, 2) }} ج.م</h4>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media print {

            .sidebar,
            .navbar,
            .breadcrumb,
            form,
            .btn {
                display: none !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush