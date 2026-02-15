@extends('layouts.app')

@section('title', 'إنشاء إذن صرف مخزني')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-heading mb-0">إنشاء إذن صرف</h2>
                <p class="text-gray-400 mb-0">صرف منتجات من المخزن بناءً على أمر بيع</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-glass-outline rounded-pill px-4">
                <i class="bi bi-arrow-right me-2"></i> عودة
            </a>
        </div>

        <!-- Error Handling -->
        @if ($errors->any())
            <div class="alert alert-danger border-0 bg-red-500/10 text-red-400 mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4" x-data="deliveryForm()">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="glass-panel p-4 rounded-4 border border-secondary border-opacity-10/10 shadow-lg">
                    <form action="{{ route('deliveries.store') }}" method="POST" id="delivery-form">
                        @csrf

                        @if(isset($salesOrder))
                            <input type="hidden" name="sales_order_id" value="{{ $salesOrder->id }}">

                            <div
                                class="d-flex align-items-center justify-content-between bg-surface/5 p-3 rounded mb-4 border border-secondary border-opacity-10/10">
                                <div>
                                    <label class="text-gray-500 small d-block">أمر البيع</label>
                                    <span class="fw-bold text-body fs-5">{{ $salesOrder->so_number }}</span>
                                </div>
                                <div>
                                    <label class="text-gray-500 small d-block">{{ __('Customer') }}</label>
                                    <span class="fw-bold text-body">{{ $salesOrder->customer->name }}</span>
                                </div>
                                <div>
                                    <label class="text-gray-500 small d-block">{{ __('Date') }}</label>
                                    <span class="text-body">{{ $salesOrder->order_date->format('Y-m-d') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="form-label text-gray-300">اختر أمر البيع</label>
                                <select name="sales_order_id" class="form-select glass-input-lg"
                                    onchange="window.location.href='{{ route('deliveries.create') }}?sales_order_id=' + this.value">
                                    <option value="">-- اختر أمر بيع معتمد --</option>
                                    @foreach($salesOrders as $so)
                                        <option value="{{ $so->id }}">{{ $so->so_number }} - {{ $so->customer->name }}
                                            ({{ $so->order_date->format('Y-m-d') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-gray-300">تاريخ الصرف <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control glass-input"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-300">المخزن <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select glass-input" required>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ (isset($salesOrder) && $salesOrder->warehouse_id == $warehouse->id) ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if(isset($salesOrder))
                            <div class="mb-4">
                                <h5 class="text-heading fw-bold mb-3 border-bottom border-secondary border-opacity-10/10 pb-2">المنتجات المطلوب صرفها
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0">
                                        <thead class="bg-surface/5 text-gray-400 small">
                                            <tr>
                                                <th class="ps-3 py-2 rounded-start">{{ __('Product') }}</th>
                                                <th class="text-center py-2">المطلوب</th>
                                                <th class="text-center py-2">تم صرفه</th>
                                                <th class="text-center py-2 bg-blue-500/10 text-blue-300">الكمية الحالية</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($salesOrder->lines as $line)
                                                @php
                                                    $remaining = $line->quantity - $line->delivered_quantity;
                                                @endphp
                                                @if($remaining > 0)
                                                    <tr class="border-bottom border-secondary border-opacity-10/5">
                                                        <td class="ps-3 py-3">
                                                            <input type="hidden" name="lines[{{ $loop->index }}][sales_order_line_id]"
                                                                value="{{ $line->id }}">
                                                            <div class="fw-bold text-body">{{ $line->product->name }}</div>
                                                            <small class="text-gray-500">{{ $line->product->code }}</small>
                                                        </td>
                                                        <td class="text-center"><span
                                                                class="badge bg-surface/10">{{ $line->quantity + 0 }}</span></td>
                                                        <td class="text-center"><span
                                                                class="badge bg-success/20 text-success">{{ $line->delivered_quantity + 0 }}</span>
                                                        </td>
                                                        <td class="text-center bg-blue-500/5">
                                                            <input type="number" name="lines[{{ $loop->index }}][quantity]"
                                                                class="form-control glass-input text-center mx-auto"
                                                                style="width: 100px;" value="{{ $remaining }}" min="0"
                                                                max="{{ $remaining }}" step="0.01">
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 text-gray-500">
                                <i class="bi bi-cart-x fs-1 opacity-50 d-block mb-3"></i>
                                يرجى اختيار أمر بيع لعرض المنتجات
                            </div>
                        @endif

                        <div class="row g-3 mb-4 border-top border-secondary border-opacity-10/10 pt-4">
                            <div class="col-md-6">
                                <label class="form-label text-gray-300">اسم السائق (اختياري)</label>
                                <input type="text" name="driver_name" class="form-control glass-input">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-300">رقم المركبة (اختياري)</label>
                                <input type="text" name="vehicle_number" class="form-control glass-input">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-300">شركة/طريقة الشحن</label>
                                <input type="text" name="shipping_method" class="form-control glass-input"
                                    value="{{ $salesOrder->shipping_method ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-300">{{ __('Address') }}</label>
                                <input type="text" name="shipping_address" class="form-control glass-input"
                                    value="{{ $salesOrder->shipping_address ?? ($salesOrder->customer->address ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-gray-300">{{ __('Notes') }}</label>
                                <textarea name="notes" class="form-control glass-input" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-glass-outline px-4">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success fw-bold px-5 shadow-lg hover-scale">
                                <i class="bi bi-check-lg me-2"></i> حفظ الإذن
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Instructions Sidebar -->
            <div class="col-lg-4">
                <div class="glass-panel p-4 rounded-4 border border-secondary border-opacity-10/10 shadow-lg">
                    <h5 class="fw-bold text-heading mb-4"><i class="bi bi-info-circle me-2 text-info"></i> تعليمات</h5>
                    <ul class="list-unstyled text-gray-300 small vstack gap-3">
                        <li class="d-flex gap-2">
                            <i class="bi bi-1-circle text-blue-400"></i>
                            <span>يتم إنشاء إذن الصرف للأوامر المعتمدة فقط.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-2-circle text-blue-400"></i>
                            <span>يمكن صرف الكمية بالكامل أو جزئياً (Partial Delivery).</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-3-circle text-blue-400"></i>
                            <span>عند حفظ الإذن، سيتم خصم الكميات من المخزن المحدد.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-4-circle text-blue-400"></i>
                            <span>حالة أمر البيع ستتحدث تلقائياً إلى "مسلم جزئياً" أو "مسلم" بناءً على الكميات.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deliveryForm() {
            return {
                // Future Alpine Logic
            }
        }
    </script>

    <style>
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
        }

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            color: var(--text-primary);
        }

        .btn-glass-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .glass-input,
        .glass-input-lg {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
        }

        .glass-input:focus,
        .glass-input-lg:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .glass-input-lg {
            font-size: 1.1rem;
            padding: 0.8rem;
        }
    </style>
@endsection