@extends('layouts.app')

@section('title', 'ملف المورد: ' . $supplier->name)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div class="avatar-md bg-gradient-cyan text-white rounded-3 d-flex align-items-center justify-content-center fw-bold fs-4 shadow-neon-cyan">
                    {{ strtoupper(substr($supplier->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-0">{{ $supplier->name }}</h2>
                    <div class="d-flex align-items-center gap-2 text-gray-400 x-small font-monospace">
                        <span>{{ $supplier->code }}</span>
                        @if($supplier->is_active)
                            <span class="badge bg-green-500 bg-opacity-10 text-green-400 border border-green-500 border-opacity-20">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-red-500 bg-opacity-10 text-red-400 border border-red-500 border-opacity-20">{{ __('Inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('suppliers.statement', $supplier->id) }}" class="btn btn-glass-cyan">
                    <i class="bi bi-file-text me-2"></i>{{ __('Account Statement') }}</a>
                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-light">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}</a>
                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" data-confirm="هل أنت متأكد من حذف هذا المورد؟">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger border-0">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-4">
            <!-- Balance (Corrected Logic: Purchases - Paid) -->
            <div class="col-md-4">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100 border-top-gradient-cyan">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-cyan-400 x-small fw-bold text-uppercase tracking-wide">الرصيد المستحق</span>
                            <h2 class="text-heading fw-bold mb-0 mt-1">{{ number_format($balance ?? 0, 2) }} <span class="fs-6 fw-normal text-gray-500">EGP</span></h2>
                        </div>
                        <div class="icon-circle bg-cyan-500 bg-opacity-10 text-cyan-400">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    @if(($balance ?? 0) > 0)
                        <div class="d-flex align-items-center gap-2 text-red-400 x-small bg-red-500 bg-opacity-10 px-2 py-1 rounded w-fit">
                            <i class="bi bi-arrow-up-right"></i>
                            <span>مستحق للمورد</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 text-green-400 x-small bg-green-500 bg-opacity-10 px-2 py-1 rounded w-fit">
                            <i class="bi bi-check-circle"></i>
                            <span>خالص / رصيد دائن</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Total Purchases -->
            <div class="col-md-4">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-gray-400 x-small fw-bold text-uppercase tracking-wide">إجمالي المشتريات</span>
                            <h3 class="text-heading fw-bold mb-0 mt-1">{{ number_format($totalPurchases ?? 0, 2) }}</h3>
                        </div>
                        <div class="icon-circle bg-surface-5 text-gray-300">
                            <i class="bi bi-bag-check"></i>
                        </div>
                    </div>
                    <div class="progress bg-surface-5" style="height: 4px;">
                        <div class="progress-bar bg-cyan-500" role="progressbar" style="width: 70%"></div>
                    </div>
                </div>
            </div>

            <!-- Total Paid -->
            <div class="col-md-4">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-gray-400 x-small fw-bold text-uppercase tracking-wide">إجمالي المدفوعات</span>
                            <h3 class="text-heading fw-bold mb-0 mt-1">{{ number_format($totalPaid ?? 0, 2) }}</h3>
                        </div>
                        <div class="icon-circle bg-surface-5 text-gray-300">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="progress bg-surface-5" style="height: 4px;">
                        <div class="progress-bar bg-green-500" role="progressbar" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="{{ route('purchase-invoices.create', ['supplier_id' => $supplier->id]) }}" class="text-decoration-none group">
                    <div class="glass-panel p-3 d-flex align-items-center gap-3 hover-scale transition-all border border-secondary border-opacity-10-5 hover-border-cyan">
                        <div class="icon-box bg-cyan-500 bg-opacity-20 text-cyan-400 group-hover-bg-cyan group-hover-text-body transition-all">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-heading fw-bold mb-1">تسجيل فاتورة شراء</h6>
                            <p class="text-gray-500 x-small mb-0">إدخال فاتورة مستحقة جديدة</p>
                        </div>
                        <i class="bi bi-arrow-left ms-auto text-gray-600 group-hover-text-body"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="text-decoration-none group">
                    <div class="glass-panel p-3 d-flex align-items-center gap-3 hover-scale transition-all border border-secondary border-opacity-10-5 hover-border-purple">
                        <div class="icon-box bg-purple-500 bg-opacity-20 text-purple-400 group-hover-bg-purple group-hover-text-body transition-all">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-heading fw-bold mb-1">{{ __('Pay Installment') }}</h6>
                            <p class="text-gray-500 x-small mb-0">تسجيل مبلغ مدفوع للمورد</p>
                        </div>
                        <i class="bi bi-arrow-left ms-auto text-gray-600 group-hover-text-body"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="text-decoration-none group">
                    <div class="glass-panel p-3 d-flex align-items-center gap-3 hover-scale transition-all border border-secondary border-opacity-10-5 hover-border-yellow">
                        <div class="icon-box bg-yellow-500 bg-opacity-20 text-yellow-400 group-hover-bg-yellow group-hover-text-body transition-all">
                            <i class="bi bi-cart-plus fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-heading fw-bold mb-1">أمر شراء جديد</h6>
                            <p class="text-gray-500 x-small mb-0">إرسال طلبية بضاعة جديدة</p>
                        </div>
                        <i class="bi bi-arrow-left ms-auto text-gray-600 group-hover-text-body"></i>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Info Column -->
            <div class="col-md-4">
                <div class="glass-panel p-4 h-100">
                    <h5 class="text-cyan-400 fw-bold mb-4"><i class="bi bi-info-circle me-2"></i>معلومات الاتصال</h5>
                    
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="d-flex align-items-center gap-3 text-body">
                            <div class="icon-sm bg-surface-5 text-gray-400"><i class="bi bi-person"></i></div>
                            <div>
                                <span class="d-block x-small text-gray-500">الشخص المسؤول</span>
                                <span class="fw-bold">{{ $supplier->contact_person ?? '-' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center gap-3 text-body">
                            <div class="icon-sm bg-surface-5 text-gray-400"><i class="bi bi-telephone"></i></div>
                            <div>
                                <span class="d-block x-small text-gray-500">الهاتف</span>
                                <span class="fw-bold font-monospace">{{ $supplier->phone ?? '-' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center gap-3 text-body">
                            <div class="icon-sm bg-surface-5 text-gray-400"><i class="bi bi-envelope"></i></div>
                            <div>
                                <span class="d-block x-small text-gray-500">{{ __('Email') }}</span>
                                <span class="fw-bold">{{ $supplier->email ?? '-' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center gap-3 text-body">
                            <div class="icon-sm bg-surface-5 text-gray-400"><i class="bi bi-geo-alt"></i></div>
                            <div>
                                <span class="d-block x-small text-gray-500">{{ __('Address') }}</span>
                                <span>{{ $supplier->address ?? '-' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center gap-3 text-body">
                            <div class="icon-sm bg-surface-5 text-gray-400"><i class="bi bi-receipt"></i></div>
                            <div>
                                <span class="d-block x-small text-gray-500">{{ __('Tax Number') }}</span>
                                <span class="font-monospace">{{ $supplier->tax_number ?? '-' }}</span>
                            </div>
                        </li>
                    </ul>

                    @if($supplier->notes)
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10-5">
                        <h6 class="text-gray-400 x-small fw-bold mb-2">{{ __('Notes') }}</h6>
                        <p class="text-gray-300 small mb-0">{{ $supplier->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity Column -->
            <div class="col-md-8">
                <div class="glass-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-cyan-400 fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>آخر الفواتير</h5>
                        <a href="#" class="btn btn-sm btn-outline-cyan disabled">عرض الكل</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>الرصيد المتبقي</th>
                                    <th>{{ __('Status') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td class="text-gray-400 x-small">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                        <td class="font-monospace text-body">{{ $invoice->invoice_number }}</td>
                                        <td class="fw-bold">{{ number_format($invoice->total, 2) }}</td>
                                        <td class="text-red-400">{{ number_format($invoice->balance_due, 2) }}</td>
                                        <td>
                                            @if($invoice->status == 'paid') <span class="badge bg-green-500 bg-opacity-10 text-green-400">خالص</span>
                                            @elseif($invoice->status == 'partial') <span class="badge bg-yellow-500 bg-opacity-10 text-yellow-400">{{ __('Partial') }}</span>
                                            @else <span class="badge bg-red-500 bg-opacity-10 text-red-400">{{ __('Unpaid') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn-icon-glass"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-gray-500">
                                            لا توجد فواتير حديثة لهذا المورد
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .shadow-neon-cyan { box-shadow: 0 0 20px rgba(6, 182, 212, 0.4); }
        
        .icon-circle {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .group:hover .group-hover-bg-cyan { background: #06b6d4 !important; }
        .group:hover .group-hover-text-white { color: var(--text-primary); !important; }
        .hover-border-cyan:hover { border-color: #06b6d4 !important; }

        .group:hover .group-hover-bg-purple { background: #a855f7 !important; }
        .hover-border-purple:hover { border-color: #a855f7 !important; }
        
        .group:hover .group-hover-bg-yellow { background: #eab308 !important; }
        .text-yellow-400 { color: #facc15 !important; }
        .bg-yellow-500 { background: #eab308 !important; }
        .hover-border-yellow:hover { border-color: #eab308 !important; }

        .icon-box {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        
        .hover-scale:hover { transform: translateY(-3px); }
        
        .btn-glass-cyan {
            background: rgba(6, 182, 212, 0.1);
            color: #22d3ee;
            border: 1px solid rgba(34, 211, 238, 0.2);
            padding: 8px 16px; border-radius: 8px;
            font-weight: bold;
        }
        .btn-glass-cyan:hover {
            background: rgba(6, 182, 212, 0.2);
            color: var(--text-primary); border-color: #22d3ee;
        }
        
        .border-top-gradient-cyan {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #06b6d4, #67e8f9) 1;
        }
    </style>
@endsection
