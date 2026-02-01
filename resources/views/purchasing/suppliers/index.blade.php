@extends('layouts.app')

@section('title', 'الموردين')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-cyan shadow-neon-cyan">
                    <i class="bi bi-truck fs-3 text-white"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-1 tracking-wide">إدارة الموردين</h2>
                    <p class="mb-0 text-gray-400 small">قاعدة بيانات شركاء التوريد</p>
                </div>
            </div>
            <a href="{{ route('suppliers.create') }}"
                class="btn btn-action-cyan d-flex align-items-center gap-2 shadow-lg">
                <i class="bi bi-plus-lg"></i>
                <span class="fw-bold">إضافة مورد جديد</span>
            </a>
        </div>

        <!-- Stats Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-cyan-400 x-small fw-bold text-uppercase tracking-wide">إجمالي الموردين</span>
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ $stats['total_suppliers'] }}</h2>
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
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ $stats['active_suppliers'] }}</h2>
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
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['total_debt']) }} <small class="fs-6 text-gray-400">EGP</small></h2>
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
                            <h2 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['monthly_purchases']) }} <small class="fs-6 text-gray-400">EGP</small></h2>
                        </div>
                        <div class="icon-circle bg-purple-500 bg-opacity-10 text-purple-400">
                            <i class="bi bi-bag-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-white-5 rounded-4 p-4 mb-5">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">بحث</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-cyan"
                            value="{{ request('search') }}" placeholder="اسم المورد، الكود، أو الهاتف...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">الحالة</label>
                    <select name="status" class="form-select form-select-dark text-white cursor-pointer hover:bg-white-5">
                        <option value="">-- الكل --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-cyan-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i> تصفية
                    </button>
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
                            <th>الرصيد</th>
                            <th>الحالة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4 text-gray-500 font-monospace">{{ $supplier->code }}</td>
                                <td>
                                    <div>
                                        <h6 class="text-white mb-0 fw-bold">{{ $supplier->name }}</h6>
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
                                    <span class="fw-bold text-white">0.00</span> <span class="x-small text-gray-500">EGP</span>
                                </td>
                                <td>
                                    @if($supplier->is_active)
                                        <span class="badge bg-green-500 bg-opacity-10 text-green-400 px-2 py-1 rounded-pill border border-green-500 border-opacity-20">نشط</span>
                                    @else
                                        <span class="badge bg-red-500 bg-opacity-10 text-red-400 px-2 py-1 rounded-pill border border-red-500 border-opacity-20">غير نشط</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn-icon-glass" title="عرض الملف">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('suppliers.statement', $supplier->id) }}" class="btn-icon-glass" title="كشف حساب">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn-icon-glass" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon-glass text-danger hover-danger" title="حذف">
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
                                        <h5 class="text-gray-400">لا يوجد موردين</h5>
                                        <p class="text-gray-600 small">ابدأ بإضافة أول مورد للنظام</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($suppliers->hasPages())
            <div class="p-4 border-top border-white-5">
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
            color: white;
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
            color: white;
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
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
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
        
        .hover-scale:hover { transform: scale(1.1); }
        .hover-text-cyan:hover { color: #22d3ee !important; }
        
        .btn-icon-glass {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 6px;
            background: rgba(255,255,255,0.05);
            color: #cbd5e1;
            transition: 0.2s;
        }
        .btn-icon-glass:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .hover-danger:hover { background: rgba(239, 68, 68, 0.2) !important; color: #ef4444 !important; }
    </style>
@endsection
