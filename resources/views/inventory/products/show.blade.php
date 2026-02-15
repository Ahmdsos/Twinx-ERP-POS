@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <a href="{{ route('products.index') }}" class="btn btn-icon-glass shadow-none border-secondary border-opacity-10-5">
                    <i class="bi bi-arrow-right fs-4"></i>
                </a>
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="fw-bold text-heading mb-0 tracking-wide">{{ $product->name }}</h2>
                        @if($product->is_active)
                            <span
                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20">{{ __('Active') }}</span>
                        @else
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20">{{ __('Disabled') }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-1 text-secondary font-monospace small">
                        <span><i class="bi bi-barcode me-1"></i>{{ $product->sku }}</span>
                        <span>|</span>
                        <span>{{ $product->type->label() }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('barcode.print', $product->id) }}" target="_blank"
                    class="btn btn-outline-purple d-flex align-items-center gap-2">
                    <i class="bi bi-upc"></i> طباعة باركود
                </a>
                <a href="{{ route('products.edit', $product->id) }}"
                    class="btn btn-outline-purple d-flex align-items-center gap-2">
                    <i class="bi bi-pencil"></i>{{ __('Edit') }}</a>
                <a href="{{ route('stock.adjust') }}?product_id={{ $product->id }}"
                    class="btn btn-action-purple d-flex align-items-center gap-2 shadow-neon-purple">
                    <i class="bi bi-sliders"></i>{{ __('Stock Adjustment') }}</a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Details (Left/Right depending on RTL) -->
            <div class="col-lg-8">
                <!-- Key Metrics Cards -->
                <div class="row g-4 mb-4">
                    <!-- Selling Price -->
                    <div class="col-md-4">
                        <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                            <div class="position-absolute top-0 end-0 m-3 text-success opacity-25">
                                <i class="bi bi-tag-fill fs-1"></i>
                            </div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-2">{{ __('Selling Price') }}</h6>
                            <h3 class="text-success fw-bold mb-0">{{ number_format($product->selling_price, 2) }} <small
                                    class="fs-6 text-body text-opacity-50">EGP</small></h3>
                        </div>
                    </div>
                    <!-- Cost Price -->
                    <div class="col-md-4">
                        <div class="glass-panel p-4 position-relative overflow-hidden h-100">
                            <div class="position-absolute top-0 end-0 m-3 text-purple-400 opacity-25">
                                <i class="bi bi-currency-dollar fs-1"></i>
                            </div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-2">{{ __('Cost Price') }}</h6>
                            <h3 class="text-heading fw-bold mb-0">{{ number_format($product->cost_price, 2) }} <small
                                    class="fs-6 text-body text-opacity-50">EGP</small></h3>
                        </div>
                    </div>
                    <!-- Total Stock -->
                    <div class="col-md-4">
                        <div class="glass-panel p-4 position-relative overflow-hidden h-100 border-top-gradient-purple">
                            <div class="position-absolute top-0 end-0 m-3 text-cyan-400 opacity-25">
                                <i class="bi bi-boxes fs-1"></i>
                            </div>
                            <h6 class="text-secondary text-uppercase small fw-bold mb-2">إجمالي المخزون</h6>
                            <h3 class="text-cyan-400 fw-bold mb-0">{{ number_format($product->total_stock, 2) }} <small
                                    class="fs-6 text-secondary">{{ $product->unit->name }}</small></h3>
                        </div>
                    </div>
                </div>

                <!-- Detailed Info Tabs -->
                <div class="glass-panel p-4">
                    <ul class="nav nav-pills custom-pills mb-4 pb-3 border-bottom border-secondary border-opacity-10-5" id="pills-tab"
                        role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-info-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-info" type="button">معلومات عامة</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-stock-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-stock" type="button">توزيع المخزون</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-movements-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-movements" type="button">سجل الحركات</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <!-- General Info -->
                        <div class="tab-pane fade show active" id="pills-info">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="bg-surface bg-opacity-30 rounded-3 p-3 border border-secondary border-opacity-10-5">
                                        <span class="d-block text-gray-500 x-small text-uppercase mb-1">{{ __('Barcode') }}</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-upc-scan text-purple-400"></i>
                                            <span class="text-body font-monospace">{{ $product->barcode ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-surface bg-opacity-30 rounded-3 p-3 border border-secondary border-opacity-10-5">
                                        <span class="d-block text-gray-500 x-small text-uppercase mb-1">الماركة</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-award text-purple-400"></i>
                                            <span
                                                class="text-body fw-bold">{{ $product->brand->name ?? 'غير محدد' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-surface bg-opacity-30 rounded-3 p-3 border border-secondary border-opacity-10-5">
                                        <span class="d-block text-gray-500 x-small text-uppercase mb-1">التصنيف</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-tag text-purple-400"></i>
                                            <span class="text-body">{{ $product->category->name ?? 'غير مصنف' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-surface bg-opacity-30 rounded-3 p-3 border border-secondary border-opacity-10-5">
                                        <span class="d-block text-gray-500 x-small text-uppercase mb-2">{{ __('Description') }}</span>
                                        <p class="text-secondary mb-0 leading-relaxed">
                                            {{ $product->description ?? 'لا يوجد وصف للمنتج.' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Attributes -->
                                <div class="col-12">
                                    <h6 class="text-heading fw-bold mb-3 mt-2">المواصفات الفنية</h6>
                                    <div class="table-responsive">
                                        <table class="table table-dark-custom table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="text-gray-500 w-25">{{ __('Brand') }}</td>
                                                    <td class="text-body">{{ $product->brand->name ?? '-' }}</td>
                                                    <td class="text-gray-500 w-25">الضمان</td>
                                                    <td class="text-body">
                                                        {{ $product->warranty_months ? $product->warranty_months . ' شهر' : '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-gray-500">الأبعاد</td>
                                                    <td class="text-body" colspan="3">
                                                        @if($product->length || $product->width || $product->height)
                                                            {{ $product->length ?? 0 }} x {{ $product->width ?? 0 }} x
                                                            {{ $product->height ?? 0 }} cm
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Distribution -->
                        <div class="tab-pane fade" id="pills-stock">
                            <div class="table-responsive">
                                <table class="table table-dark-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>المستودع</th>
                                            <th>الكمية المتوفرة</th>
                                            <th>متوسط التكلفة</th>
                                            <th>القيمة الإجمالية</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($product->stock as $stock)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="icon-circle-xs bg-purple-500 bg-opacity-20 text-purple-400">
                                                            <i class="bi bi-building"></i>
                                                        </div>
                                                        <span class="text-body fw-bold">{{ $stock->warehouse->name }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="fw-bold {{ $stock->quantity <= 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($stock->quantity, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-secondary font-monospace">
                                                    {{ number_format($stock->average_cost, 2) }}
                                                </td>
                                                <td class="text-body font-monospace">
                                                    {{ number_format($stock->quantity * $stock->average_cost, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Movements (Preview) -->
                        <div class="tab-pane fade" id="pills-movements">
                            <div class="text-center py-4">
                                <i class="bi bi-clock-history fs-1 text-gray-600 mb-3 opacity-50"></i>
                                <p class="text-secondary">لمشاهدة سجل الحركات التفصيلي، انتقل إلى قسم الحركات.</p>
                                <a href="{{ route('stock.index') }}?product_id={{ $product->id }}"
                                    class="btn btn-outline-purple btn-sm">عرض كل الحركات</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <!-- Product Image -->
                <div class="glass-panel p-2 mb-4 text-center">
                    <div class="rounded-3 overflow-hidden position-relative" style="height: 300px;">
                        @if($product->primary_image_url)
                            <img src="{{ $product->primary_image_url }}" alt="Product Image"
                                class="w-100 h-100 object-fit-cover">
                        @else
                            <div
                                class="w-100 h-100 bg-surface d-flex flex-column align-items-center justify-content-center text-gray-600">
                                <i class="bi bi-image display-1 mb-3 opacity-30"></i>
                                <span>لا توجد صورة</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Settings Summary -->
                <div class="glass-panel p-4">
                    <h6 class="text-heading fw-bold mb-3 border-bottom border-secondary border-opacity-10-5 pb-2">{{ __('Settings') }}</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">قابل للبيع</span>
                            @if($product->is_sellable)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">قابل للشراء</span>
                            @if($product->is_purchasable)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">السعر شامل الضريبة</span>
                            @if($product->is_tax_inclusive)
                                <span class="badge bg-purple-500 bg-opacity-20 text-purple-300">{{ __('Yes') }}</span>
                            @else
                                <span class="badge bg-gray-700 text-secondary">لا (+{{ $product->tax_rate }}%)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Scoped Styles for Product Show */
        .glass-panel {
            background: var(--glass-bg);
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(12px);
            border-radius: 16px;
        }

        .btn-icon-glass {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--btn-glass-bg);
            color: var(--text-secondary);
            border-radius: 12px;
            border: 1px solid var(--btn-glass-border);
            transition: all 0.2s;
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            transform: translateX(-2px);
        }

        .btn-outline-purple {
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #d8b4fe;
            background: rgba(168, 85, 247, 0.05);
        }

        .btn-outline-purple:hover {
            background: rgba(168, 85, 247, 0.1);
            color: var(--text-primary);
            border-color: #a855f7;
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            border: none;
            color: var(--text-primary);
            transition: all 0.3s;
        }

        .btn-action-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.5);
        }

        .custom-pills .nav-link {
            color: var(--text-secondary);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .custom-pills .nav-link.active {
            background: rgba(168, 85, 247, 0.1);
            color: #d8b4fe;
            border: 1px solid rgba(168, 85, 247, 0.2);
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: var(--text-body);
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
        }

        .table-dark-custom td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .bg-purple-500 {
            background-color: #a855f7 !important;
        }

        .icon-circle-xs {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .border-top-gradient-purple {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #a855f7, #c084fc) 1;
        }
    </style>
@endsection