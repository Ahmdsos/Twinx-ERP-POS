@extends('layouts.app')

@section('title', 'تسجيل فاتورة شراء')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">فاتورة شراء جديدة</h2>
                    <p class="text-gray-400 mb-0 x-small">تسجيل بضاعة واردة واستحقاق مالي</p>
                </div>
            </div>
            <button type="submit" form="invoiceForm" class="btn btn-action-cyan fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-save"></i>{{ __('Save Invoice') }}</button>
        </div>

        <form action="{{ route('purchase-invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            
            <div class="row g-4">
                <!-- Main Form (Left) -->
                <div class="col-md-9">
                    <!-- Products Section -->
                    <div class="glass-panel p-4 mb-4" style="min-height: 500px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-cyan-400 fw-bold mb-0"><i class="bi bi-basket me-2"></i>أصناف الفاتورة</h5>
                            <button type="button" class="btn btn-sm btn-outline-cyan" onclick="addNewRow()">
                                <i class="bi bi-plus-lg me-1"></i>{{ __('Add Item') }}</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-dark-custom align-middle" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">{{ __('Product') }}</th>
                                        <th style="width: 15%">{{ __('Quantity') }}</th>
                                        <th style="width: 20%">{{ __('Unit Price') }}</th>
                                        <th style="width: 20%">{{ __('Total') }}</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic Rows Here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="button" class="btn btn-dashed-cyan w-100 mt-3 p-3" onclick="addNewRow()">
                            <i class="bi bi-plus-circle me-2"></i>اضغط لإضافة منتج جديد
                        </button>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="col-md-3">
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="text-heading fw-bold mb-4 border-bottom border-secondary border-opacity-10-5 pb-2">بيانات الفاتورة</h5>
                        

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">المورد <span class="text-danger">*</span></label>
                            @if(isset($grn))
                                <input type="hidden" name="supplier_id" value="{{ $grn->supplier_id }}">
                                <input type="hidden" name="grn_id" value="{{ $grn->id }}">
                                <input type="text" class="form-control form-control-dark" value="{{ $grn->supplier->name }}" readonly>
                            @else
                                <select name="supplier_id" class="form-select form-select-dark focus-ring-cyan" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">المخزن المستلم <span class="text-danger">*</span></label>
                            @if(isset($grn))
                                <input type="hidden" name="warehouse_id" value="{{ $grn->warehouse_id }}">
                                <input type="text" class="form-control form-control-dark" value="{{ $grn->warehouse->name }}" readonly>
                            @else
                                <select name="warehouse_id" class="form-select form-select-dark focus-ring-cyan" required>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">{{ __('Invoice Date') }}<span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control form-control-dark focus-ring-cyan" 
                                value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <!-- ... (Other Date Fields Unchanged) ... -->
                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">تاريخ الاستحقاق <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control form-control-dark focus-ring-cyan" 
                                value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">رقم فاتورة المورد <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="supplier_invoice_number" id="supplierInvoiceInput" class="form-control form-control-dark focus-ring-cyan" required placeholder="مثال: INV-2024-001">
                                <button type="button" class="btn btn-outline-cyan" onclick="generateInvoiceNumber()">
                                    <i class="bi bi-magic"></i> توليد تلقائي
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 x-small fw-bold">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control form-control-dark focus-ring-cyan" rows="2">{{ isset($grn) ? 'فاتورة عن إذن استلام: ' . $grn->grn_number : '' }}</textarea>
                        </div>

                        <!-- ... (Payment Section Unchanged) ... -->
                        <div class="border-top border-secondary border-opacity-10-5 pt-3 mt-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="payNowCheck" onchange="togglePayment(this)">
                                <label class="form-check-label text-body small fw-bold" for="payNowCheck">دفع مبلغ فوري (مقدم)؟</label>
                            </div>

                            <div id="paymentSection" class="d-none">
                                <div class="mb-2">
                                    <label class="text-gray-500 x-small">المبلغ المدفوع</label>
                                    <input type="number" step="0.01" name="paid_amount" id="paidAmount" class="form-control form-control-sm form-control-dark" placeholder="0.00" oninput="updateRemaining()">
                                </div>
                                <div class="mb-2">
                                    <label class="text-gray-500 x-small">{{ __('Payment Method') }}</label>
                                    <select name="payment_method" class="form-select form-select-sm form-select-dark">
                                        <option value="cash">{{ __('Cash') }}</option>
                                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                        <option value="cheque">{{ __('Check') }}</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="text-gray-500 x-small">الخزينة / الحساب</label>
                                    <select name="payment_account_id" class="form-select form-select-sm form-select-dark">
                                        @foreach($paymentAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Totals ... -->
                    <div class="glass-panel p-4 bg-gradient-to-br from-slate-900 to-slate-800">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-400 small">الإجمالي قبل الضريبة</span>
                            <span class="text-body fw-bold" id="subtotalDisplay">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-gray-400 small">الضريبة (0%)</span>
                            <span class="text-body fw-bold">0.00</span>
                        </div>
                        <div class="border-top border-secondary border-opacity-10-10 pt-3 mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-cyan-400 fw-bold fs-5">الإجمالي النهائي</span>
                                <span class="text-body fw-bold fs-4" id="totalDisplay">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Product Data for JS -->
    <script>
        const products = @json($products);
        // Load GRN items if enabled
        const grnItems = @json(isset($grn) ? $grn->lines : []);
    </script>

    <script>
        let rowCount = 0;

        function addNewRow(item = null) {
            const tbody = document.querySelector('#itemsTable tbody');
            const index = rowCount++;
            
            const productId = item ? item.product_id : '';
            const quantity = item ? item.quantity : 1;
            const price = item && item.product && item.product.cost_price ? item.product.cost_price : 0; // Default to cost price
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="items[${index}][product_id]" class="form-select form-select-dark product-select" required onchange="updatePrice(this)">
                        <option value="">اختر المنتج...</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.cost_price}" ${p.id == productId ? 'selected' : ''}>${p.name} (${p.sku})</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${index}][quantity]" class="form-control form-control-dark text-center qty-input" 
                        value="${quantity}" min="1" step="any" required oninput="calculateRow(this)">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control form-control-dark text-center price-input" 
                        value="${price}" min="0" required oninput="calculateRow(this)">
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
            
            // Calculate initial totals for this row
            const newRow = tbody.lastElementChild;
            calculateRow(newRow.querySelector('.qty-input'));
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
            
            document.getElementById('subtotalDisplay').innerText = total.toFixed(2);
            document.getElementById('totalDisplay').innerText = total.toFixed(2);
            updateRemaining();
        }

        function togglePayment(checkbox) {
            const section = document.getElementById('paymentSection');
            if (checkbox.checked) {
                section.classList.remove('d-none');
            } else {
                section.classList.add('d-none');
                document.getElementById('paidAmount').value = '';
                updateRemaining();
            }
        }

        function updateRemaining() {
            const total = parseFloat(document.getElementById('totalDisplay').innerText) || 0;
            const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
            const remaining = total - paid;
        }

        function generateInvoiceNumber() {
            const timestamp = new Date().getTime().toString().slice(-6);
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            document.getElementById('supplierInvoiceInput').value = `INV-${timestamp}-${random}`;
        }

        // Initialize: Check for GRN Items or add Empty Row
        document.addEventListener('DOMContentLoaded', function() {
            if (grnItems && grnItems.length > 0) {
                grnItems.forEach(item => addNewRow(item));
            } else {
                addNewRow();
            }
        });
    </script>

    <style>
        .btn-action-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: var(--text-primary); border: none; padding: 10px 24px; border-radius: 10px; }
        .btn-action-cyan:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(6, 182, 212, 0.4); }
        
        .btn-dashed-cyan { border: 2px dashed rgba(34, 211, 238, 0.3); color: #22d3ee; border-radius: 12px; transition: 0.2s; background: rgba(34, 211, 238, 0.05); }
        .btn-dashed-cyan:hover { background: rgba(34, 211, 238, 0.1); border-color: #22d3ee; color: var(--text-primary); }

        .form-control-dark, .form-select-dark { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid var(--btn-glass-border); !important; color: var(--text-primary); !important; }
        .focus-ring-cyan:focus { border-color: #22d3ee !important; box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.1) !important; }
        
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-border-color: rgba(255, 255, 255, 0.05); color: #e2e8f0; }
        .table-dark-custom th { background: rgba(0, 0, 0, 0.2); color: var(--text-secondary); font-weight: 600; padding: 1rem; }
        .table-dark-custom td { padding: 0.75rem 1rem; }

        .btn-icon-glass { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: var(--btn-glass-bg); color: #cbd5e1; transition: 0.2s; }
        .btn-icon-glass:hover { background: rgba(255,255,255,0.1); color: var(--text-primary); }
        .hover-danger:hover { background: rgba(239, 68, 68, 0.2) !important; color: #ef4444 !important; }
    </style>
@endsection
