@extends('layouts.app')

@section('title', 'سداد ذكي لمورد')

@section('content')
    <div class="container-fluid p-0" x-data="smartPayment()">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('supplier-payments.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">سداد ذكي لمورد</h2>
                    <p class="text-gray-400 mb-0 x-small">نظام التوزيع التلقائي الذكي على الفواتير</p>
                </div>
            </div>
            <button type="submit" form="paymentForm" class="btn btn-action-purple fw-bold shadow-lg d-flex align-items-center gap-2 px-4 py-2">
                <i class="bi bi-check-lg fs-5"></i> تأكيد وترحيل الدفعة
            </button>
        </div>

        <form action="{{ route('supplier-payments.store') }}" method="POST" id="paymentForm">
            @csrf

            <div class="row g-4 justify-content-center">
                <!-- Step 1 & 2: Main Controls -->
                <div class="col-lg-8">
                    <!-- Basic Info Row -->
                    <div class="glass-panel p-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-gray-400 small fw-bold">المورد المستلم <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select form-select-dark focus-ring-purple" required 
                                    x-model="supplierId" @change="fetchSupplierData()">
                                    <option value="">-- اختر المورد --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-gray-400 small fw-bold">{{ __('Payment Method') }}<span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select form-select-dark focus-ring-purple" required>
                                    <option value="cash">نقدي (خزينة)</option>
                                    <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                    <option value="cheque">{{ __('Check') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-gray-400 small fw-bold">تاريخ العملية</label>
                                <input type="date" name="payment_date" class="form-control form-control-dark focus-ring-purple" 
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-gray-400 small fw-bold">رقم مرجع</label>
                                <input type="text" name="reference" class="form-control form-control-dark focus-ring-purple" placeholder="اختياري">
                            </div>
                        </div>
                    </div>

                    <!-- THE SMART AMOUNT FIELD -->
                    <div class="glass-panel p-5 text-center mb-4 position-relative overflow-hidden shadow-2xl" 
                        :class="supplierId ? 'opacity-100' : 'opacity-50 pointer-events-none'">
                        <!-- Decoration -->
                        <div class="position-absolute top-0 start-0 w-100 h-1 border-top border-purple-500 border-4"></div>

                        <h4 class="text-gray-400 fw-bold mb-4">أدخل المبلغ المراد سداده</h4>

                        <div class="d-inline-block position-relative mb-2">
                            <input type="number" step="0.01" name="amount" x-model.number="amount" @input="recalculateAllocations()"
                                class="smart-amount-input text-center fw-black display-3 text-body bg-transparent border-0 focus-ring-none"
                                placeholder="0.00" autocomplete="off" required>
                            <span class="fs-4 text-purple-400 fw-bold ms-2">EGP</span>
                        </div>

                        <div class="d-flex justify-content-center gap-4 mt-3 pt-3 border-top border-secondary border-opacity-10 border-opacity-5">
                            <div class="text-center">
                                <p class="text-gray-500 x-small mb-1">المديونية الحالية</p>
                                <h5 class="text-heading fw-black mb-0" x-text="formatCurrency(supplierBalance)">0.00</h5>
                            </div>
                            <div class="vr bg-surface opacity-10"></div>
                            <div class="text-center">
                                <p class="text-gray-500 x-small mb-1">الرصيد بعد السداد</p>
                                <h5 class="fw-black mb-0" :class="remainingBalance > 0 ? 'text-red-400' : 'text-green-400'" 
                                    x-text="formatCurrency(Math.abs(remainingBalance))">0.00</h5>
                            </div>
                        </div>
                    </div>

                    <!-- SMART INSIGHTS / ALLOCATION REVIEW -->
                    <div class="glass-panel p-4 h-100" x-show="supplierId && (amount > 0 || invoices.length > 0)" x-cloak x-transition>
                        <h5 class="text-purple-400 fw-bold mb-4 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-cpu me-2"></i>تحليل الذكاء المالي</span>
                            <span class="badge bg-purple-500 fs-x-small px-3" x-show="autoAllocated">توزيع تلقائي (FIFO)</span>
                        </h5>

                        <div class="space-y-3">
                            <!-- Advance Payment Alert -->
                            <template x-if="remainingBalance < -0.01">
                                <div class="alert bg-cyan-500 bg-opacity-10 border-cyan-500 border-opacity-20 text-cyan-400 d-flex align-items-center gap-3 py-3 rounded-4 shadow-sm mb-4">
                                    <i class="bi bi-info-circle-fill fs-4"></i>
                                    <div>
                                        <p class="mb-0 fw-bold">هذه الدفعة أكبر من إجمالي الفواتير المستحقة.</p>
                                        <p class="mb-0 x-small opacity-75">سيتم تسجيل مبلغ <span class="fw-black" x-text="formatCurrency(Math.abs(remainingBalance))"></span> كدفعة مقدمة في رصيد المورد.</p>
                                    </div>
                                </div>
                            </template>

                            <!-- Allocation List -->
                            <div class="table-responsive rounded-4 overflow-hidden border border-secondary border-opacity-10 border-opacity-5 shadow-inner">
                                <table class="table table-dark-custom align-middle mb-0">
                                    <thead class="bg-black bg-opacity-25">
                                        <tr>
                                            <th class="ps-4">الفاتورة المستهدفة</th>
                                            <th class="text-center">{{ __('Status') }}</th>
                                            <th class="text-end">المبلغ المخصص</th>
                                            <th class="text-end pe-4">المتبقي بالفاتورة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(inv, index) in invoices" :key="inv.id">
                                            <tr :class="inv.allocated > 0 ? 'bg-purple-500 bg-opacity-5' : 'opacity-40'">
                                                <td class="ps-4">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-body" x-text="inv.invoice_number"></span>
                                                        <span class="x-small text-gray-500" x-text="inv.due_date"></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <template x-if="inv.allocated >= inv.balance_due">
                                                        <span class="badge bg-green-500 bg-opacity-10 text-green-400 border border-green-500 border-opacity-20 px-3">سداد كلي</span>
                                                    </template>
                                                    <template x-if="inv.allocated > 0 && inv.allocated < inv.balance_due">
                                                        <span class="badge bg-yellow-500 bg-opacity-10 text-yellow-400 border border-yellow-500 border-opacity-20 px-3">سداد جزئي</span>
                                                    </template>
                                                    <template x-if="inv.allocated == 0">
                                                        <span class="badge bg-gray-500 bg-opacity-10 text-gray-400 border border-secondary border-opacity-10 border-opacity-10 px-3">غير مشمول</span>
                                                    </template>
                                                </td>
                                                <td class="text-end fw-black text-body" x-text="formatCurrency(inv.allocated)"></td>
                                                <td class="text-end pe-4">
                                                    <span :class="inv.new_balance <= 0 ? 'text-gray-500' : 'text-red-400 fw-bold'" 
                                                        x-text="formatCurrency(inv.new_balance)"></span>
                                                </td>
                                                <!-- Hidden fields for form submission -->
                                                <input type="hidden" :name="'allocations['+index+'][invoice_id]'" :value="inv.id">
                                                <input type="hidden" :name="'allocations['+index+'][amount]'" :value="inv.allocated">
                                            </tr>
                                        </template>
                                        <tr x-show="invoices.length === 0">
                                            <td colspan="4" class="text-center py-5 text-gray-500">لا توجد فواتير مستحقة لهذا المورد</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Sidebar Info -->
                <div class="col-lg-4">
                    <div class="glass-panel p-4 mb-4">
                        <h5 class="text-gray-300 mb-4 fw-bold">إعدادات الحساب</h5>

                        <div class="mb-4">
                            <label class="form-label text-gray-400 small fw-bold">الخزينة / الحساب المصرفي</label>
                            <select name="payment_account_id" class="form-select form-select-dark focus-ring-purple" required>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ $account->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label text-gray-400 small fw-bold">ملاحظات العملية</label>
                            <textarea name="notes" class="form-control form-control-dark focus-ring-purple" rows="4" 
                                placeholder="أضف أي ملاحظات مهمة هنا..."></textarea>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="glass-panel p-4 bg-gradient-to-br from-purple-900/20 to-transparent border-purple-500/10">
                        <h6 class="text-heading fw-bold mb-3"><i class="bi bi-lightning-charge me-2 text-yellow-400"></i>نصيحة ذكية</h6>
                        <p class="text-gray-400 x-small lh-lg mb-0">
                            السيستم بيقوم بتوزيع المبلغ اللي بتدخله تلقائياً على أقدم الفواتير المستحقة (FIFO). 
                            لو المبلغ أكبر من قيمة كل الفواتير، الزيادة هتتحسب كـ "دفعة مقدمة" في رصيد المورد وتقدر تخصمها من أي فواتير مستقبلية.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .smart-amount-input { outline: none; border-bottom: 2px solid rgba(139, 92, 246, 0.2) !important; padding: 10px; min-width: 250px; transition: all 0.3s; }
        .smart-amount-input:focus { border-bottom-color: #8b5cf6 !important; }
        .smart-amount-input::-webkit-inner-spin-button, .smart-amount-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

        .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .display-3 { font-size: 4.5rem; }
        .fw-black { font-weight: 900; }
        .rounded-4 { border-radius: 1.25rem !important; }

        .btn-action-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border: none; color: var(--text-primary); border-radius: 12px; transition: all 0.3s;
        }
        .btn-action-purple:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.4); }

        .form-control-dark, .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border-bottom: 1px solid var(--border-color); !important;
            border-radius: 10px; color: var(--text-primary); !important;
        }
        .focus-ring-purple:focus { border-color: #c084fc !important; box-shadow: 0 0 0 4px rgba(192, 132, 252, 0.08) !important; }

        [x-cloak] { display: none !important; }
    </style>

    <script>
        function smartPayment() {
            return {
                supplierId: '{{ request('supplier_id', '') }}',
                amount: {{ request('amount', 0) }},
                supplierBalance: 0,
                invoices: [],
                autoAllocated: true,

                init() {
                    // Raw data from PHP sent to JS
                    const rawInvoices = @json($pendingInvoices);
                    this.allPendingInvoices = rawInvoices.map(inv => ({
                        ...inv,
                        allocated: 0,
                        new_balance: parseFloat(inv.balance_due)
                    }));

                    if (this.supplierId) {
                        this.fetchSupplierData();
                    }

                    // Handling pre-selected invoice from URL
                    const preSelectedInvoiceId = '{{ request('invoice_id') }}';
                    if (preSelectedInvoiceId && this.amount > 0) {
                        // Special logic if a specific invoice is targeted? 
                        // For now keep it FIFO or just prioritize it?
                        // ERP convention: Even if targetted, UI should reflect truth.
                    }
                },

                fetchSupplierData() {
                    if (!this.supplierId) {
                        this.invoices = [];
                        this.supplierBalance = 0;
                        return;
                    }

                    const preSelectedInvoiceId = '{{ request('invoice_id') }}';

                    // Filter invoices by selected supplier and sort
                    this.invoices = this.allPendingInvoices
                        .filter(inv => inv.supplier_id == this.supplierId)
                        .sort((a, b) => {
                            // 1. Prioritize pre-selected invoice
                            if (preSelectedInvoiceId && a.id == preSelectedInvoiceId) return -1;
                            if (preSelectedInvoiceId && b.id == preSelectedInvoiceId) return 1;
                            // 2. Otherwise FIFO by due date
                            return new Date(a.due_date) - new Date(b.due_date);
                        });
                    
                    this.supplierBalance = this.invoices.reduce((sum, inv) => sum + parseFloat(inv.balance_due), 0);
                    
                    this.recalculateAllocations();
                },

                recalculateAllocations() {
                    let runningAmount = parseFloat(this.amount) || 0;

                    this.invoices.forEach(inv => {
                        if (runningAmount > 0) {
                            const balance = parseFloat(inv.balance_due);
                            const toAllocate = Math.min(balance, runningAmount);
                            inv.allocated = toAllocate;
                            inv.new_balance = balance - toAllocate;
                            runningAmount -= toAllocate;
                        } else {
                            inv.allocated = 0;
                            inv.new_balance = parseFloat(inv.balance_due);
                        }
                    });
                },

                get remainingBalance() {
                    let totalDue = this.invoices.reduce((sum, inv) => sum + parseFloat(inv.balance_due), 0);
                    return totalDue - (parseFloat(this.amount) || 0);
                },

                formatCurrency(val) {
                    return new Intl.NumberFormat('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
                }
            }
        }
    </script>
@endsection
