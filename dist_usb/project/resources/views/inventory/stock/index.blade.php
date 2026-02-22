@extends('layouts.app')

@section('title', 'سجل حركات المخزون')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-cyan shadow-neon">
                    <i class="bi bi-clock-history fs-3 text-body"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-1 tracking-wide">سجل حركات المخزون</h2>
                    <p class="mb-0 text-secondary small">تتبع شامل لكافة عمليات الدخول والخروج</p>
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('stock.adjust') }}" class="btn btn-outline-cyan d-flex align-items-center gap-2">
                    <i class="bi bi-sliders"></i>
                    <span class="d-none d-md-block">تسوية يدوية</span>
                </a>
                <a href="{{ route('stock.transfer') }}"
                    class="btn btn-action-cyan d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-arrow-left-right"></i>
                    <span class="fw-bold">تحويل مخزون</span>
                </a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-surface bg-opacity-50 border border-secondary border-opacity-10-5 rounded-4 p-4 mb-5">
            <form action="{{ route('stock.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">التصنيف بالحركة</label>
                    <select name="type" class="form-select form-select text-body cursor-pointer hover:bg-surface-5">
                        <option value="">-- كل الحركات --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">المستودع</label>
                    <select name="warehouse_id"
                        class="form-select form-select text-body cursor-pointer hover:bg-surface-5">
                        <option value="">-- كل المستودعات --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-cyan-400 x-small fw-bold text-uppercase ps-1">{{ __('Product') }}</label>
                    <select name="product_id"
                        class="form-select form-select text-body cursor-pointer hover:bg-surface-5">
                        <option value="">-- كل المنتجات --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-cyan-glass w-100 fw-bold">
                        <i class="bi bi-funnel"></i> تصفية النتائج
                    </button>
                </div>
            </form>
        </div>

        <!-- Movements Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-cyan">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">التاريخ / المرجع</th>
                            <th>نوع الحركة</th>
                            <th>{{ __('Product') }}</th>
                            <th>المستودع</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Cost') }}</th>
                            <th class="pe-4">{{ __('User') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr class="table-row-hover">
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-body fw-bold">{{ $movement->movement_date->format('Y-m-d') }}</span>
                                        <span
                                            class="text-gray-500 x-small font-monospace">{{ $movement->movement_date->format('h:i A') }}</span>
                                        @if($movement->reference)
                                            <span
                                                class="text-xs text-cyan-400 opacity-75 mt-1 font-monospace">{{ $movement->reference }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match ($movement->type->value) {
                                            'purchase' => 'success',
                                            'sale' => 'primary',
                                            'return_in' => 'info',
                                            'return_out' => 'warning',
                                            'adjustment_in', 'adjustment_out' => 'danger',
                                            'transfer_in', 'transfer_out' => 'purple',
                                            default => 'secondary'
                                        };

                                        // Map colors to hex for premium look
                                        $colorMap = [
                                            'success' => ['bg' => '#10b981', 'text' => '#34d399'],
                                            'primary' => ['bg' => '#3b82f6', 'text' => '#60a5fa'],
                                            'info' => ['bg' => '#06b6d4', 'text' => '#22d3ee'],
                                            'warning' => ['bg' => '#f59e0b', 'text' => '#fbbf24'],
                                            'danger' => ['bg' => '#ef4444', 'text' => '#f87171'],
                                            'purple' => ['bg' => '#a855f7', 'text' => '#c084fc'],
                                            'secondary' => ['bg' => '#64748b', 'text' => '#94a3b8'],
                                        ];
                                        $theme = $colorMap[$badgeColor];
                                    @endphp
                                    <span class="badge border fw-normal px-2 py-1"
                                        style="background: {{ $theme['bg'] }}20; color: {{ $theme['text'] }}; border-color: {{ $theme['bg'] }}40 !important;">
                                        {{ $movement->type->label() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-product-sm">
                                            @if($movement->product && $movement->product->image_url)
                                                <img src="{{ $movement->product->image_url }}" alt="Prod"
                                                    class="rounded-2 w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-box text-gray-500 x-small"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-body small fw-bold">{{ $movement->product->name ?? 'محذوف' }}</div>
                                            <div class="text-gray-500 x-small font-monospace">
                                                {{ $movement->product->sku ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $movement->warehouse->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i
                                            class="bi {{ $movement->quantity > 0 ? 'bi-arrow-up-right text-success' : 'bi-arrow-down-left text-danger' }}"></i>
                                        <span class="fw-bold {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}"
                                            style="direction: ltr;">
                                            {{ abs($movement->quantity) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-secondary small">{{ number_format(abs($movement->total_cost), 2) }}</div>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-circle-xs bg-surface bg-opacity-10 text-secondary">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <span class="text-secondary x-small">{{ $movement->creator->name ?? 'System' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state opacity-50">
                                        <i class="bi bi-clock-history display-4 text-gray-600 mb-3"></i>
                                        <p class="text-secondary">لا توجد حركات مخزون مسجلة بعد</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-top border-secondary border-opacity-10-10">
                {{ $movements->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <style>
        /* Premium Theme Variables (Cyan Variant) */
        /* :root override removed for theme compatibility */

        .icon-box {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            border: 1px solid var(--btn-glass-border);
        }

        .bg-gradient-cyan {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(8, 145, 178, 0.2) 100%);
            border-color: rgba(6, 182, 212, 0.3);
        }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: var(--text-primary);
            transition: all 0.3s;
            box-shadow: var(--cyan-glow);
        }

        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.5);
        }

        .btn-outline-cyan {
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #22d3ee;
            background: rgba(6, 182, 212, 0.05);
        }

        .btn-outline-cyan:hover {
            background: rgba(6, 182, 212, 0.1);
            color: #fff;
            border-color: #22d3ee;
        }

        .btn-cyan-glass {
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.2);
            color: #22d3ee;
            transition: all 0.3s;
        }

        .btn-cyan-glass:hover {
            background: rgba(6, 182, 212, 0.2);
            color: var(--text-primary);
            transform: translateY(-1px);
        }

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 16px;
        }

        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border);
            !important;
            color: var(--text-primary);
            !important;
            padding: 0.6rem 1rem;
        }

        .form-select-dark:focus {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--glass-border);
            color: var(--text-body);
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            padding: 1rem;
        }

        .table-dark-custom td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .table-row-hover:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .avatar-product-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-circle-xs {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .x-small {
            font-size: 0.7rem;
        }

        .border-top-gradient-cyan {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #06b6d4, #22d3ee) 1;
        }
    </style>
@endsection