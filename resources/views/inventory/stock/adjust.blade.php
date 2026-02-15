@extends('layouts.app')

@section('title', 'تسوية المخزون')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center icon-box-lg mb-3 shadow-neon-cyan">
                        <i class="bi bi-sliders fs-2 text-body"></i>
                    </div>
                    <h3 class="fw-bold text-heading tracking-wide">تسوية المخزون اليدوية</h3>
                    <p class="text-secondary">تعديل كميات المخزون الحالية (عجز / زيادة)</p>
                </div>

                <!-- Glass Card Content -->
                <div class="glass-panel p-5 position-relative overflow-hidden">
                    <div class="glow-orb bg-cyan-500 opacity-10" style="top: -50px; left: -50px;"></div>

                    <form action="{{ route('stock.adjust.process') }}" method="POST" id="adjustForm">
                        @csrf

                        <div class="row g-4 mb-4">
                            <!-- Warehouse Select -->
                            <div class="col-md-6">
                                <label
                                    class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">المستودع
                                    <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-building"></i></span>
                                    <select name="warehouse_id" id="warehouseSelect"
                                        class="form-select form-select border-start-0 ps-0 text-body cursor-pointer"
                                        required>
                                        <option value="" selected disabled>-- اختر المستودع --</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Product Select -->
                            <div class="col-md-6">
                                <label
                                    class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">{{ __('Product') }}<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-box-seam"></i></span>
                                    <select name="product_id" id="productSelect"
                                        class="form-select form-select border-start-0 ps-0 text-body cursor-pointer"
                                        required>
                                        <option value="" selected disabled>-- اختر المنتج --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->cost_price }}">
                                                {{ $product->name }} ({{ $product->sku }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Current Stock Display (AJAX) -->
                        <div id="stockDisplay"
                            class="d-none mb-4 p-4 rounded-3 bg-surface bg-opacity-50 border border-secondary border-opacity-10-5 transition-all">
                            <div class="row text-center align-items-center">
                                <div class="col-4 border-end border-secondary border-opacity-10-10">
                                    <p class="text-gray-500 x-small text-uppercase mb-1">الكمية الحالية</p>
                                    <h4 class="fw-bold text-heading mb-0" id="currentQty">0</h4>
                                    <span class="text-gray-600 small" id="unitDisplay">-</span>
                                </div>
                                <div class="col-4 border-end border-secondary border-opacity-10-10">
                                    <p class="text-gray-500 x-small text-uppercase mb-1">المحجوز</p>
                                    <h4 class="fw-bold text-warning mb-0" id="reservedQty">0</h4>
                                </div>
                                <div class="col-4">
                                    <p class="text-gray-500 x-small text-uppercase mb-1">المتاح الفعلي</p>
                                    <h4 class="fw-bold text-emerald-400 mb-0" id="availableQty">0</h4>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label
                                    class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">الكمية
                                    الجديدة (الجرده) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-list-ol"></i></span>
                                    <input type="number" step="0.01" name="new_quantity" id="newQuantity"
                                        class="form-control form-control border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-cyan fw-bold fs-5"
                                        placeholder="0.00" required>
                                </div>
                                <div class="form-text text-gray-500 ms-1" id="diffText">أدخل الكمية الموجودة فعلياً في
                                    المخزن</div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">تحديث
                                    التكلفة (اختياري)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-surface-secondary-input border-end-0 text-gray-500"><i
                                            class="bi bi-currency-dollar"></i></span>
                                    <input type="number" step="0.01" name="new_unit_cost"
                                        class="form-control form-control border-start-0 ps-0 text-body placeholder-gray-600 focus-ring-cyan"
                                        placeholder="اترك فارغاً للإبقاء على التكلفة الحالية">
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label text-cyan-400 small fw-bold text-uppercase tracking-wider ps-1">سبب
                                التسوية</label>
                            <textarea name="reason"
                                class="form-control form-control text-body placeholder-gray-600 focus-ring-cyan"
                                rows="3" placeholder="مثال: جرد سنوي، تلف بضاعة، خطأ في الإدخال..." required></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top border-secondary border-opacity-10-10">
                            <a href="{{ route('stock.index') }}"
                                class="btn btn-link text-secondary text-decoration-none hover-text-white d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right"></i>{{ __('Cancel') }}</a>
                            <button type="submit"
                                class="btn btn-action-cyan px-5 py-2 rounded-pill fw-bold shadow-neon-cyan d-flex align-items-center gap-2">
                                <i class="bi bi-check-lg"></i> تأكيد التسوية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const warehouseSelect = document.getElementById('warehouseSelect');
            const productSelect = document.getElementById('productSelect');
            const stockDisplay = document.getElementById('stockDisplay');
            const currentQtyEl = document.getElementById('currentQty');
            const reservedQtyEl = document.getElementById('reservedQty');
            const availableQtyEl = document.getElementById('availableQty');
            const diffText = document.getElementById('diffText');
            const newQuantityInput = document.getElementById('newQuantity');

            let currentStock = 0;

            async function fetchStock() {
                const warehouseId = warehouseSelect.value;
                const productId = productSelect.value;

                if (warehouseId && productId) {
                    // Show loading state could be added here
                    try {
                        const response = await fetch(`{{ route('stock.get-stock') }}?warehouse_id=${warehouseId}&product_id=${productId}`);
                        const data = await response.json();

                        currentStock = parseFloat(data.quantity);
                        currentQtyEl.textContent = currentStock;
                        reservedQtyEl.textContent = data.reserved;
                        availableQtyEl.textContent = data.available;

                        stockDisplay.classList.remove('d-none');
                        stockDisplay.classList.add('d-block');
                    } catch (error) {
                        console.error('Error fetching stock:', error);
                    }
                } else {
                    stockDisplay.classList.add('d-none');
                }
            }

            warehouseSelect.addEventListener('change', fetchStock);
            productSelect.addEventListener('change', fetchStock);

            newQuantityInput.addEventListener('input', function () {
                const newQty = parseFloat(this.value);
                if (!isNaN(newQty)) {
                    const diff = newQty - currentStock;
                    const diffStr = diff > 0 ? `+${diff}` : `${diff}`;
                    const colorClass = diff >= 0 ? 'text-success' : 'text-danger';
                    const action = diff >= 0 ? 'زيادة' : 'عجز';

                    diffText.innerHTML = `<span class="${colorClass} fw-bold">${action} بمقدار ${diffStr}</span>`;
                } else {
                    diffText.innerText = 'أدخل الكمية الموجودة فعلياً في المخزن';
                }
            });
        });
    </script>

    <style>
        /* Scoped Styles for Adjust Form (Cyan Theme) */
        .icon-box-lg {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(30, 41, 59, 0.5));
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.15);
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-secondary);
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
            padding: 0.8rem 1rem;
        }

        .form-control-dark:focus,
        .form-select-dark:focus {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: var(--text-primary);
            transition: all 0.3s;
        }

        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.5);
        }

        .placeholder-gray-600::placeholder {
            color: #475569;
        }

        .bg-surface-5 {
            background: rgba(255, 255, 255, 0.02);
        }

        .border-secondary border-opacity-10-10 {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        .x-small {
            font-size: 0.75rem;
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .bg-cyan-500 {
            background-color: #06b6d4 !important;
        }

        .shadow-neon-cyan {
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
        }
    </style>
@endsection