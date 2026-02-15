@extends('layouts.app')

@section('title', 'تفاصيل العميل: ' . $customer->name)

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center gap-3">
             <div class="icon-box-lg bg-gradient-to-br from-purple-600 to-indigo-700 rounded-circle shadow-lg text-white">
                <i class="bi bi-person-vcard fs-2"></i>
            </div>
            <div>
                <h2 class="fw-bold text-heading mb-0">{{ $customer->name }}</h2>
                <div class="d-flex align-items-center gap-2 text-gray-400 small mt-1">
                    <span class="font-monospace">{{ $customer->code }}</span>
                    <span class="text-body/20">|</span>
                    <span class="badge {{ $customer->is_active ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }} border border-secondary border-opacity-10/10">
                        {{ $customer->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                    <span class="text-body/20">|</span>
                    <span class="badge bg-{{ $customer->type_color }}/10 text-{{ $customer->type_color }} border border-{{ $customer->type_color }}/20">
                        {{ $customer->type_label }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('customers.index') }}" class="btn btn-glass-outline rounded-pill">
                <i class="bi bi-arrow-right me-2"></i> القائمة
            </a>
            
            <div class="btn-group shadow-lg rounded-pill overflow-hidden">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-dark border-start border-secondary border-opacity-10/10 text-warning hover-bg-warning-dark">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}</a>
                <a href="{{ route('customers.statement', $customer->id) }}" target="_blank" class="btn btn-dark border-start border-secondary border-opacity-10/10 text-info hover-bg-info-dark">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>{{ __('Account Statement') }}</a>
                <a href="{{ route('customer-payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-success fw-bold px-4 hover-scale text-white">
                    <i class="bi bi-cash-stack me-2"></i> تحصيل دفعة
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                <div class="absolute-glow top-0 end-0 bg-red-500/10"></div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-gray-400 mb-0">{{ __('Current Balance') }}</h6>
                    <i class="bi bi-wallet2 text-red-400 fs-4"></i>
                </div>
                <h3 class="fw-bold {{ $customer->balance > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ number_format($customer->balance, 2) }} <small class="fs-6 text-gray-500">EGP</small>
                </h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                <div class="absolute-glow top-0 end-0 bg-blue-500/10"></div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-gray-400 mb-0">المتاحة للطلب (Credit)</h6>
                    <i class="bi bi-graph-up-arrow text-blue-400 fs-4"></i>
                </div>
                <h3 class="fw-bold text-heading mb-0">
                     {{ number_format($customer->getAvailableCredit(), 2) }} <small class="fs-6 text-gray-500">EGP</small>
                </h3>
                <div class="progress mt-3 bg-surface/5" style="height: 4px;">
                     @php 
                        $limit = $customer->credit_limit > 0 ? $customer->credit_limit : 1;
                        $usage = ($customer->balance / $limit) * 100;
                     @endphp
                    <div class="progress-bar bg-blue-500" style="width: {{ min($usage, 100) }}%"></div>
                </div>
                <small class="text-gray-500 mt-2 d-block">السقف الائتماني: {{ number_format($customer->credit_limit, 2) }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                 <div class="absolute-glow top-0 end-0 bg-purple-500/10"></div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-gray-400 mb-0">إجراءات سريعة</h6>
                    <i class="bi bi-lightning-charge text-purple-400 fs-4"></i>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('sales-orders.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-glass-outline w-100">
                        <i class="bi bi-cart-plus me-1"></i> أمر بيع
                    </a>
                    <a href="{{ route('sales-invoices.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-glass-outline w-100">
                        <i class="bi bi-receipt me-1"></i> فواتير
                    </a>
                     <a href="{{ route('sales-orders.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-glass-outline w-100">
                        <i class="bi bi-box-seam me-1"></i> طلبات
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Basic Info -->
        <div class="col-md-6">
            <div class="glass-panel p-4 h-100 border border-secondary border-opacity-10/10">
                <h5 class="fw-bold text-heading mb-4 border-bottom border-secondary border-opacity-10/10 pb-3">
                    <i class="bi bi-info-circle me-2 text-info"></i>{{ __('Basic Information') }}</h5>
                <div class="vstack gap-3">
                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10/5 pb-2">
                        <span class="text-gray-400">{{ __('Email') }}</span>
                        <span class="text-body">{{ $customer->email ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10/5 pb-2">
                        <span class="text-gray-400">الهاتف</span>
                        <span class="text-body font-monospace">{{ $customer->phone ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10/5 pb-2">
                        <span class="text-gray-400">الموبايل</span>
                        <span class="text-body font-monospace">{{ $customer->mobile ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10/5 pb-2">
                        <span class="text-gray-400">الشخص المسؤول</span>
                        <span class="text-body">{{ $customer->contact_person ?? '-' }}</span>
                    </div>
                     <div class="d-flex justify-content-between pt-2">
                        <span class="text-gray-400">{{ __('Tax Number') }}</span>
                        <span class="text-body font-monospace">{{ $customer->tax_number ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses & Financial -->
        <div class="col-md-6">
            <div class="glass-panel p-4 h-100 border border-secondary border-opacity-10/10">
                <h5 class="fw-bold text-heading mb-4 border-bottom border-secondary border-opacity-10/10 pb-3">
                    <i class="bi bi-geo-alt me-2 text-warning"></i> العناوين والشروط
                </h5>
                 <div class="row g-4">
                    <div class="col-12">
                        <h6 class="text-gray-500 small text-uppercase fw-bold mb-2">العنوان الرئيسي</h6>
                        <p class="text-body bg-surface/5 p-3 rounded mb-0">
                            {{ $customer->billing_address ?? 'لا يوجد عنوان مسجل' }}
                            <br>
                            <span class="text-gray-400 small">{{ $customer->billing_city }} - {{ $customer->billing_country }}</span>
                        </p>
                    </div>
                    <div class="col-6">
                         <h6 class="text-gray-500 small text-uppercase fw-bold mb-2">شروط الدفع</h6>
                         <div class="text-body fs-5 fw-bold">{{ $customer->payment_terms }} <span class="fs-6 text-gray-400 fw-normal">يوم</span></div>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Notes -->
        @if($customer->notes)
        <div class="col-12">
            <div class="glass-panel p-4 border border-secondary border-opacity-10/10">
                 <h6 class="text-gray-400 fw-bold mb-2 small text-uppercase"><i class="bi bi-sticky me-2"></i>{{ __('Notes') }}</h6>
                 <p class="text-body mb-0 opacity-75">{{ $customer->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .glass-panel {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .icon-box-lg {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-glass-outline {
        background: var(--btn-glass-bg);
        border: 1px solid var(--btn-glass-border);
        color: var(--text-primary);
    }
    .btn-glass-outline:hover {
        background: rgba(255,255,255,0.1);
        color: var(--text-primary);
    }
    .absolute-glow {
        position: absolute;
        width: 100px; height: 100px;
        filter: blur(40px);
        pointer-events: none;
    }
    .hover-bg-warning-dark:hover { background-color: #78350f !important; color: #fbbf24 !important; }
    .hover-bg-info-dark:hover { background-color: #0c4a6e !important; color: #38bdf8 !important; }
</style>
@endsection