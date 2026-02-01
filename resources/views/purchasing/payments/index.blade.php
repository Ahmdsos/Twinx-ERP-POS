@extends('layouts.app')

@section('title', 'دفعات الموردين')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-purple shadow-neon-purple">
                    <i class="bi bi-cash-stack fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">دفعات الموردين</h2>
                    <p class="mb-0 text-gray-400 small">سجل المدفوعات الصادرة</p>
                </div>
            </div>
            <a href="{{ route('supplier-payments.create') }}"
                class="btn btn-action-purple d-flex align-items-center gap-2 shadow-lg">
                <i class="bi bi-plus-lg"></i>
                <span class="fw-bold">تسجيل دفعة جديدة</span>
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-purple-400 x-small fw-bold text-uppercase tracking-wide">مدفوعات اليوم</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['today'], 2) }} <small
                                    class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-purple-500 bg-opacity-10 text-purple-400">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-cyan-400 x-small fw-bold text-uppercase tracking-wide">مدفوعات الشهر
                                الحالي</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['this_month'], 2) }} <small
                                    class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-cyan-500 bg-opacity-10 text-cyan-400">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-white-5 rounded-4 p-4 mb-5">
            <form action="{{ route('supplier-payments.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">بحث</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-purple"
                            value="{{ request('search') }}" placeholder="رقم السند...">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-purple-400 x-small fw-bold text-uppercase ps-1">المورد</label>
                    <select name="supplier_id"
                        class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-purple-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i> تصفية
                    </button>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-purple">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">رقم السند</th>
                            <th>التاريخ</th>
                            <th>المورد</th>
                            <th>الحساب المخصوم منه</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 font-monospace text-purple-300">
                                    <a href="{{ route('supplier-payments.show', $payment->id) }}"
                                        class="text-decoration-none text-purple-300 hover-text-white">
                                        {{ $payment->payment_number }}
                                    </a>
                                </td>
                                <td class="text-gray-400 x-small">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td class="fw-bold text-white">{{ $payment->supplier->name }}</td>
                                <td class="text-gray-400 x-small">{{ $payment->paymentAccount->name ?? '-' }}</td>
                                <td class="fw-bold text-white fs-6">{{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    @if($payment->payment_method == 'cash')
                                        <span class="badge bg-green-500 bg-opacity-10 text-green-400">نقدي</span>
                                    @elseif($payment->payment_method == 'bank_transfer')
                                        <span class="badge bg-blue-500 bg-opacity-10 text-blue-400">تحويل بنكي</span>
                                    @else
                                        <span class="badge bg-orange-500 bg-opacity-10 text-orange-400">شيك</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('supplier-payments.show', $payment->id) }}" class="btn-icon-glass"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('supplier-payments.print', $payment->id) }}" class="btn-icon-glass"
                                            title="طباعة" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-wallet2 fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">لا توجد مدفوعات</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-top border-white-5">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Styles (Purple Theme) -->
    <style>
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-gradient-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
        }

        .shadow-neon-purple {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.4);
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-purple-300 {
            color: #d8b4fe !important;
        }

        .bg-purple-500 {
            background: #a855f7 !important;
        }

        .border-top-gradient-purple {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #8b5cf6, #c084fc) 1;
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
        }

        .btn-purple-glass {
            background: rgba(139, 92, 246, 0.15);
            color: #c084fc;
            border: 1px solid rgba(192, 132, 252, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-purple-glass:hover {
            background: rgba(139, 92, 246, 0.25);
            color: white;
            border-color: #c084fc;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .focus-ring-purple:focus {
            border-color: #c084fc !important;
            box-shadow: 0 0 0 4px rgba(192, 132, 252, 0.1) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: #94a3b8;
            font-weight: 600;
            padding: 1rem;
        }

        .table-dark-custom td {
            padding: 1rem;
        }

        .group-hover-actions:hover .group-hover-visible {
            opacity: 1 !important;
        }

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            transition: 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
    </style>
@endsection