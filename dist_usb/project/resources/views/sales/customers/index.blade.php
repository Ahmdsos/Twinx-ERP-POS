@extends('layouts.app')

@section('title', __('Customers'))

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-indigo shadow-neon-indigo">
                    <i class="bi bi-people-fill fs-3 text-body"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-1 tracking-wide">{{ __('Manage customers') }}</h2>
                    <p class="mb-0 text-gray-400 small">قاعدة بيانات المتسوقين والشركات</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-success-glass d-flex align-items-center gap-2 shadow-lg dropdown-toggle"
                        type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i>
                        <span class="fw-bold d-none d-md-inline">{{ __('Export') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark bg-slate-900 border-secondary border-opacity-10-10 shadow-neon"
                        aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('export.customers', ['format' => 'xlsx']) }}">
                                <i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('export.customers', ['format' => 'csv']) }}">
                                <i class="bi bi-file-earmark-code text-info"></i> CSV (.csv)
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('customers.import.form') }}"
                    class="btn btn-info-glass d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-cloud-upload"></i>
                    <span class="fw-bold d-none d-md-inline">{{ __('Import') }}</span>
                </a>
                <a href="{{ route('customers.create') }}"
                    class="btn btn-action-indigo d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-person-plus-fill"></i>
                    <span class="fw-bold">إضافة عميل جديد</span>
                </a>
            </div>
        </div>

        <!-- Filters Section (Glass) -->
        <div class="bg-slate-900 bg-opacity-50 border border-secondary border-opacity-10-5 rounded-4 p-4 mb-5">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-indigo-400 x-small fw-bold text-uppercase ps-1">{{ __('Search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                class="bi bi-search"></i></span>
                        <input type="text" name="search"
                            class="form-control form-control-dark border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-indigo"
                            value="{{ request('search') }}" placeholder="اسم العميل، الهاتف، أو العنوان...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-indigo-400 x-small fw-bold text-uppercase ps-1">{{ __('Status') }}</label>
                    <select name="active_only"
                        class="form-select form-select-dark text-body cursor-pointer hover:bg-surface-5">
                        <option value="0">-- الكل --</option>
                        <option value="1" {{ request('active_only') ? 'selected' : '' }}>نشط فقط</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-indigo-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i>{{ __('Filter') }}</button>
                </div>
            </form>
        </div>

        <!-- Customers Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-indigo">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">{{ __('Customer') }}</th>
                            <th>معلومات الاتصال</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('City') }}</th>
                            <th>{{ __('Credit Limit') }}</th>
                            <th class="pe-4 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr class="table-row-hover position-relative group-hover-actions">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-xs bg-indigo-500 rounded-circle text-body d-flex align-items-center justify-content-center fw-bold"
                                            style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="text-heading mb-0 fw-bold">{{ $customer->name }}</h6>
                                            @if($customer->is_blocked)
                                                <span class="badge bg-red-500 bg-opacity-20 text-red-400 x-small px-2">موقوف</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if($customer->phone)
                                            <span class="x-small text-gray-400"><i
                                                    class="bi bi-telephone text-indigo-400 me-2"></i>{{ $customer->phone }}</span>
                                        @endif
                                        @if($customer->mobile)
                                            <span class="x-small text-gray-400"><i
                                                    class="bi bi-phone text-indigo-400 me-2"></i>{{ $customer->mobile }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'consumer' => 'فرد (Consumer)',
                                            'company' => 'شركة (Company)',
                                            'distributor' => 'موزع معتمد (Distributor)',
                                            'wholesale' => 'تاجر جملة (Wholesale)',
                                            'half_wholesale' => 'نص جملة (Half Wholesale)',
                                            'quarter_wholesale' => 'ربع جملة (Quarter Wholesale)',
                                            'technician' => 'فني / مقاول (Technician)',
                                            'employee' => 'موظف (Employee)',
                                            'vip' => 'عميل مميز (VIP)'
                                        ];
                                        $label = $typeLabels[$customer->type] ?? $customer->type;
                                        $badgeClass = match ($customer->type) {
                                            'company' => 'bg-blue-500 text-blue-400 border-blue-500',
                                            'distributor' => 'bg-purple-500 text-purple-400 border-purple-500',
                                            'wholesale' => 'bg-orange-500 text-orange-400 border-orange-500',
                                            'vip' => 'bg-amber-500 text-amber-400 border-amber-500',
                                            default => 'bg-gray-500 text-gray-400 border-gray-500'
                                        };
                                    @endphp
                                    <span
                                        class="badge {{ $badgeClass }} bg-opacity-10 border border-opacity-20">{{ $label }}</span>
                                </td>
                                <td class="text-gray-400">{{ $customer->billing_city ?? '-' }}</td>
                                <td>
                                    <span class="fw-bold text-body">{{ number_format($customer->credit_limit, 2) }}</span>
                                    <span class="x-small text-gray-500">EGP</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 opacity-0 group-hover-visible transition-all">
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn-icon-glass"
                                            title="ملف العميل">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.statement', $customer->id) }}" class="btn-icon-glass"
                                            title="{{ __('Account Statement') }}">
                                            <i class="bi bi-file-spreadsheet"></i>
                                        </a>
                                        <a href="{{ route('customer-payments.create', ['customer_id' => $customer->id]) }}"
                                            class="btn-icon-glass text-success hover-success" title="تحصيل دفعة">
                                            <i class="bi bi-cash-stack"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn-icon-glass"
                                            title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
                                            class="d-inline" data-confirm="هل أنت متأكد من حذف هذا العميل؟">
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
                                        <i class="bi bi-people fs-1 text-gray-500 mb-3"></i>
                                        <h5 class="text-gray-400">{{ __('No customers found') }}</h5>
                                        <p class="text-gray-600 small">ابدأ بإضافة عملاء لقاعدة البيانات</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="p-4 border-top border-secondary border-opacity-10-5">
                    {{ $customers->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Indigo Theme */
        .text-indigo-400 {
            color: #818cf8 !important;
        }

        .bg-indigo-500 {
            background: #6366f1 !important;
        }

        .bg-gradient-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .shadow-neon-indigo {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .btn-action-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-indigo:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-indigo-glass {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(129, 140, 248, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-indigo-glass:hover {
            background: rgba(99, 102, 241, 0.25);
            color: var(--text-primary);
            border-color: #818cf8;
        }

        .focus-ring-indigo:focus {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.1) !important;
        }

        .border-top-gradient-indigo {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #6366f1, #818cf8) 1;
        }

        /* Common Glass Styles */
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            backdrop-filter: blur(12px);
        }

        .form-control-dark,
        .form-select-dark {
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
            background: var(--btn-glass-bg);
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

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: var(--btn-glass-bg);
            color: #cbd5e1;
            transition: 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        .hover-danger:hover {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #ef4444 !important;
        }
    </style>
@endsection