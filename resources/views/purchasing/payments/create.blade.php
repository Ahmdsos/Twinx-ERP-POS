@extends('layouts.app')

@section('title', 'تسجيل دفعة مورد')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('supplier-payments.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-white mb-0">تسجيل دفعة جديدة</h2>
                    <p class="text-gray-400 mb-0 x-small">إصدار دفعة نقدية/بنكية لمورد</p>
                </div>
            </div>
            <button type="submit" form="paymentForm" class="btn btn-action-purple fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-check-lg"></i> حفظ وترحيل
            </button>
        </div>

        <form action="{{ route('supplier-payments.store') }}" method="POST" id="paymentForm">
            @csrf
            
            <div class="row g-4">
                <!-- Payment Details -->
                <div class="col-md-4">
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="text-purple-400 mb-4 fw-bold"><i class="bi bi-info-circle me-2"></i>بيانات الدفع</h5>
                        
                        <div class="mb-3">
                            <label class="form-label text-gray-400 small fw-bold">المورد <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplierSelect" class="form-select form-select-dark focus-ring-purple" required onchange="filterInvoices()">
                                <option value="">-- اختر المورد --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                        {{ (old('supplier_id') == $supplier->id || request('supplier_id') == $supplier->id || (isset($selectedInvoice) && $selectedInvoice->supplier_id == $supplier->id)) ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 small fw-bold">قيمة الدفعة (الإجمالي) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount" id="amountField" class="form-control form-control-dark focus-ring-purple fs-4 fw-bold text-center" 
                                    value="{{ old('amount', isset($selectedInvoice) ? $selectedInvoice->balance_due : '') }}" required placeholder="0.00">
                                <span class="input-group-text bg-dark-input border-start-0 text-gray-400">EGP</span>
                            </div>
                            @error('amount') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 small fw-bold">تاريخ الدفع <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-dark focus-ring-purple" 
                                value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 small fw-bold">طريقة الدفع <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-dark focus-ring-purple" required>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>نقدي (خزينة)</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>شيك</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gray-400 small fw-bold">الخزينة / الحساب <span class="text-danger">*</span></label>
                            <select name="payment_account_id" class="form-select form-select-dark focus-ring-purple" required>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ $account->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-gray-400 small fw-bold">ملاحظات / رقم المرجع</label>
                            <textarea name="notes" class="form-control form-control-dark focus-ring-purple" rows="3" placeholder="مثال: رقم التحويل البنكي...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Invoice Allocation (Auto-filtered by JS) -->
                <div class="col-md-8">
                    <div class="glass-panel p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="text-purple-400 fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>الفواتير المستحقة</h5>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="autoAllocate()">توزيع تلقائي</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark-custom align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>رقم الفاتورة</th>
                                        <th>تاريخ الاستحقاق</th>
                                        <th class="text-end">قيمة الفاتورة</th>
                                        <th class="text-end">المتبقي</th>
                                        <th class="text-end" style="width: 150px;">التخصيص (سداد جزء)</th>
                                    </tr>
                                </thead>
                                <tbody id="invoicesTableBody">
                                    @foreach($pendingInvoices as $index => $invoice)
                                        <tr class="invoice-row supplier-{{ $invoice->supplier_id }}" style="display: none;">
                                            <td>
                                                <input type="checkbox" class="form-check-input invoice-check" 
                                                    id="check_{{ $invoice->id }}"
                                                    data-id="{{ $invoice->id }}"
                                                    data-balance="{{ $invoice->balance_due }}">
                                            </td>
                                            <td class="font-monospace">{{ $invoice->invoice_number }}</td>
                                            <td class="text-gray-400 x-small">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</td>
                                            <td class="text-end text-gray-400">{{ number_format($invoice->total, 2) }}</td>
                                            <td class="text-end fw-bold text-red-300">{{ number_format($invoice->balance_due, 2) }}</td>
                                            <td>
                                                <input type="number" step="0.01" 
                                                    name="allocations[{{ $index }}][amount]" 
                                                    class="form-control form-control-sm form-control-dark text-end allocation-input"
                                                    data-id="{{ $invoice->id }}"
                                                    max="{{ $invoice->balance_due }}"
                                                    placeholder="0.00">
                                                <input type="hidden" name="allocations[{{ $index }}][invoice_id]" value="{{ $invoice->id }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr id="noInvoicesRow">
                                        <td colspan="6" class="text-center py-5 text-gray-500">
                                            يرجى اختيار مورد لعرض الفواتير المستحقة
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .btn-action-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border: none; color: white; padding: 10px 24px; border-radius: 10px; transition: all 0.3s;
        }
        .btn-action-purple:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4); }

        .form-control-dark, .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }
        .focus-ring-purple:focus {
            border-color: #c084fc !important;
            box-shadow: 0 0 0 4px rgba(192, 132, 252, 0.1) !important;
        }
        .bg-dark-input { background: rgba(15, 23, 42, 0.8) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; }
        
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-border-color: rgba(255, 255, 255, 0.05); color: #e2e8f0; }
    </style>

    <script>
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            filterInvoices();
            
            // Add listener for manual inputs
            document.querySelectorAll('.allocation-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const checkbox = row.querySelector('.invoice-check');
                    const val = parseFloat(this.value) || 0;
                    checkbox.checked = val > 0;
                });
            });

            // Pre-select Invoice if passed from Controller
            @if(isset($selectedInvoice))
                const preselectedId = "{{ $selectedInvoice->id }}";
                const preselectedInput = document.querySelector(`.invoice-row input.allocation-input[data-id="${preselectedId}"]`);
                if (preselectedInput) {
                    preselectedInput.value = "{{ $selectedInvoice->balance_due }}";
                    preselectedInput.closest('tr').querySelector('.invoice-check').checked = true;
                    // Auto allocate call not needed as we manually set it here, but filtering is needed
                }
            @endif
        });

        function filterInvoices() {
            const supplierId = document.getElementById('supplierSelect').value;
            const rows = document.querySelectorAll('.invoice-row');
            let hasVisible = false;

            rows.forEach(row => {
                // IMPORTANT: ClassList check needs to be precise
                if (supplierId && row.classList.contains('supplier-' + supplierId)) {
                    row.style.display = 'table-row';
                    hasVisible = true;
                } else {
                    row.style.display = 'none';
                    // Reset inputs when hidden to avoid unintentional submission
                    row.querySelector('.allocation-input').value = '';
                    row.querySelector('.invoice-check').checked = false;
                }
            });

            const noRow = document.getElementById('noInvoicesRow');
            if (hasVisible) {
                noRow.style.display = 'none';
            } else {
                noRow.style.display = 'table-row';
                const td = noRow.querySelector('td');
                if (supplierId) {
                    td.innerText = 'لا توجد فواتير مستحقة لهذا المورد';
                } else {
                    td.innerText = 'يرجى اختيار مورد لعرض الفواتير المستحقة';
                }
            }
        }

        function autoAllocate() {
            const amountInput = document.getElementById('amountField');
            let remainingAmount = parseFloat(amountInput.value) || 0;
            const supplierId = document.getElementById('supplierSelect').value;
            
            if (!supplierId) {
                alert('يرجى اختيار المورد أولاً');
                return;
            }
            if (remainingAmount <= 0) {
                alert('يرجى إدخال قيمة الدفعة أولاً');
                amountInput.focus();
                return;
            }

            // Get visible rows only
            const rows = Array.from(document.querySelectorAll(`.invoice-row.supplier-${supplierId}`));
            
            // Reset previous allocations first
            rows.forEach(row => {
                row.querySelector('.allocation-input').value = '';
                row.querySelector('.invoice-check').checked = false;
            });

            // Distribute
            for (const row of rows) {
                if (remainingAmount <= 0.009) break; // Float tolerance

                const check = row.querySelector('.invoice-check');
                const input = row.querySelector('.allocation-input');
                const balance = parseFloat(check.dataset.balance);

                if (balance > 0) {
                    const allocate = Math.min(balance, remainingAmount);
                    input.value = allocate.toFixed(2);
                    check.checked = true;
                    remainingAmount -= allocate;
                }
            }
            
            // Optional: Feedback
            if (remainingAmount > 0.01) {
                // If money left over, maybe show a toast or log
                console.log('Remaining unallocated: ' + remainingAmount);
            }
        }
    </script>
@endsection
