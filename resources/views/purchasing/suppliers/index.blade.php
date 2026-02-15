@extends('layouts.app')

@section('title', __('Suppliers'))

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-cyan shadow-neon-cyan">
                    <i class="bi bi-truck fs-3 text-body"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-1 tracking-wide">إدارة الموردين</h2>
                    <p class="mb-0 text-gray-400 small">قاعدة بيانات شركاء التوريد</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-success-glass d-flex align-items-center gap-2 shadow-lg dropdown-toggle" 
                        type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i>
                        <span class="fw-bold d-none d-md-inline">{{ __('Export') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark bg-slate-900 border-secondary border-opacity-10-10 shadow-neon" aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('export.suppliers', ['format' => 'xlsx']) }}">
                                <i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('export.suppliers', ['format' => 'csv']) }}">
                                <i class="bi bi-file-earmark-code text-info"></i> CSV (.csv)
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('suppliers.import.form') }}"
                    class="btn btn-cyan-glass d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-cloud-upload"></i>
                    <span class="fw-bold d-none d-md-inline">{{ __('Import') }}</span>
                </a>
                <a href="{{ route('suppliers.create') }}"
                    class="btn btn-action-cyan d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-plus-lg"></i>
                    <span class="fw-bold">إضافة مورد جديد</span>
                </a>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-cyan-400 x-small fw-bold text-uppercase tracking-wide">إجمالي الموردين</span>
                            <h2 class="text-heading fw-bold mb-0 mt-1">{{ $stats['total_suppliers'] }}</h2>
                        </div>
                        <div class="icon-circle bg-cyan-500 bg-opacity-10 text-cyan-400">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-green-400 x-small fw-bold text-uppercase tracking-wide">الموردين النشطين</span>
                            <h2 class="text-heading fw-bold mb-0 mt-1">{{ $stats['active_suppliers'] }}</h2>
                        </div>
                        <div class="icon-circle bg-green-500 bg-opacity-10 text-green-400">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-red-400 x-small fw-bold text-uppercase tracking-wide">مستحقات الموردين</span>
                            <h2 class="text-heading fw-bold mb-0 mt-1">{{ number_format($stats['total_debt']) }} <small class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-red-500 bg-opacity-10 text-red-400">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-purple-400 x-small fw-bold text-uppercase tracking-wide">مشتريات الشهر</span>
                            <h2 class="text-heading fw-bold mb-0 mt-1">{{ number_format($stats['monthly_purchases']) }} <small class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-purple-500 bg-opacity-10 text-purple-400">
                            <i class="bi bi-bag-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-secondary border-opacity-10-5 rounded-4 p-4 mb-5">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('Search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-cyan"
                            value="{{ request('search') }}" placeholder="اسم المورد، الكود، أو الهاتف...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('Status') }}</label>
                    <select name="status" class="form-select form-select-dark text-body cursor-pointer hover:bg-surface-5">
                        <option value="">-- الكل --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-cyan-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i>{{ __('Filter') }}</button>
                </div>
            </form>
        </div>

        <!-- Suppliers Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-cyan">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>المورد</th>
                            <th>معلومات الاتصال</th>
                            <th>{{ __('Balance') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="pe-4 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 text-gray-500 font-monospace">{{ $supplier->code }}</td>
                                <td>
                                    <div>
                                        <h6 class="text-heading mb-0 fw-bold">{{ $supplier->name }}</h6>
                                        <span class="x-small text-gray-500">{{ $supplier->contact_person }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if($supplier->phone)
                                        <span class="x-small text-gray-400"><i class="bi bi-telephone text-cyan-400 me-2"></i>{{ $supplier->phone }}</span>
                                        @endif
                                        @if($supplier->email)
                                        <span class="x-small text-gray-400"><i class="bi bi-envelope text-cyan-400 me-2"></i>{{ $supplier->email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <!-- Balance Calc Placeholder: In real app, this comes from transaction sum -->
                                    @php
                                        // $balance = $supplier->purchaseInvoices()->sum('total') - $supplier->payments()->sum('amount');
                                        // Using model method if available or 0 for now
                                        $balance = 0; // Optimization: Eager load or calculate in controller
                                        // Controller didn't pass balance for list, usually calculated via relationship
                                    @endphp
                                    <span class="fw-bold text-body">0.00</span> <span class="x-small text-gray-500">EGP</span>
                                </td>
                                <td>
                                    @if($supplier->is_active)
                                        <span class="badge bg-green-500 bg-opacity-10 text-green-400 px-2 py-1 rounded-pill border border-green-500 border-opacity-20">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-red-500 bg-opacity-10 text-red-400 px-2 py-1 rounded-pill border border-red-500 border-opacity-20">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn-icon-glass" title="عرض الملف">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('suppliers.statement', $supplier->id) }}" class="btn-icon-glass" title="{{ __('Account Statement') }}">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn-icon-glass" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" data-confirm="هل أنت متأكد من حذف هذا المورد؟">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon-glass text-danger hover-danger" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                        <i class="bi bi-box-seam fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">{{ __('No suppliers found') }}</h5>
                                        <p class="text-gray-600 small">ابدأ بإضافة أول مورد للنظام</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($suppliers->hasPages())
            <div class="p-4 border-top border-secondary border-opacity-10-5">
                {{ $suppliers->links() }}
            </div>
            @endif
        </div>
    </div>

    <style>
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Cyan Theme Overrides */
        .text-cyan-400 { color: #22d3ee !important; }
        .bg-cyan-500 { background: #06b6d4 !important; }
        
        .bg-gradient-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        
        .shadow-neon-cyan {
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
        }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(6, 182, 212, 0.4);
        }

        .btn-cyan-glass {
            background: rgba(6, 182, 212, 0.15);
            color: #22d3ee;
            border: 1px solid rgba(34, 211, 238, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-cyan-glass:hover {
            background: rgba(6, 182, 212, 0.25);
            color: var(--text-primary);
            border-color: #22d3ee;
        }

        .focus-ring-cyan:focus {
            border-color: #22d3ee !important;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.1) !important;
        }

        .border-top-gradient-cyan {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #06b6d4, #67e8f9) 1;
        }
        
        /* Reusing global glass styles */
        .form-control-dark, .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
        }
        
        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }
        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 1rem;
        }
        .table-dark-custom td {
            padding: 1rem;
        }
        
        .group-hover-actions:hover .group-hover-visible {
            opacity: 1 !important;
        }
        
        .hover-scale:hover { transform: scale(1.1); }
        .hover-text-cyan:hover { color: #22d3ee !important; }
        
        .btn-icon-glass {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 6px;
            background: var(--btn-glass-bg);
            color: #cbd5e1;
            transition: 0.2s;
        }
        .btn-icon-glass:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text-primary);
        }
        .hover-danger:hover { background: rgba(239, 68, 68, 0.2) !important; color: #ef4444 !important; }
    </style>
@endsection
