@extends('layouts.app')

@section('title', 'إنشاء عرض سعر | Premium Builder')

@section('content')
    <div class="container-fluid p-0" x-data="quotationBuilder()">

        <!-- Top Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 sticky-top py-3 glass-header z-20">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-gradient-to-br from-cyan-500 to-blue-600 rounded-circle shadow-lg text-white">
                    <i class="bi bi-file-earmark-richtext fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-0" style="letter-spacing: -0.5px;">عرض سعر جديد</h3>
                    <small class="text-blue-300 fw-bold">No. <span class="font-monospace">AUTO-GEN</span></small>
                </div>
            </div>

            <div class="d-flex gap-3">
                <a href="{{ route('quotations.index') }}" class="btn btn-glass-outline rounded-pill px-4 fw-bold">
                    إلغاء
                </a>
                <button type="button" @click="submitForm" class="btn btn-gradient-primary rounded-pill px-5 fw-bold shadow-lg hover-scale">
                    <i class="bi bi-send-check-fill me-2"></i> حفظ العرض
                </button>
            </div>
        </div>

        <form action="{{ route('quotations.store') }}" method="POST" id="quotation-form" @keydown.enter.prevent>
            @csrf

            <div class="row g-4">
                <!-- Left Column: Customer & Line Items -->
                <div class="col-xl-9 col-lg-8">

                    <!-- 1. Customer & Meta Data Card -->
                    <div class="glass-card mb-4 p-4 position-relative overflow-hidden">
                        <div class="absolute-glow top-0 end-0"></div>
                        <div class="row g-4 position-relative z-10">
                            <div class="col-md-6">
                                <label class="section-label mb-2">العميل <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <i class="bi bi-person-bounding-box position-absolute top-50 start-0 translate-middle-y ms-3 text-blue-400"></i>
                                    <select name="customer_id" class="form-select glass-select ps-5 text-white fw-bold h-50px" required>
                                        <option value="" class="text-gray-500">اختر العميل...</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" class="bg-gray-900 text-white py-2" 
                                                {{ (old('customer_id') == $customer->id || (isset($selectedCustomer) && $selectedCustomer->id == $customer->id)) ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="section-label mb-2">تاريخ الإصدار</label>
                                <div class="position-relative">
                                    <i class="bi bi-calendar-event position-absolute top-50 start-0 translate-middle-y ms-3 text-blue-400"></i>
                                    <input type="date" name="quotation_date" class="form-control glass-input ps-5 text-white fw-bold h-50px" 
                                           value="{{ old('quotation_date', date('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="section-label mb-2 text-warning">تاريخ الانتهاء</label>
                                <div class="position-relative">
                                    <i class="bi bi-calendar-x position-absolute top-50 start-0 translate-middle-y ms-3 text-warning"></i>
                                    <input type="date" name="valid_until" class="form-control glass-input ps-5 text-white fw-bold h-50px" 
                                           value="{{ old('valid_until', date('Y-m-d', strtotime('+15 days'))) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Items Builder -->
                    <div class="glass-card p-0 mb-4 overflow-hidden min-h-600 d-flex flex-column">
                        <div class="p-4 border-bottom border-white/10 d-flex justify-content-between align-items-center bg-white/5">
                            <h5 class="fw-bold text-white mb-0"><i class="bi bi-basket2 me-2 text-info"></i> جدول الأصناف</h5>
                            <div class="badge bg-blue-500/20 text-blue-300 px-3 py-2 rounded-pill">
                                <span x-text="items.length"></span> بنود
                            </div>
                        </div>

                        <div class="table-responsive flex-grow-1">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="bg-white/5 text-gray-400 text-uppercase small fw-bold">
                                    <tr>
                                        <th class="ps-4 py-3" style="width: 5%">#</th>
                                        <th class="py-3" style="width: 35%">المنتج / الخدمة</th>
                                        <th class="text-center py-3" style="width: 12%">الكمية</th>
                                        <th class="text-center py-3" style="width: 15%">السعر</th>
                                        <th class="text-center py-3" style="width: 12%">خصم %</th>
                                        <th class="text-end py-3 pe-4" style="width: 15%">الإجمالي</th>
                                        <th style="width: 6%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="item-row hover:bg-white/5 transition-colors">
                                            <td class="ps-4 text-gray-500 font-monospace" x-text="index + 1"></td>

                                            <!-- Product Select -->
                                            <td class="p-2">
                                                <div class="position-relative">
                                                    <select :name="'lines[' + index + '][product_id]'" 
                                                            class="form-select glass-input-sm text-white fw-bold border-0 shadow-none" 
                                                            x-model="item.product_id" 
                                                            @change="updateProductDetails(index, $event)" required>
                                                        <option value="" class="bg-gray-900">اختر منتج...</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" 
                                                                    data-price="{{ $product->selling_price }}" 
                                                                    data-unit="{{ $product->unit->name ?? '' }}"
                                                                    class="bg-gray-900 py-2">
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-blue-400 ms-2" x-text="item.unit_name"></small>
                                                </div>
                                            </td>

                                            <!-- Qty -->
                                            <td class="p-2">
                                                <input type="number" :name="'lines[' + index + '][quantity]'" 
                                                       class="form-control glass-input-number text-center text-white fw-bold" 
                                                       x-model.number="item.quantity" min="1" step="0.01" required>
                                            </td>

                                            <!-- Price -->
                                            <td class="p-2">
                                                <input type="number" :name="'lines[' + index + '][unit_price]'" 
                                                       class="form-control glass-input-number text-center text-white fw-bold" 
                                                       x-model.number="item.unit_price" min="0" step="0.01" required>
                                            </td>

                                            <!-- Discount -->
                                            <td class="p-2">
                                                <div class="position-relative">
                                                    <input type="number" :name="'lines[' + index + '][discount_percent]'" 
                                                           class="form-control glass-input-number text-center text-warning fw-bold" 
                                                           x-model.number="item.discount_percent" min="0" max="100" step="0.1">
                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-2 text-warning small">%</span>
                                                </div>
                                            </td>

                                            <!-- Total -->
                                            <td class="p-2 text-end pe-4">
                                                <div class="fw-bold fs-6 text-white" x-text="formatMoney(calculateLineTotal(item))"></div>
                                            </td>

                                            <!-- Delete -->
                                            <td class="text-center">
                                                <button type="button" class="btn btn-icon-glass text-danger hover-scale" @click="removeItem(index)" x-show="items.length > 1">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-0">
                            <button type="button" class="btn btn-dark w-100 py-3 text-success fw-bold hover-bg-success-dark transition-all rounded-0 border-top border-white/10" @click="addItem()">
                                <i class="bi bi-plus-circle-fill me-2 fs-5 align-middle"></i> إضافة سطر جديد
                            </button>
                        </div>
                    </div>

                     <!-- Notes & Terms -->
                     <div class="glass-card p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="section-label mb-2"><i class="bi bi-chat-text me-1"></i> ملاحظات</label>
                                <textarea name="notes" class="form-control glass-textarea text-white" rows="3" placeholder="أي ملاحظات إضافية تظهر في العرض..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="section-label mb-2"><i class="bi bi-file-text me-1"></i> الشروط والأحكام</label>
                                <textarea name="terms" class="form-control glass-textarea text-white" rows="3" placeholder="مدة التوريد، طريقة الدفع، الخ..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Summary Sticky -->
                <div class="col-xl-3 col-lg-4">
                    <div class="sticky-top" style="top: 100px;">
                        <div class="glass-card p-4 border-0 shadow-2xl bg-gradient-to-b from-gray-900 to-black position-relative overflow-hidden">
                            <!-- Neon Glow Effect -->
                            <div class="position-absolute bottom-0 start-50 translate-middle-x w-100 h-50 bg-blue-500/10 blur-3xl rounded-circle pointer-events-none"></div>

                            <h5 class="fw-bold text-white mb-4">ملخص الحساب</h5>

                            <div class="d-flex justify-content-between mb-3 text-gray-400">
                                <span>المجموع الفرعي</span>
                                <span class="fw-bold text-white" x-text="formatMoney(totals.subtotal)"></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 text-gray-400">
                                <span>خصم كلي</span>
                                <div class="w-50 position-relative">
                                    <input type="number" name="discount_amount" 
                                           class="form-control glass-input-sm text-end text-warning border-warning/30" 
                                           x-model.number="globalDiscount" min="0" step="0.01">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-4 text-gray-400">
                                <span>الضريبة ({{ number_format($taxRatePercent, 0) }}%)</span>
                                <span class="fw-bold text-white" x-text="formatMoney(totals.tax)"></span>
                            </div>

                            <div class="border-top border-white/10 my-4"></div>

                            <div class="text-center mb-4">
                                <small class="text-gray-500 text-uppercase tracking-widest d-block mb-1">الإجمالي النهائي</small>
                                <h2 class="display-6 fw-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300" x-text="formatMoney(totals.grandTotal)"></h2>
                                <input type="hidden" name="total" :value="totals.grandTotal">
                            </div>

                            <button type="button" @click="submitForm" class="btn btn-success w-100 py-3 fw-bold rounded-xl shadow-neon-success hover-scale shimmer-effect">
                                حفظ واعتماد العرض
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function quotationBuilder() {
            return {
                items: [
                    { product_id: '', quantity: 1, unit_price: 0, discount_percent: 0, unit_name: '' }
                ],
                globalDiscount: 0,

                addItem() {
                    this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount_percent: 0, unit_name: '' });
                    // Scroll to bottom of table logic if needed
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                updateProductDetails(index, event) {
                    let select = event.target;
                    let option = select.options[select.selectedIndex];

                    if (option.value) {
                        this.items[index].unit_price = parseFloat(option.dataset.price) || 0;
                        this.items[index].unit_name = option.dataset.unit || '';
                    } else {
                        this.items[index].unit_price = 0;
                        this.items[index].unit_name = '';
                    }
                },

                calculateLineTotal(item) {
                    let gross = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                    let discount = gross * ((parseFloat(item.discount_percent) || 0) / 100);
                    return Math.max(0, gross - discount);
                },

                get totals() {
                    let subtotal = this.items.reduce((sum, item) => sum + this.calculateLineTotal(item), 0);
                    let grand = subtotal - (parseFloat(this.globalDiscount) || 0);
                    let tax = grand * {{ $taxRate }}; // Tax rate from settings

                    return {
                        subtotal: subtotal,
                        tax: tax,
                        grandTotal: Math.max(0, grand + tax)
                    };
                },

                formatMoney(value) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'EGP'
                    }).format(value);
                },

                submitForm() {
                    // Future: Validation logic here
                    document.getElementById('quotation-form').submit();
                }
            }
        }
    </script>

    <style>
        /* Premium Glass Styles */
        .glass-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .glass-select, .glass-input, .glass-textarea {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            transition: all 0.3s ease;
        }

        /* Force option styling for ALL glass inputs */
        .glass-select option, .glass-input option, .glass-input-sm option, select option {
            background-color: #111827 !important; /* Gray 900 */
            color: #ffffff !important;
            padding: 10px;
        }

        .glass-select:focus, .glass-input:focus, .glass-textarea:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .section-label {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        /* Item Table Inputs */
        .glass-input-sm {
            background: transparent !important;
            border: none !important;
            color: white !important;
            padding-left: 0;
        }
        .glass-input-sm:focus {
            background: rgba(255,255,255,0.05) !important;
            box-shadow: none;
        }

        .glass-input-number {
            background: rgba(0,0,0,0.2) !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
            border-radius: 8px;
        }

        .h-50px { height: 50px; }
        .icon-box {
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
        }

        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: translateY(-2px); }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-glass-outline {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
        }
        .btn-glass-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.2);
        }

        .shimmer-effect {
            position: relative;
            overflow: hidden;
        }
        .shimmer-effect::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            100% { left: 100%; }
        }

        .absolute-glow {
            position: absolute;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(59,130,246,0.3) 0%, transparent 70%);
            filter: blur(40px);
            pointer-events: none;
        }
    </style>
@endsection
