@extends('layouts.app')

@section('title', __('Dashboard'))

@push('styles')
<style>
    
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.2);
    }
    .text-glow-primary { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
    .text-glow-success { text-shadow: 0 0 15px rgba(34, 197, 94, 0.5); }
    .text-glow-warning { text-shadow: 0 0 15px rgba(234, 179, 8, 0.5); }
    .text-glow-danger { text-shadow: 0 0 15px rgba(239, 68, 68, 0.5); }

    .btn-glass-action {
        background: var(--btn-glass-bg);
        border: 1px solid var(--btn-glass-border);
        color: var(--text-primary);
        transition: all 0.3s;
    }
    .btn-glass-action:hover {
        background: rgba(255, 255, 255, 0.15);
        color: var(--text-primary);
        transform: translateY(-2px);
    }

    .absolute-glow {
        position: absolute;
        width: 150px;
        height: 150px;
        filter: blur(50px);
        pointer-events: none;
        opacity: 0.4;
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <div class="row g-4 mb-5">
        <div class="col-md-9">
            <div class="glass-card h-100 position-relative overflow-hidden p-5 d-flex flex-column justify-content-center">
                <div class="absolute-glow top-0 end-0 bg-primary"></div>
                <div class="absolute-glow bottom-0 start-0 bg-purple-600"></div>
                
                <div class="position-relative z-1 d-flex justify-content-between align-items-end">
                    <div>
                        <h1 class="display-5 fw-bold text-heading mb-2">{{ __('Welcome') }}, {{ auth()->user()->name }} 👋</h1>
                        <p class="text-secondary fs-5 mb-0">{{ __('Overview of today\'s performance.') }}</p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="{{ route('pos.index') }}" class="btn btn-gradient-primary fw-bold shadow-lg px-4 py-3 rounded-pill d-flex align-items-center gap-2 hover-scale">
                            <i class="bi bi-shop fs-4"></i>
                            <span>{{ __('POS (Point of Sale)') }}</span>
                        </a>
                        <a href="{{ route('sales-invoices.create') }}" class="btn btn-glass-action fw-bold px-4 py-3 rounded-pill d-flex align-items-center gap-2">
                            <i class="bi bi-receipt fs-4"></i>
                            <span>{{ __('New Invoice') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="glass-card h-100 position-relative overflow-hidden p-4 d-flex flex-column justify-content-center text-center bg-gradient-to-br from-blue-600 to-blue-800 border-0">
                <div class="absolute-glow top-0 start-0 bg-surface opacity-25"></div>
                
                <div class="position-relative z-1">
                     <div class="display-5 fw-bold text-body mb-1 tracking-wider" id="clock">{{ \Carbon\Carbon::now()->translatedFormat('h:i A') }}</div>
                    <div class="text-body opacity-75" id="date">{{ \Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('l, d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <!-- Sales Today -->
        <div class="col-md-3">
            <div class="glass-card h-100 p-4 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-primary/20" style="width: 100px; height: 100px;"></div>
                <div class="d-flex justify-content-between mb-4">
                    <div class="icon-box bg-primary/20 text-primary rounded-circle p-3">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                     @if($dashboard['orders_count_today'] > 0)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 rounded-pill d-flex align-items-center gap-1">
                            <i class="bi bi-arrow-up-short"></i> {{ $dashboard['orders_count_today'] }} {{ __('Order(s)') }}
                        </span>
                    @endif
                </div>
                <h2 class="fw-bold text-heading mb-1 text-glow-primary">{{ number_format($dashboard['sales_today'], 2) }}</h2>
                <div class="text-secondary small">{{ __('Total Sales (Cash Movement)') }}</div>

            </div>
        </div>

        <!-- Revenue MTD -->
        <div class="col-md-3">
            <div class="glass-card h-100 p-4 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-success opacity-25" style="width: 100px; height: 100px;"></div>
                <div class="d-flex justify-content-between mb-4">
                    <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-heading mb-1 text-glow-success">{{ number_format($dashboard['revenue_mtd'], 2) }}</h2>
                <div class="text-secondary small">{{ __('Sales Revenue (MTD)') }}</div>
                <div class="text-muted mt-2 x-small">
                    <i class="bi bi-info-circle me-1"></i> {{ __('Matches Profit & Loss Report') }}
                </div>
            </div>
        </div>

        <!-- Receivables -->
        <div class="col-md-3">
            <div class="glass-card h-100 p-4 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-warning opacity-25" style="width: 100px; height: 100px;"></div>
                 <div class="d-flex justify-content-between mb-4">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="bi bi-person-exclamation fs-4"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-heading mb-1 text-glow-warning">{{ number_format($dashboard['receivables'], 2) }}</h2>
                <div class="text-secondary small">{{ __('Receivables (Credit)') }}</div>
            </div>
        </div>

        <!-- Payables -->
        <div class="col-md-3">
           <div class="glass-card h-100 p-4 position-relative overflow-hidden">
                <div class="absolute-glow top-0 end-0 bg-danger opacity-25" style="width: 100px; height: 100px;"></div>
                 <div class="d-flex justify-content-between mb-4">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                        <i class="bi bi-building-exclamation fs-4"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-heading mb-1 text-glow-danger">{{ number_format($dashboard['payables'], 2) }}</h2>
                <div class="text-secondary small">{{ __('Payables (Liabilities)') }}</div>
            </div>
        </div>
    </div>

    <!-- Charts & Tables -->
    <div class="row g-4 mb-4">
        <!-- Sales Chart -->
        <div class="col-md-8">
            <div class="glass-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-heading mb-0">{{ __('Sales Analysis') }}</h5>
                    <div class="badge bg-surface bg-opacity-10 text-secondary border border-secondary border-opacity-10 border-opacity-10 px-3 py-2">{{ __('Last 7 Days') }}</div>
                </div>
                <div style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-md-4">
             <div class="glass-card h-100 p-0 overflow-hidden">
                <div class="p-4 border-bottom border-secondary border-opacity-10 border-opacity-10">
                    <h5 class="fw-bold text-heading mb-0">{{ __('Top Selling Products') }}</h5>
                </div>
                <div class="p-2">
                     @forelse($dashboard['top_products'] as $product)
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-surface bg-opacity-10 transition-all">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                    <span class="fw-bold">{{ substr($product['name'], 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="fw-bold text-body">{{ $product['name'] }}</div>
                                    <small class="text-secondary">{{ $product['qty'] }} {{ __('Item(s)') }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-body">{{ number_format($product['sales']) }}</div>
                                <small class="text-secondary">{{ __('EGP') }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-secondary">{{ __('No Data') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="row g-4">
        <div class="col-12">
             <div class="glass-card p-0 overflow-hidden">
                <div class="p-4 border-bottom border-secondary border-opacity-10/10 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-heading mb-0">{{ __('Recent Transactions') }}</h5>
                    <a href="{{ route('sales-invoices.index') }}" class="btn btn-sm btn-glass-action rounded-pill px-3">{{ __('View Full History') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead class="bg-surface/5">
                            <tr>
                                <th class="px-4 py-3 text-gray-400 fw-normal">{{ __('Invoice Number') }}</th>
                                <th class="py-3 text-gray-400 fw-normal">{{ __('Customer') }}</th>
                                <th class="py-3 text-gray-400 fw-normal">{{ __('Date') }}</th>
                                <th class="py-3 text-gray-400 fw-normal">{{ __('Amount') }}</th>
                                <th class="py-3 text-gray-400 fw-normal">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-end text-gray-400 fw-normal">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             @forelse($dashboard['recent_invoices'] as $invoice)
                                <tr class="hover-bg-surface-5">
                                    <td class="px-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-hash text-primary"></i>
                                            <span class="font-monospace text-body fw-bold">{{ $invoice->invoice_number }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-surface/10 rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                <i class="bi bi-person text-gray-400 small"></i>
                                            </div>
                                            <span class="text-body">{{ $invoice->customer->name ?? 'عميل نقدي' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-gray-300 small">{{ $invoice->invoice_date->format('Y-m-d') }}</div>
                                        <div class="text-gray-600 x-small">{{ $invoice->invoice_date->format('h:i A') }}</div>
                                    </td>
                                    <td><span class="fw-bold text-body">{{ number_format($invoice->total, 2) }}</span></td>
                                    <td>
                                        @if($invoice->status == \Modules\Sales\Enums\SalesInvoiceStatus::PAID)
                                            <span class="badge bg-green-500/20 text-green-400 border border-green-500/20 px-3 py-1 rounded-pill">{{ __('Paid') }}</span>
                                        @elseif($invoice->status == \Modules\Sales\Enums\SalesInvoiceStatus::PARTIAL)
                                            <span class="badge bg-yellow-500/20 text-yellow-400 border border-yellow-500/20 px-3 py-1 rounded-pill">{{ __('Partial') }}</span>
                                        @else
                                            <span class="badge bg-red-500/20 text-red-400 border border-red-500/20 px-3 py-1 rounded-pill">{{ __('Unpaid') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 text-end">
                                        <a href="{{ route('pos.receipt', $invoice->id) }}" target="_blank" class="btn btn-sm btn-icon-glass text-info rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-gray-500">{{ __('No recent invoices') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamic Clock
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const timeString = `${hours}:${minutes} ${ampm}`;
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Blue start
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Transparent end

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($dashboard['sales_trend']['labels']),
                datasets: [{
                    label: 'المبيعات',
                    data: @json($dashboard['sales_trend']['values']),
                    borderColor: '#60a5fa', // Blue-400
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#1e293b',
                    pointBorderColor: '#60a5fa',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                },
                interaction: { intersect: false, mode: 'index' },
            }
        });
    });
</script>
<style>
    .btn-icon-glass {
        background: var(--btn-glass-bg);
        border: 1px solid var(--btn-glass-border);
        transition: all 0.2s;
    }
    .btn-icon-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        color: var(--text-primary); !important;
        transform: scale(1.1);
    }
    .hover-bg-surface-5:hover { background: var(--btn-glass-bg); }
    
    .btn-gradient-primary {
         background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
         border: none;
         color: var(--text-primary);
         transition: all 0.3s;
    }
    .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
    .from-blue-600 { --tw-gradient-from: #2563eb; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(37, 99, 235, 0)); }
    .to-blue-800 { --tw-gradient-to: #1e40af; }
    .from-indigo-500 { --tw-gradient-from: #6366f1; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(99, 102, 241, 0)); }
    .to-purple-600 { --tw-gradient-to: #9333ea; }
</style>
@endpush