@extends('layouts.app')

@section('title', 'تعديل الفاتورة #' . $salesInvoice->invoice_number)
@section('header', 'تعديل الفاتورة رقم ' . $salesInvoice->invoice_number)

@section('content')
    <div class="container-fluid" x-data="invoiceEditor()">

        <form action="{{ route('sales-invoices.update', $salesInvoice) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Header Info -->
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-4">بيانات الفاتورة الأساسية</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Customer') }}</label>
                                    <select name="customer_id" class="form-select" required>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $salesInvoice->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Invoice Date') }}</label>
                                    <input type="date" name="invoice_date" class="form-control"
                                        value="{{ $salesInvoice->invoice_date->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">تاريخ الاستحقاق</label>
                                    <input type="date" name="due_date" class="form-control"
                                        value="{{ $salesInvoice->due_date ? $salesInvoice->due_date->format('Y-m-d') : '' }}"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lines -->
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">بنود الفاتورة</h5>
                            <button type="button" @click="addLine" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> إضافة بند
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%">{{ __('Product') }}</th>
                                            <th style="width: 15%">{{ __('Quantity') }}</th>
                                            <th style="width: 15%">{{ __('Price') }}</th>
                                            <th style="width: 15%">خصم %</th>
                                            <th style="width: 15%">{{ __('Total') }}</th>
                                            <th style="width: 10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(line, index) in lines" :key="index">
                                            <tr>
                                                <td>
                                                    <select :name="'lines['+index+'][product_id]'" x-model="line.product_id"
                                                        @change="updateProductInfo(index)"
                                                        class="form-select form-select-sm" required>
                                                        <option value="">اختر المنتج...</option>
                                                        @foreach($productsData as $product)
                                                            <option value="{{ $product['id'] }}"
                                                                data-price="{{ $product['price'] }}">
                                                                {{ $product['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" :name="'lines['+index+'][id]'" x-model="line.id">
                                                </td>
                                                <td>
                                                    <input type="number" :name="'lines['+index+'][quantity]'"
                                                        x-model="line.quantity" step="0.01" min="0.1"
                                                        class="form-control form-control-sm" required>
                                                </td>
                                                <td>
                                                    <input type="number" :name="'lines['+index+'][unit_price]'"
                                                        x-model="line.unit_price" step="0.01" min="0"
                                                        class="form-control form-control-sm" required>
                                                </td>
                                                <td>
                                                    <input type="number" :name="'lines['+index+'][discount_percent]'"
                                                        x-model="line.discount_percent" step="0.1" min="0" max="100"
                                                        class="form-control form-control-sm">
                                                    <input type="hidden" :name="'lines['+index+'][tax_percent]'"
                                                        x-model="line.tax_percent">
                                                </td>
                                                <td class="fw-bold text-end">
                                                    <span x-text="formatMoney(calculateLineTotal(line))"></span>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" @click="removeLine(index)"
                                                        class="btn btn-sm btn-outline-danger border-0">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="4" class="text-end">الإجمالي قبل الضريبة:</td>
                                            <td class="text-end" x-text="formatMoney(netTotal)"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">الضريبة (تقديري):</td>
                                            <td class="text-end" x-text="formatMoney(taxTotal)"></td>
                                            <td></td>
                                        </tr>
                                        <tr class="fs-5 bg-primary bg-opacity-10">
                                            <td colspan="4" class="text-end">الإجمالي النهائي:</td>
                                            <td class="text-end text-primary" x-text="formatMoney(grandTotal)"></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-12 mt-4 text-end">
                    <a href="{{ route('sales-invoices.show', $salesInvoice) }}" class="btn btn-light border">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                        <i class="bi bi-save me-2"></i> حفظ التعديلات
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function invoiceEditor() {
                return {
                    lines: @json($linesData),
                    products: @json($productsData),
                    defaultTaxRate: {{ $defaultTaxRate ?? 14 }},

                    addLine() {
                        this.lines.push({
                            product_id: '',
                            quantity: 1,
                            unit_price: 0,
                            discount_percent: 0,
                            tax_percent: this.defaultTaxRate
                        });
                    },

                    removeLine(index) {
                        this.lines.splice(index, 1);
                    },

                    updateProductInfo(index) {
                        let line = this.lines[index];
                        let product = this.products.find(p => p.id == line.product_id);
                        if (product) {
                            line.unit_price = product.price;
                            line.tax_percent = product.tax_rate || this.defaultTaxRate;
                        }
                    },

                    calculateLineTotal(line) {
                        let qty = parseFloat(line.quantity) || 0;
                        let price = parseFloat(line.unit_price) || 0;
                        let discount = parseFloat(line.discount_percent) || 0;
                        return qty * price * (1 - discount / 100);
                    },

                    get netTotal() {
                        return this.lines.reduce((sum, line) => sum + this.calculateLineTotal(line), 0);
                    },

                    get taxTotal() {
                        return this.lines.reduce((sum, line) => {
                            let lineTotal = this.calculateLineTotal(line);
                            let taxPercent = parseFloat(line.tax_percent) || 0;
                            return sum + (lineTotal * taxPercent / 100);
                        }, 0);
                    },

                    get grandTotal() {
                        return this.netTotal + this.taxTotal;
                    },

                    formatMoney(amount) {
                        return new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'EGP'
                        }).format(amount);
                    }
                }
            }
        </script>
    @endpush
@endsection