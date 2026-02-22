@extends('layouts.app')

@section('title', 'تسجيل مرتجع جديد')

@section('content')
    <div x-data="salesReturnForm()" class="row justify-content-center">
        <div class="col-12">
            <form action="{{ route('sales-returns.store') }}" method="POST" @submit.prevent="submitForm">
                @csrf

                <!-- Header Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-heading mb-0">تسجيل مرتجع مبيعات جديد</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sales-returns.index') }}" class="btn btn-glass-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ المرتجع
                        </button>
                    </div>
                </div>

                <!-- Main Info Card -->
                <div class="glass-card p-4 mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-gray-300">{{ __('Customer') }}<span class="text-danger">*</span></label>
                            <select name="customer_id" x-model="customerId" @change="loadInvoices"
                                class="form-select form-select-lg bg-transparent text-body border-secondary" required>
                                <option value="">اختر العميل...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-gray-300">فاتورة مرجعية (اختياري)</label>
                            <select name="sales_invoice_id" x-model="invoiceId" @change="loadInvoiceItems"
                                class="form-select bg-transparent text-body border-secondary" :disabled="!customerId">
                                <option value="">-- بدون فاتورة (مرتجع حر) --</option>
                                <template x-for="invoice in invoices" :key="invoice.id">
                                    <option :value="invoice.id"
                                        x-text="'#' + invoice.invoice_number + ' (' + invoice.invoice_date + ')'"></option>
                                </template>
                            </select>
                            <div x-show="loadingInvoices" class="text-info small mt-1">جاري تحميل الفواتير...</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-gray-300">المخزن (الاستلام) <span
                                    class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select bg-transparent text-body border-secondary"
                                required>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-gray-300">{{ __('Return Date') }}<span class="text-danger">*</span></label>
                            <input type="date" name="return_date"
                                class="form-control bg-transparent text-body border-secondary" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-heading mb-0">الأصناف المرتجعة</h5>
                        <button type="button" @click="addItem()" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-plus-lg me-1"></i> إضافة صنف يدوي
                        </button>
                    </div>

                    <div x-show="loadingLines" class="text-center py-3 text-info">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        جاري تحميل أصناف الفاتورة...
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="text-gray-400 border-bottom border-secondary border-opacity-25 small">
                                <tr>
                                    <th width="35%">{{ __('Product') }}</th>
                                    <th width="15%">حالة الصنف</th>
                                    <th width="12%">{{ __('Quantity') }}</th>
                                    <th width="15%">سعر الاسترجاع</th>
                                    <th width="15%">{{ __('Total') }}</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-bottom border-secondary border-opacity-10">
                                        <td>
                                            <select :name="'items['+index+'][product_id]'" x-model="item.product_id"
                                                class="form-select bg-transparent text-body border-secondary fs-6"
                                                required>
                                                <option value="">اختر المنتج...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->name }} ({{ $product->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select :name="'items['+index+'][condition]'" x-model="item.condition"
                                                class="form-select bg-transparent text-body border-secondary fs-6">
                                                <option value="resalable">صالح للبيع</option>
                                                <option value="damaged">تالف / معيب</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" :name="'items['+index+'][quantity]'"
                                                x-model="item.quantity"
                                                class="form-control bg-transparent text-body border-secondary text-center"
                                                min="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" :name="'items['+index+'][unit_price]'"
                                                x-model="item.unit_price"
                                                class="form-control bg-transparent text-body border-secondary text-center"
                                                min="0" required>
                                        </td>
                                        <td class="text-end fw-bold text-info">
                                            <span x-text="(item.quantity * item.unit_price).toFixed(2)"></span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" @click="removeItem(index)"
                                                class="btn btn-sm btn-icon-glass text-danger hover-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="border-top border-secondary border-opacity-25">
                                <tr>
                                    <td colspan="4" class="text-end py-3 text-gray-300">الإجمالي الكلي:</td>
                                    <td class="text-end py-3 text-body fw-bold fs-5">
                                        <span x-text="grandTotal"></span> <small
                                            class="fs-6 fw-normal text-gray-500">{{ __('EGP') }}</small>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div x-show="items.length === 0"
                        class="text-center py-5 text-gray-500 border border-dashed border-secondary border-opacity-25 rounded-3 mt-3">
                        <i class="bi bi-cart-x fs-1 d-block mb-3 opacity-50"></i>
                        لم يتم إضافة أي أصناف للمرتجع
                    </div>
                </div>

                <!-- Notes -->
                <div class="glass-card p-4">
                    <label class="form-label text-gray-300">ملاحظات إضافية</label>
                    <textarea name="notes" class="form-control bg-transparent text-body border-secondary" rows="3"
                        placeholder="أدخل أي تفاصيل إضافية حول سبب الإرجاع..."></textarea>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            

            .btn-glass-outline {
                background: var(--btn-glass-bg);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: var(--text-primary);
            }

            .btn-icon-glass {
                background: var(--btn-glass-bg);
                border: 1px solid var(--btn-glass-border);
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .form-control:focus,
            .form-select:focus {
                background-color: rgba(30, 41, 59, 0.9);
                border-color: #3b82f6;
                box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
                color: var(--text-primary);
            }

            option {
                background-color: var(--input-bg);
                color: var(--text-primary);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function salesReturnForm() {
                return {
                    customerId: '',
                    invoiceId: '',
                    invoices: [],
                    items: [
                        { product_id: '', quantity: 1, unit_price: 0, condition: 'resalable' }
                    ],
                    loadingInvoices: false,
                    loadingLines: false,



                    async init() {
                        // Check for URL params
                        const urlParams = new URLSearchParams(window.location.search);
                        const invoiceId = urlParams.get('invoice_id');
                        const customerId = urlParams.get('customer_id');

                        if (customerId) {
                            this.customerId = customerId;
                            await this.loadInvoices(); // Wait for invoices to load

                            if (invoiceId) {
                                // Check if invoice exists in the list (it should)
                                const invoiceExists = this.invoices.some(inv => inv.id == invoiceId);
                                if (invoiceExists) {
                                    this.invoiceId = invoiceId;
                                    await this.loadInvoiceItems();
                                }
                            }
                        }
                    },

                    async loadInvoices() {
                        this.invoices = [];
                        // Don't clear invoiceId if we are initializing (it might be set)
                        // this.invoiceId = ''; 

                        if (!this.customerId) return;

                        this.loadingInvoices = true;
                        try {
                            const response = await fetch(`{{ url('api/customers') }}/${this.customerId}/invoices`);
                            this.invoices = await response.json();
                        } catch (error) {
                            console.error('Error loading invoices:', error);
                        } finally {
                            this.loadingInvoices = false;
                        }
                    },

                    async loadInvoiceItems() {
                        if (!this.invoiceId) return;

                        this.loadingLines = true;
                        // Keep existing manual items or clear? Let's clear to avoid confusion, or confirm? 
                        // For simplified UX, let's Replace items.
                        this.items = [];

                        try {
                            const response = await fetch(`{{ url('api/invoices') }}/${this.invoiceId}/lines`);
                            const lines = await response.json();

                            this.items = lines.map(line => ({
                                product_id: line.product_id,
                                quantity: line.quantity, // Default to full quantity, user can reduce
                                unit_price: line.unit_price,
                                condition: 'resalable'
                            }));

                            if (this.items.length === 0) {
                                this.addItem(); // Add one empty row if invoice has no lines? Should not happen.
                            }
                        } catch (error) {
                            console.error('Error loading lines:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: 'حدث خطأ أثناء تحميل أصناف الفاتورة',
                                background: '#1e293b',
                                color: '#fff'
                            });
                            this.addItem();
                        } finally {
                            this.loadingLines = false;
                        }
                    },

                    addItem() {
                        this.items.push({ product_id: '', quantity: 1, unit_price: 0, condition: 'resalable' });
                    },

                    removeItem(index) {
                        if (this.items.length > 0) {
                            this.items.splice(index, 1);
                        }
                    },

                    get grandTotal() {
                        return this.items.reduce((sum, item) => {
                            return sum + (item.quantity * item.unit_price);
                        }, 0).toFixed(2);
                    },

                    submitForm(e) {
                        if (this.items.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'تنبيه',
                                text: 'يجب إضافة صنف واحد على الأقل للمرتجع',
                                background: '#1e293b',
                                color: '#fff'
                            });
                            return;
                        }
                        e.target.submit();
                    }
                }
            }
        </script>
    @endpush
@endsection