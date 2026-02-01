@extends('layouts.app')

@section('title', 'تحويل مخزون')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center icon-box-lg mb-3 shadow-neon-blue">
                        <i class="bi bi-arrow-left-right fs-2 text-white"></i>
                    </div>
                    <h3 class="fw-bold text-white tracking-wide">تحويل بين المخازن</h3>
                    <p class="text-gray-400">نقل البضائع من مستودع لآخر مع توثيق الحركة</p>
                </div>

                <!-- Glass Card Content -->
                <div class="glass-panel p-5 position-relative overflow-hidden">
                    <div class="glow-orb bg-blue-500 opacity-10" style="top: -50px; right: 50%;"></div>

                    <form action="{{ route('stock.transfer.process') }}" method="POST" id="transferForm">
                        @csrf

                        <div class="row g-4 align-items-center mb-5">
                            <!-- From Warehouse -->
                            <div class="col-md-5">
                                <div
                                    class="p-4 rounded-4 bg-slate-900 bg-opacity-50 border border-white-5 position-relative">
                                    <span
                                        class="position-absolute top-0 start-50 translate-middle badge bg-danger text-white px-3 py-2 rounded-pill border border-danger shadow-neon-sm">من
                                        المستودع (المصدر)</span>
                                    <div class="mt-3">
                                        <select name="from_warehouse_id" id="fromWarehouse"
                                            class="form-select form-select-dark text-center py-3 fs-5 fw-bold text-white cursor-pointer"
                                            required>
                                            <option value="" selected disabled>-- اختر المصدر --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Direction Icon -->
                            <div class="col-md-2 text-center">
                                <div
                                    class="d-inline-flex align-items-center justify-content-center icon-circle-md bg-white bg-opacity-5 text-gray-400 border border-white-10">
                                    <i class="bi bi-arrow-left display-6"></i>
                                </div>
                            </div>

                            <!-- To Warehouse -->
                            <div class="col-md-5">
                                <div
                                    class="p-4 rounded-4 bg-slate-900 bg-opacity-50 border border-white-5 position-relative">
                                    <span
                                        class="position-absolute top-0 start-50 translate-middle badge bg-success text-white px-3 py-2 rounded-pill border border-success shadow-neon-sm">إلى
                                        المستودع (الوجهة)</span>
                                    <div class="mt-3">
                                        <select name="to_warehouse_id" id="toWarehouse"
                                            class="form-select form-select-dark text-center py-3 fs-5 fw-bold text-white cursor-pointer"
                                            required>
                                            <option value="" selected disabled>-- اختر الوجهة --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label
                                    class="form-label text-blue-400 small fw-bold text-uppercase tracking-wider ps-1">المنتج
                                    المراد نقله <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-box-seam"></i></span>
                                    <select name="product_id" id="productSelect"
                                        class="form-select form-select-dark border-start-0 ps-0 text-white cursor-pointer"
                                        required>
                                        <option value="" selected disabled>-- اختر المنتج --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label text-blue-400 small fw-bold text-uppercase tracking-wider ps-1">الكمية
                                    <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark-input border-end-0 text-gray-500"><i
                                            class="bi bi-123"></i></span>
                                    <input type="number" step="0.01" name="quantity" id="transferQty"
                                        class="form-control form-control-dark border-start-0 ps-0 text-white placeholder-gray-600 focus-ring-blue fw-bold"
                                        placeholder="0.00" required>
                                </div>
                                <div class="form-text text-gray-500 ms-1" id="maxQtyText">أقصى حد مسموح: <span
                                        id="maxQtyVal">-</span></div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-blue-400 small fw-bold text-uppercase tracking-wider ps-1">رقم
                                    مرجعي (اختياري)</label>
                                <input type="text" name="reference"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-blue"
                                    placeholder="REF-001">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label text-blue-400 small fw-bold text-uppercase tracking-wider ps-1">ملاحظات</label>
                                <input type="text" name="notes"
                                    class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-blue"
                                    placeholder="سبب التحويل...">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top border-white-10">
                            <a href="{{ route('stock.index') }}"
                                class="btn btn-link text-gray-400 text-decoration-none hover-text-white d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right"></i> إلغاء
                            </a>
                            <button type="submit"
                                class="btn btn-action-blue px-5 py-2 rounded-pill fw-bold shadow-neon-blue d-flex align-items-center gap-2">
                                <i class="bi bi-send"></i> تنفيذ التحويل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fromWarehouse = document.getElementById('fromWarehouse');
            const toWarehouse = document.getElementById('toWarehouse');
            const productSelect = document.getElementById('productSelect');
            const maxQtyText = document.getElementById('maxQtyText');
            const maxQtyVal = document.getElementById('maxQtyVal');
            const transferQty = document.getElementById('transferQty');

            let availableStock = 0;

            // Prevent selecting same warehouse
            function validateWarehouses() {
                if (fromWarehouse.value === toWarehouse.value && fromWarehouse.value !== "") {
                    alert('لا يمكن التحويل لنفس المستودع!');
                    toWarehouse.value = "";
                }
                if (fromWarehouse.value && productSelect.value) {
                    fetchSourceStock();
                }
            }

            fromWarehouse.addEventListener('change', validateWarehouses);
            toWarehouse.addEventListener('change', validateWarehouses);
            productSelect.addEventListener('change', fetchSourceStock);

            async function fetchSourceStock() {
                const warehouseId = fromWarehouse.value;
                const productId = productSelect.value;

                if (!warehouseId || !productId) return;

                try {
                    const response = await fetch(`{{ route('stock.get-stock') }}?warehouse_id=${warehouseId}&product_id=${productId}`);
                    const data = await response.json();

                    availableStock = parseFloat(data.available);
                    maxQtyVal.textContent = availableStock;

                    if (availableStock <= 0) {
                        maxQtyVal.classList.add('text-danger');
                        maxQtyVal.classList.remove('text-success');
                        transferQty.disabled = true;
                        transferQty.placeholder = "غير متوفر";
                    } else {
                        maxQtyVal.classList.add('text-success');
                        maxQtyVal.classList.remove('text-danger');
                        transferQty.disabled = false;
                        transferQty.placeholder = "0.00";
                        transferQty.max = availableStock;
                    }
                } catch (error) {
                    console.error('Error fetching source stock:', error);
                }
            }
        });
    </script>

    <style>
        /* Scoped Styles for Transfer Form (Blue Theme) */
        .icon-box-lg {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(30, 41, 59, 0.5));
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.15);
        }

        .icon-circle-md {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 0.8rem 1rem;
        }

        .form-control-dark:focus,
        .form-select-dark:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .btn-action-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-action-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
        }

        .placeholder-gray-600::placeholder {
            color: #475569;
        }

        .bg-white-5 {
            background: rgba(255, 255, 255, 0.02);
        }

        .border-white-5 {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        .border-white-10 {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        .x-small {
            font-size: 0.75rem;
        }

        .text-blue-400 {
            color: #60a5fa !important;
        }

        .bg-blue-500 {
            background-color: #3b82f6 !important;
        }

        .shadow-neon-blue {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }
    </style>
@endsection