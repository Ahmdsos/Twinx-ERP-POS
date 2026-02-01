@extends('layouts.app')

@section('title', 'إنشاء أمر شراء')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-white mb-0">أمر شراء جديد</h2>
                    <p class="text-gray-400 mb-0 x-small">إصدار طلب شراء رسمي لمورد</p>
                </div>
            </div>
            <button type="submit" form="orderForm"
                class="btn btn-action-blue fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-save"></i> حفظ الأمر (Draft)
            </button>
        </div>

        <form action="{{ route('purchase-orders.store') }}" method="POST" id="orderForm">
            @csrf

            <div class="row g-4">
                <!-- Main Form (Left) -->
                <div class="col-md-9">
                    <!-- Products Section -->
                    <div class="glass-panel p-4 mb-4" style="min-height: 500px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-blue-400 fw-bold mb-0"><i class="bi bi-basket me-2"></i>الأصناف المطلوبة</h5>
                            <button type="button" class="btn btn-sm btn-outline-blue" onclick="addNewRow()">
                                <i class="bi bi-plus-lg me-1"></i> إضافة صنف
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark-custom align-middle" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">المنتج</th>
                                        <th style="width: 15%">الكمية</th>
                                        <th style="width: 20%">سعر الوحدة</th>
                                        <th style="width: 20%">الإجمالي</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-dashed-blue w-100 mt-3 p-3" onclick="addNewRow()">
                            <i class="bi bi-plus-circle me-2"></i>اضغط لإضافة منتج جديد
                        </button>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="col-md-3">
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="text-white fw-bold mb-4 border-bottom border-white-5 pb-2">بيانات الطلب</h5>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">المورد <span
                                    class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select form-select-dark focus-ring-blue" required>
                                <option value="">-- اختر المورد --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">المخزن المستهدف <span
                                    class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select form-select-dark focus-ring-blue" required>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">تاريخ الطلب <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control form-control-dark focus-ring-blue"
                                value="{{ old('order_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">تاريخ التوقع</label>
                            <input type="date" name="expected_date" class="form-control form-control-dark focus-ring-blue"
                                value="{{ old('expected_date') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">المرجع (اختياري)</label>
                            <input type="text" name="reference" class="form-control form-control-dark focus-ring-blue"
                                placeholder="REF-...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">ملاحظات</label>
                            <textarea name="notes" class="form-control form-control-dark focus-ring-blue"
                                rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Totals Panel -->
                    <div class="glass-panel p-4 bg-gradient-to-br from-slate-900 to-slate-800">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-400 small">الإجمالي</span>
                            <span class="text-white fw-bold" id="totalDisplay">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- JavaScript similar to Invoices -->
    <script>
        const products = @json($products);
        let rowCount = 0;

        function addNewRow() {
            const tbody = document.querySelector('#itemsTable tbody');
            const index = rowCount++;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                    <td>
                        <select name="lines[${index}][product_id]" class="form-select form-select-dark product-select" required onchange="updatePrice(this)">
                            <option value="">اختر المنتج...</option>
                            ${products.map(p => `<option value="${p.id}" data-price="${p.cost_price}">${p.name} (${p.sku})</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="lines[${index}][quantity]" class="form-control form-control-dark text-center qty-input" 
                            value="1" min="1" step="any" required oninput="calculateRow(this)">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="lines[${index}][unit_price]" class="form-control form-control-dark text-center price-input" 
                            value="0.00" min="0" required oninput="calculateRow(this)">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-dark text-center total-input" value="0.00" readonly>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-icon-glass text-danger hover-danger" onclick="removeRow(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
            tbody.appendChild(tr);
        }

        function updatePrice(select) {
            const option = select.options[select.selectedIndex];
            const price = option.dataset.price || 0;
            const row = select.closest('tr');
            row.querySelector('.price-input').value = price;
            calculateRow(select);
        }

        function calculateRow(element) {
            const row = element.closest('tr');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const total = qty * price;

            row.querySelector('.total-input').value = total.toFixed(2);
            calculateGrandTotal();
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.total-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('totalDisplay').innerText = total.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', addNewRow);
    </script>

    <style>
        .btn-action-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
        }

        .btn-dashed-blue {
            border: 2px dashed rgba(59, 130, 246, 0.3);
            color: #3b82f6;
            border-radius: 12px;
            transition: 0.2s;
            background: rgba(59, 130, 246, 0.05);
        }

        .btn-dashed-blue:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            color: white;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .focus-ring-blue:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            transition: 0.2s;
        }

        .hover-danger:hover {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #ef4444 !important;
        }
    </style>
@endsection