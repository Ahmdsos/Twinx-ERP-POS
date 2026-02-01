@extends('layouts.app')

@section('title', 'تاريخ الائتمان: ' . $customer->name)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline-light rounded-circle p-2"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h3 class="fw-bold text-white mb-0">تاريخ الائتمان والفواتير</h3>
                    <p class="text-gray-400 mb-0 small">{{ $customer->name }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card bg-slate-800 border-slate-700 h-100">
                    <div class="card-body">
                        <small class="text-gray-400">إجمالي المديونية</small>
                        <h4 class="text-white fw-bold mb-0 mt-2">{{ number_format($stats->total_balance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-slate-800 border-slate-700 h-100">
                    <div class="card-body">
                        <small class="text-gray-400">حد الائتمان</small>
                        <h4 class="text-white fw-bold mb-0 mt-2">{{ number_format($customer->credit_limit, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-slate-800 border-slate-700 h-100">
                    <div class="card-body">
                        <small class="text-gray-400">فواتير متأخرة</small>
                        <h4 class="text-danger fw-bold mb-0 mt-2">{{ $stats->overdue_count }}</h4>
                        <small class="text-danger opacity-75">{{ number_format($stats->overdue_amount, 2) }} ج.م</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-slate-800 border-slate-700 h-100">
                    <div class="card-body">
                        <small class="text-gray-400">نسبة السداد</small>
                        @php $ratio = $stats->total_amount > 0 ? ($stats->total_paid / $stats->total_amount) * 100 : 0; @endphp
                        <h4 class="text-success fw-bold mb-0 mt-2">{{ number_format($ratio, 1) }}%</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="card bg-slate-900 border-slate-800">
            <div class="card-header bg-transparent border-slate-800">
                <h5 class="text-white mb-0">سجل الفواتير</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="p-3">رقم الفاتورة</th>
                            <th class="p-3">التاريخ</th>
                            <th class="p-3">تاريخ الاستحقاق</th>
                            <th class="p-3 text-end">القيمة</th>
                            <th class="p-3 text-end">المدفوع</th>
                            <th class="p-3 text-end">المتبقي</th>
                            <th class="p-3 text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr class="{{ $inv->is_overdue ? 'bg-red-500 bg-opacity-5' : '' }}">
                                <td class="p-3 font-monospace">{{ $inv->invoice_number }}</td>
                                <td class="p-3">{{ \Carbon\Carbon::parse($inv->date)->format('Y-m-d') }}</td>
                                <td class="p-3 {{ $inv->is_overdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : '-' }}
                                    @if($inv->is_overdue) <small class="d-block">متأخر</small> @endif
                                </td>
                                <td class="p-3 text-end">{{ number_format($inv->total, 2) }}</td>
                                <td class="p-3 text-end">{{ number_format($inv->paid, 2) }}</td>
                                <td class="p-3 text-end fw-bold">{{ number_format($inv->balance, 2) }}</td>
                                <td class="p-3 text-center">
                                    @if($inv->balance == 0)
                                        <span class="badge bg-green-500 bg-opacity-20 text-green-300">خالصة</span>
                                    @elseif($inv->paid > 0)
                                        <span class="badge bg-orange-500 bg-opacity-20 text-orange-300">جزئي</span>
                                    @else
                                        <span class="badge bg-red-500 bg-opacity-20 text-red-300">غير مدفوع</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-gray-500">لا توجد فواتير</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection