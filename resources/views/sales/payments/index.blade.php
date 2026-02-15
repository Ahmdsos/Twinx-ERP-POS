@extends('layouts.app')

@section('title', 'سجل التحصيلات')

@section('content')
    <div class="container-fluid p-0">

        <!-- Headers & Actions -->
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold text-heading mb-2 tracking-wide display-6">
                    <i class="bi bi-wallet2 text-success me-2"></i> سجل التحصيلات
                </h2>
                <p class="text-gray-400 mb-0 fs-5">إدارة ومتابعة دفعات العملاء والسندات المالية</p>
            </div>
            <a href="{{ route('customer-payments.create') }}"
                class="btn btn-gradient-success shadow-lg px-4 py-2 rounded-pill fs-6 fw-bold d-flex align-items-center gap-2 hover-scale">
                <i class="bi bi-plus-circle-fill fs-4"></i>
                <span>تسجيل تحصيل جديد</span>
            </a>
        </div>

        <!-- 3D Stats Cards -->
        <div class="row g-4 mb-5">
            <!-- Today's Collections -->
            <div class="col-md-4">
                <div class="glass-card stat-card h-100 position-relative border-0 shadow-lg overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="bi bi-cash-stack display-1 text-body"></i>
                    </div>
                    <div class="p-4 position-relative z-1">
                        <div
                            class="badge bg-surface/10 text-body mb-3 backdrop-blur-md border border-secondary border-opacity-10/10 px-3 py-1 rounded-pill">
                            <i class="bi bi-calendar-event me-1"></i> تحصيلات اليوم
                        </div>
                        <h1 class="text-heading fw-bold mb-1 display-5">{{ number_format($totalToday, 2) }}</h1>
                        <span class="text-success small fw-bold">
                            <i class="bi bi-arrow-up-circle-fill"></i> محدث الآن
                        </span>
                    </div>
                    <div class="progress h-1 mt-3 bg-surface/5">
                        <div class="progress-bar bg-success w-75 shadow-neon"></div>
                    </div>
                </div>
            </div>

            <!-- Month's Collections -->
            <div class="col-md-4">
                <div class="glass-card stat-card h-100 position-relative border-0 shadow-lg overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="bi bi-bank display-1 text-body"></i>
                    </div>
                    <div class="p-4 position-relative z-1">
                        <div
                            class="badge bg-surface/10 text-body mb-3 backdrop-blur-md border border-secondary border-opacity-10/10 px-3 py-1 rounded-pill">
                            <i class="bi bi-calendar-month me-1"></i> إجمالي الشهر
                        </div>
                        <h1 class="text-heading fw-bold mb-1 display-5">{{ number_format($totalMonth, 2) }}</h1>
                        <span class="text-info small fw-bold">
                            <i class="bi bi-graph-up-arrow"></i> أداء التحصيل
                        </span>
                    </div>
                    <div class="progress h-1 mt-3 bg-surface/5">
                        <div class="progress-bar bg-info w-50 shadow-neon-blue"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions / Info -->
            <div class="col-md-4">
                <div
                    class="glass-card h-100 p-4 d-flex flex-column justify-content-center align-items-center text-center border-dashed border-secondary border-opacity-10/10">
                    <div class="icon-circle bg-surface/5 mb-3 text-warning">
                        <i class="bi bi-printer-fill fs-3"></i>
                    </div>
                    <p class="text-gray-400 mb-0">يمكنك طباعة سندات القبض مباشرة من الجدول بالأسفل</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="glass-panel p-4 mb-4 rounded-4 border border-secondary border-opacity-10/10 shadow-lg position-relative overflow-hidden">
            <div
                class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-r from-blue-500/5 to-purple-500/5 pointer-events-none">
            </div>

            <form action="{{ route('customer-payments.index') }}" method="GET" class="position-relative z-1">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="text-gray-300 small fw-bold mb-2 ps-1">بحث برقم الإيصال / المرجع</label>
                        <div class="input-group glass-input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-gray-400 ps-3"><i
                                    class="bi bi-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control bg-transparent border-start-0 text-body shadow-none"
                                placeholder="بحث سريع...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-gray-300 small fw-bold mb-2 ps-1">فلتر بالعميل</label>
                        <select name="customer_id" class="form-select glass-select text-body shadow-none">
                            <option value="" class="bg-gray-900 text-gray-400">-- كل العملاء --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" class="bg-gray-900 text-body" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="text-gray-300 small fw-bold mb-2 ps-1">{{ __('Payment Method') }}</label>
                        <select name="payment_method" class="form-select glass-select text-body shadow-none">
                            <option value="" class="bg-gray-900 text-gray-400">-- الكل --</option>
                            <option value="cash" class="bg-gray-900" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقدي (Cash)</option>
                            <option value="bank_transfer" class="bg-gray-900" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                            <option value="check" class="bg-gray-900" {{ request('payment_method') == 'check' ? 'selected' : '' }}>{{ __('Check') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-gradient-primary w-100 fw-bold shadow-neon-blue py-2">
                            <i class="bi bi-funnel-fill me-2"></i> تصفية النتائج
                        </button>
                        <a href="{{ route('customer-payments.index') }}"
                            class="btn btn-glass-icon px-3 d-flex align-items-center justify-content-center"
                            title="{{ __('Reset') }}">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="glass-panel rounded-4 overflow-hidden shadow-lg">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead class="bg-surface/5">
                        <tr>
                            <th class="py-3 ps-4 text-gray-400 fw-normal">رقم الإيصال</th>
                            <th class="py-3 text-gray-400 fw-normal">{{ __('Customer') }}</th>
                            <th class="py-3 text-gray-400 fw-normal">{{ __('Date') }}</th>
                            <th class="py-3 text-gray-400 fw-normal">{{ __('Payment Method') }}</th>
                            <th class="py-3 text-gray-400 fw-normal">{{ __('Reference') }}</th>
                            <th class="py-3 text-gray-400 fw-normal text-end">{{ __('Amount') }}</th>
                            <th class="py-3 pe-4 text-gray-400 fw-normal text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="hover-bg-surface-5">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-square-sm bg-success/20 text-success rounded-2">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <span class="font-monospace fw-bold text-body">{{ $payment->payment_number }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-body">{{ $payment->customer?->name ?? 'عميل غير معروف' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-body small">{{ $payment->payment_date->format('Y-m-d') }}</span>
                                        <span class="text-gray-500 x-small">{{ $payment->payment_date->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($payment->payment_method == 'cash')
                                        <span class="badge bg-success/10 text-success border border-success/20">
                                            <i class="bi bi-cash-stack me-1"></i>{{ __('Cash') }}</span>
                                    @elseif($payment->payment_method == 'bank_transfer')
                                        <span class="badge bg-primary/10 text-primary border border-primary/20">
                                            <i class="bi bi-bank2 me-1"></i>{{ __('Bank Transfer') }}</span>
                                    @else
                                        <span class="badge bg-secondary/10 text-secondary border border-secondary/20">
                                            {{ $payment->payment_method }}
                                        </span>
                                    @endif
                                </td>
                                <td class="font-monospace text-gray-400 small">{{ $payment->reference ?? '-' }}</td>
                                <td class="text-end">
                                    <h5 class="mb-0 fw-bold text-heading text-glow">{{ number_format($payment->amount, 2) }}</h5>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('customer-payments.show', $payment->id) }}"
                                            class="btn btn-sm btn-icon-glass text-info" title="{{ __('View Details') }}">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('customer-payments.print', $payment->id) }}" target="_blank"
                                            class="btn btn-sm btn-icon-glass text-warning" title="{{ __('Print Receipt') }}">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center opacity-50">
                                        <div class="icon-circle bg-gray-800 text-gray-500 mb-3"
                                            style="width: 80px; height: 80px;">
                                            <i class="bi bi-inbox fs-1"></i>
                                        </div>
                                        <h5 class="text-gray-400">لا توجد تحصيلات مسجلة</h5>
                                        <p class="text-gray-600 small">يمكنك تسجيل تحصيل جديد من الزر بالأعلى</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-top border-secondary border-opacity-10/10 d-flex justify-content-center">
                    {{ $payments->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        

        .shadow-neon {
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.5);
        }

        .shadow-neon-blue {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }

        .text-glow {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .hover-bg-surface-5:hover {
            background: var(--btn-glass-bg);
        }

        .btn-gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: var(--text-primary);
        }

        .icon-square-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Filter Styles */
        .glass-input-group,
        .glass-select {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--btn-glass-border);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .glass-input-group:focus-within,
        .glass-select:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: rgba(59, 130, 246, 0.5);
            /* Blue Glow */
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
        }

        .glass-input-group .input-group-text {
            border: none;
        }

        .glass-input-group input {
            border: none;
            padding: 10px 15px;
        }

        .glass-select {
            padding: 10px 15px;
            cursor: pointer;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: var(--text-primary);
            transition: all 0.3s;
        }

        .btn-gradient-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-glass-icon {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            color: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            transition: all 0.2s;
        }

        .btn-glass-icon:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border-color: rgba(255, 255, 255, 0.2);
        }
    </style>
@endsection