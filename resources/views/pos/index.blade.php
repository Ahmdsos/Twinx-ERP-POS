@extends('layouts.app')

@section('title', 'Twinx POS Pro')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-pro.css') }}">
    <!-- Using Google Fonts for Inter and Arabic support -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&family=Noto+Kufi+Arabic:wght@400;700&display=swap"
        rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', 'Noto Kufi+Arabic', sans-serif;
            margin: 0;
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
    <div class="pos-container" x-data="posEngine()" x-init="init()" x-cloak>

        <!-- PANEL 1: SIDEBAR (Navigation & Shifts) -->
        <aside class="pos-sidebar">
            <div class="p-4 border-bottom border-slate-800 d-flex align-items-center">
                <div class="bg-primary rounded-3 p-2 me-3">
                    <i class="bi bi-grid-fill text-white fs-4"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-black text-white">Twinx ERP - POS</h6>
                    <span class="small text-slate-500">نظام البيع الاحترافي v2.5</span>
                </div>
            </div>

            <nav class="flex-grow-1 p-3">
                <div class="nav-section mb-4">
                    <label class="small text-slate-600 fw-bold mb-2 d-block px-2">العمليات الأساسية</label>
                    <button class="w-100 btn-slate mt-1" :class="activeView === 'catalog' ? 'active' : ''"
                        @click="activeView = 'catalog'">
                        <i class="bi bi-shop me-2"></i> شاشة البيع
                    </button>
                    <button class="w-100 btn-slate mt-1" @click="heldModal.show()">
                        <i class="bi bi-pause-circle me-2"></i> فواتير معلقة
                        <span class="badge bg-primary ms-auto" x-text="parkedSales.length"
                            x-show="parkedSales.length > 0"></span>
                    </button>
                    <button class="w-100 btn-slate mt-3 border-danger-subtle text-danger" @click="showReturnModal()">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> معالجة مرتجع
                    </button>
                </div>

                <div class="nav-section mb-4">
                    <label class="small text-slate-600 fw-bold mb-2 d-block px-2">إدارة الوردية</label>
                    <div class="p-3 bg-slate-950 rounded-4 border border-slate-800">
                        <template x-if="shift">
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-slate-400">الحالة</span>
                                    <span class="badge bg-success">مفتوحة</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-slate-500">بدأت في</span>
                                    <span class="text-white" x-text="formatDate(shift.opened_at)"></span>
                                </div>
                                <button class="btn btn-outline-danger btn-sm w-100 mt-3 fw-bold" @click="closeShift()">
                                    إنهاء الوردية
                                </button>
                            </div>
                        </template>
                        <template x-if="!shift">
                            <div class="text-center py-2">
                                <i class="bi bi-lock-fill text-warning mb-2 d-block fs-3"></i>
                                <p class="small text-slate-400 mb-3">الوردية مغلقة حالياً.</p>
                                <button class="btn btn-primary btn-sm w-100 fw-bold" @click="openShift()">
                                    فتحوردية جديدة
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </nav>

            <div class="p-4 border-top border-slate-800 bg-slate-950/50">
                <div class="d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=3b82f6&color=fff"
                        class="rounded-circle me-3" width="36">
                    <div class="overflow-hidden">
                        <p class="mb-0 small fw-bold text-white text-truncate">{{ auth()->user()->name }}</p>
                        <span class="small text-slate-500">رقم الكاشير: #{{ auth()->id() }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- PANEL 2: MAIN WORKSPACE -->
        <main class="pos-main">
            <!-- GLOBAL SEARCH BAR -->
            <div class="mb-4 d-flex gap-3 align-items-center">
                <div class="flex-grow-1 position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-slate-500"></i>
                    <input type="text" class="pos-input ps-5" placeholder="ابحث بالاسم أو الباركود (F2)..."
                        x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                        @keydown.enter="handleBarcodeSearch()" id="main-pos-search">
                </div>

                <select class="pos-input" style="width: 200px;" x-model="activeCategory" @change="searchProducts()">
                    <option value="">كل التصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select class="pos-input" style="width: 200px;" x-model="warehouseId" @change="searchProducts()">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- CATALOG GRID -->
            <div class="flex-grow-1 overflow-auto pe-2">
                <template x-if="isLoading">
                    <div class="h-100 d-flex align-items-center justify-content-center">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </template>

                <template x-if="!isLoading && products.length > 0">
                    <div class="product-grid">
                        <template x-for="product in products" :key="product.id">
                            <div class="glass-card product-card" @click="addToCart(product)">
                                <div class="product-img mb-3 position-relative">
                                    <img :src="product.image" class="rounded-3 w-100"
                                        style="height: 120px; object-fit: cover;">
                                    <span class="badge position-absolute top-0 end-0 m-2"
                                        :class="product.stock > 10 ? 'bg-success' : 'bg-danger'"
                                        x-text="product.stock + ' في المخزن'"></span>
                                </div>
                                <h6 class="text-white mb-1 fw-bold text-truncate" x-text="product.name"></h6>
                                <p class="text-primary fw-black mb-0" x-text="formatMoney(product.price)"></p>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!isLoading && products.length === 0">
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-slate-600">
                        <i class="bi bi-box-seam fs-1 mb-3"></i>
                        <p class="fw-bold">لم يتم العثور على منتجات مطابقة للبحث.</p>
                    </div>
                </template>
            </div>

            <!-- KEYBOARD SHORTCUTS FOOTER -->
            <div class="pt-4 border-top border-slate-900 mt-auto d-flex gap-4">
                <div class="small"><span class="badge bg-slate-800 me-2 text-slate-400">F2</span> بحث</div>
                <div class="small"><span class="badge bg-slate-800 me-2 text-slate-400">F9</span> خصم</div>
                <div class="small"><span class="badge bg-slate-800 me-2 text-slate-400">F10</span> دفع</div>
                <div class="small"><span class="badge bg-slate-800 me-2 text-slate-400">ESC</span> مسح</div>
            </div>
        </main>

        <!-- PANEL 3: CART & CHECKOUT -->
        <section class="pos-cart-panel">
            <div class="p-4 border-bottom border-slate-800 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-black text-white">سلة المشتريات</h6>
                <button class="btn btn-link link-danger p-0 small text-decoration-none" @click="clearCart()">مسح
                    الكل</button>
            </div>

            <!-- CUSTOMER SELECTION -->
            <div class="p-3 bg-slate-950/50 border-bottom border-slate-800">
                <div class="d-flex gap-2">
                    <select class="pos-input flex-grow-1" x-model="customerId" @change="loadCustomerBrief()">
                        <option value="">عميل نقدي (Walk-in)</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-slate-action" @click="showAddCustomerModal()">
                        <i class="bi bi-person-plus"></i>
                    </button>
                </div>
                <template x-if="customerBrief">
                    <div class="mt-2 px-2 d-flex justify-content-between small">
                        <span class="text-slate-500">حد الائتمان: <span class="text-white"
                                x-text="formatMoney(customerBrief.credit_limit)"></span></span>
                        <span class="text-slate-500">الرصيد: <span class="text-danger"
                                x-text="formatMoney(customerBrief.balance)"></span></span>
                    </div>
                </template>
            </div>

            <!-- CART ITEMS -->
            <div class="flex-grow-1 overflow-auto">
                <template x-if="cart.length === 0">
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-slate-700">
                        <i class="bi bi-cart3 fs-1 mb-3"></i>
                        <p class="small fw-bold">السلة فارغة.</p>
                    </div>
                </template>

                <div class="cart-items-list">
                    <template x-for="(item, index) in cart" :key="item.product_id">
                        <div class="p-3 border-bottom border-slate-950 hover-slate">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white fw-bold small flex-grow-1" x-text="item.name"></span>
                                <button class="ms-2 btn-remove-p" @click="removeFromCart(index)"><i
                                        class="bi bi-x"></i></button>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn-qty-p"
                                        @click="item.quantity > 1 ? item.quantity-- : removeFromCart(index)">-</button>
                                    <input type="number" class="qty-input" x-model.number="item.quantity"
                                        @change="validateQty(item)">
                                    <button class="btn-qty-p" @click="item.quantity++">+</button>
                                </div>
                                <div class="text-end">
                                    <span class="small text-slate-500 d-block"
                                        x-text="formatMoney(item.price) + ' x ' + item.quantity"></span>
                                    <span class="text-white fw-black"
                                        x-text="formatMoney(item.price * item.quantity)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SUMMATION -->
            <div class="p-4 bg-slate-950 border-top border-slate-800">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">المجموع الفرعي</span>
                    <span class="text-white" x-text="formatMoney(subtotal)"></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">الخصم</span>
                    <button class="small text-primary border-0 bg-transparent p-0" @click="showDiscountModal()"
                        x-text="globalDiscount > 0 ? formatMoney(globalDiscount) : 'إضافة خصم'"></button>
                </div>
                <div class="d-flex justify-content-between mb-4 pt-3 border-top border-slate-800">
                    <h4 class="mb-0 text-white fw-black">الإجمالي</h4>
                    <h4 class="mb-0 text-success fw-black" x-text="formatMoney(total)"></h4>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary py-3 fw-black fs-5 shadow-lg" @click="showPaymentModal()"
                        :disabled="cart.length === 0 || isProcessing">
                        <span x-show="!isProcessing">إتمام البيع (Checkout)</span>
                        <span x-show="isProcessing">
                            <div class="spinner-border spinner-border-sm"></div> جاري المعالجة...
                        </span>
                    </button>
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-slate-action w-100 py-2" @click="parkSale()"
                                :disabled="cart.length === 0">
                                <i class="bi bi-pause-fill me-2"></i> تعليق (Park)
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn w-100 py-2" :class="isDelivery ? 'btn-primary' : 'btn-slate-action'"
                                @click="showDeliveryModal()">
                                <i class="bi bi-truck me-2"></i> توصيل (Delivery)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- EXTRA MODALS -->

    <!-- PAYMENT MODAL -->
    <div class="modal fade" id="paymentModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content slate-modal">
                <div class="modal-header border-slate-800">
                    <h5 class="modal-title fw-black text-white">إتمام عملية البيع</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-7 p-4 border-end border-slate-800">
                            <label class="small text-slate-500 fw-bold mb-3 d-block uppercase tracking-wider">اختر طريقة
                                الدفع</label>
                            <div class="row g-3">
                                <template x-for="method in ['cash', 'card', 'bank', 'credit']">
                                    <div class="col-6">
                                        <div class="payment-method-btn"
                                            :class="activePaymentMethod === method ? 'active' : ''"
                                            @click="activePaymentMethod = method">
                                            <i class="bi" :class="{
                                                                                                'bi-cash-stack text-success': method === 'cash',
                                                                                                'bi-credit-card text-primary': method === 'card',
                                                                                                'bi-bank text-warning': method === 'bank',
                                                                                                'bi-piggy-bank text-info': method === 'credit'
                                                                                            }"
                                                class="fs-1 mb-2 d-block"></i>
                                            <span class="fw-bold"
                                                x-text="method === 'cash' ? 'نقدي' : (method === 'card' ? 'بطاقة' : (method === 'bank' ? 'تحويل' : 'آجل'))"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <label class="small text-slate-500 fw-bold mb-2 d-block">المبلغ المدفوع</label>
                                <input type="number" class="pos-input fs-2 fw-black text-center"
                                    x-model.number="paymentAmount" @focus="$event.target.select()">
                            </div>

                            <!-- PAYMENT ACCOUNT SELECTION -->
                            <div class="mt-4">
                                <label class="small text-slate-500 fw-bold mb-2 d-block uppercase tracking-wider">الإيداع
                                    إلى (الخزينة / الحساب)</label>
                                <select class="pos-input" x-model="accountId">
                                    @foreach($paymentAccounts as $account)
                                        <option value="{{ $account->id }}" {{ $account->code == '1100' ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- DELIVERY INFO SUMMARY (If active) -->
                            <template x-if="isDelivery">
                                <div class="mt-4 p-3 bg-primary/10 border border-primary/30 rounded-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 small text-primary fw-bold">طلب توصيل نشط</p>
                                            <p class="mb-0 small text-slate-400" x-text="deliveryAddress"></p>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary"
                                            @click="showDeliveryModal()">تعديل</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="col-md-5 p-4 bg-slate-950/20">
                            <div class="mb-4">
                                <label class="small text-slate-500 fw-bold mb-1 d-block">المطلوب سداده</label>
                                <h2 class="fw-black text-white"
                                    x-text="formatMoney(isDelivery ? total + deliveryFee : total)"></h2>
                            </div>
                            <div class="mb-4">
                                <label class="small text-slate-500 fw-bold mb-1 d-block">المبلغ المستلم</label>
                                <h2 class="fw-black text-primary" x-text="formatMoney(paymentAmount)"></h2>
                            </div>
                            <div class="mb-4 pt-3 border-top border-slate-800">
                                <label class="small text-slate-500 fw-bold mb-1 d-block">المتبقي / الفكة</label>
                                <h2 class="fw-black" :class="paymentAmount >= total ? 'text-success' : 'text-danger'"
                                    x-text="formatMoney(paymentAmount - total)"></h2>
                            </div>

                            <button class="btn btn-primary w-100 py-3 fw-black fs-5 mt-4" @click="processCheckout()"
                                :disabled="(paymentAmount < total && activePaymentMethod !== 'credit') || isProcessing">
                                <span x-show="!isProcessing">تأكيد وطباعة</span>
                                <span x-show="isProcessing">جاري الحفظ...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DISCOUNT MODAL -->
    <div class="modal fade" id="discountModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content slate-modal">
                <div class="modal-body p-4 text-center">
                    <h5 class="fw-black text-white mb-4">خصم على الفاتورة</h5>
                    <div class="input-group mb-3">
                        <input type="number" class="pos-input text-center fs-3" x-model.number="globalDiscount"
                            @focus="$event.target.select()">
                        <span class="input-group-text bg-slate-800 border-slate-700 text-white">ج.م</span>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary fw-bold" data-bs-dismiss="modal">تطبيق الخصم</button>
                        <button class="btn btn-link link-slate small" @click="globalDiscount = 0"
                            data-bs-dismiss="modal">إلغاء الخصم</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECURITY PIN MODAL -->
    <div class="modal fade" id="securityPinModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content slate-modal text-center">
                <div class="modal-body p-4">
                    <i class="bi bi-shield-lock text-warning fs-1 mb-3 d-block"></i>
                    <h5 class="fw-black text-white mb-1">صلاحيات المدير</h5>
                    <p class="small text-slate-500 mb-4">أدخل الرقم السري للمتابعة</p>
                    <input type="password" class="pos-input text-center fs-2 mb-4" x-model="securityPin" maxlength="10"
                        placeholder="••••">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary fw-bold" @click="validatePin()">تأكيد</button>
                        <button class="btn btn-link link-slate small" @click="cancelPin()">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HELD SALES MODAL (Parked Sales Hub) -->
    <div class="modal fade" id="heldSalesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content slate-modal">
                <div class="modal-header border-slate-800">
                    <h5 class="modal-title fw-black text-white">الفواتير المعلقة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <template x-if="parkedSales.length === 0">
                        <div class="p-5 text-center text-slate-600">
                            <i class="bi bi-inboxes fs-1 mb-3 d-block"></i>
                            <p class="fw-bold">لا توجد فواتير معلقة حالياً.</p>
                        </div>
                    </template>
                    <div class="list-group list-group-flush bg-transparent">
                        <template x-for="(sale, idx) in parkedSales" :key="sale.id">
                            <div
                                class="list-group-item bg-transparent border-slate-800 p-4 d-flex justify-content-between align-items-center hover-slate">
                                <div>
                                    <h6 class="text-white mb-1 fw-black">مسودة رقم: #<span x-text="sale.id"></span></h6>
                                    <p class="small text-slate-500 mb-0">
                                        <span x-text="sale.items.length"></span> أصناف •
                                        <span x-text="formatDate(sale.date)"></span>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm fw-bold px-3"
                                        @click="resumeSale(idx)">استرجاع</button>
                                    <button class="btn btn-outline-danger btn-sm" @click="deleteParkedSale(idx)"><i
                                            class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK CUSTOMER MODAL -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content slate-modal">
                <div class="modal-header border-slate-800">
                    <h5 class="modal-title fw-black text-white">إضافة عميل سريع</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-slate-500 fw-bold mb-2 d-block">اسم العميل</label>
                        <input type="text" class="pos-input" x-model="newCustomer.name" placeholder="الاسم ثلاثي">
                    </div>
                    <div class="mb-4">
                        <label class="small text-slate-500 fw-bold mb-2 d-block">رقم الموبايل</label>
                        <input type="text" class="pos-input" x-model="newCustomer.mobile" placeholder="01xxxxxxxxx">
                    </div>
                    <button class="btn btn-primary w-100 py-2 fw-black" @click="quickCreateCustomer()"
                        :disabled="!newCustomer.name">حفظ واختيار العميل</button>
                </div>
            </div>
        </div>
        <!-- SALES RETURN MODAL -->
        <div class="modal fade" id="returnModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content slate-modal">
                    <div class="modal-header border-slate-800">
                        <h5 class="modal-title fw-black text-white">معالجة مرتجع مبيعات</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="small text-slate-500 fw-bold mb-2 d-block uppercase">رقم الفاتورة</label>
                            <div class="d-flex gap-2">
                                <input type="text" class="pos-input" x-model="returnInvoiceNum"
                                    placeholder="مثال: INV-2024-001">
                                <button class="btn btn-primary px-3" @click="searchReturnInvoice()"><i
                                        class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <template x-if="returnInvoice">
                            <div class="bg-slate-950/50 p-3 rounded-4 mb-4 border border-slate-800">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-slate-500">العميل</span>
                                    <span class="text-white fw-bold" x-text="returnInvoice.customer_name"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-slate-500">إجمالي الفاتورة</span>
                                    <span class="text-white fw-bold" x-text="formatMoney(returnInvoice.total)"></span>
                                </div>
                            </div>
                        </template>

                        <div class="d-grid gap-2">
                            <button class="btn btn-danger py-2 fw-black" @click="confirmReturn()"
                                :disabled="!returnInvoice">
                                تأكيد الإرجاع واستعادة المبلغ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SHIFT MODAL -->
        <div class="modal fade" id="shiftModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content slate-modal">
                    <div class="modal-header border-slate-800">
                        <h5 class="modal-title fw-black text-white"
                            x-text="shiftAction === 'open' ? 'فتح الوردية' : 'إغلاق الوردية'"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <template x-if="shiftAction === 'open'">
                            <div>
                                <i class="bi bi-unlock text-success fs-1 mb-3 d-block"></i>
                                <label class="small text-slate-500 fw-bold mb-2 d-block">المبلغ الافتتاحي بالخزينة</label>
                                <input type="number" class="pos-input text-center fs-2 mb-4" x-model.number="shiftCash"
                                    placeholder="0.00">
                                <button class="btn btn-primary w-100 fw-black py-2" @click="submitShift()">تأكيد فتح
                                    الوردية</button>
                            </div>
                        </template>
                        <template x-if="shiftAction === 'close'">
                            <div>
                                <i class="bi bi-lock text-danger fs-1 mb-3 d-block"></i>
                                <label class="small text-slate-500 fw-bold mb-2 d-block">إجمالي الكاش بالمكان</label>
                                <input type="number" class="pos-input text-center fs-2 mb-4" x-model.number="shiftCash"
                                    placeholder="0.00">
                                <p class="small text-warning mb-3">سيتم إنهاء ورديتك الحالية وحفظ البيانات.</p>
                                <button class="btn btn-danger w-100 fw-black py-2" @click="submitShift()">إنهاء الوردية وقفل
                                    الخزينة</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- DELIVERY MODAL -->
        <div class="modal fade" id="deliveryModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content slate-modal">
                    <div class="modal-header border-slate-800">
                        <h5 class="modal-title fw-black text-white">إعدادات التوصيل (Delivery)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-check form-switch mb-4 p-3 bg-slate-900 rounded-3">
                            <input class="form-check-input" type="checkbox" id="deliveryToggleMain" x-model="isDelivery">
                            <label class="form-check-label text-white fw-bold" for="deliveryToggleMain">تفعيل خدمة التوصيل
                                لهذا الأوردر</label>
                        </div>

                        <div :class="!isDelivery ? 'opacity-50 pointer-events-none' : ''">
                            <div class="mb-3">
                                <label class="small text-slate-500 fw-bold mb-2 d-block uppercase">الطيار / السائق</label>
                                <select class="pos-input" x-model="deliveryDriverId">
                                    <option value="">اختر الطيار</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="small text-slate-500 fw-bold mb-2 d-block uppercase">عنوان التوصيل</label>
                                <input type="text" class="pos-input" x-model="deliveryAddress"
                                    placeholder="عنوان العميل التفصيلي">
                            </div>
                            <div class="mb-4">
                                <label class="small text-slate-500 fw-bold mb-2 d-block uppercase">رسوم التوصيل
                                    (ج.م)</label>
                                <input type="number" class="pos-input" x-model.number="deliveryFee">
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 py-2 fw-black" data-bs-dismiss="modal">حفظ وإغلاق</button>
                    </div>
                </div>
            </div>
        </div>

@endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            // Global Axios Configuration for POS
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        </script>
        <script>
            function posEngine() {
                return {
                    activeView: 'catalog',
                    activeCategory: '',
                    searchQuery: '',
                    isLoading: false,
                    isProcessing: false,
                    products: [],
                    cart: [],
                    parkedSales: JSON.parse(localStorage.getItem('pos_parked_sales') || '[]'),
                    customerId: '',
                    customerBrief: null,
                    globalDiscount: 0,
                    shift: @json($activeShift),
                    activePaymentMethod: 'cash',
                    paymentAmount: 0,
                    securityPin: '',
                    pendingAction: null,

                    // Delivery State
                    isDelivery: false,
                    deliveryDriverId: '',
                    deliveryAddress: '',
                    deliveryFee: 0,
                    warehouseId: @json($warehouses->first()?->id ?? 1),
                    accountId: @json($paymentAccounts->where('code', '1100')->first()?->id ?? $paymentAccounts->first()?->id),

                    // Bootstrap Modal instances
                    paymentModal: null,
                    discountModal: null,
                    pinModal: null,
                    heldModal: null,
                    customerModal: null,
                    shiftModal: null,
                    deliveryModal: null,
                    shiftAction: 'open',
                    shiftCash: 0,
                    newCustomer: { name: '', mobile: '' },

                    init() {
                        this.searchProducts();
                        this.setupKeyBindings();
                        this.$nextTick(() => {
                            document.getElementById('main-pos-search').focus();
                            this.paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                            this.discountModal = new bootstrap.Modal(document.getElementById('discountModal'));
                            this.pinModal = new bootstrap.Modal(document.getElementById('securityPinModal'));
                            this.heldModal = new bootstrap.Modal(document.getElementById('heldSalesModal'));
                            this.customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
                            this.returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
                            this.shiftModal = new bootstrap.Modal(document.getElementById('shiftModal'));
                            this.deliveryModal = new bootstrap.Modal(document.getElementById('deliveryModal'));
                        });
                    },

                    // Return Logic State
                    returnInvoiceNum: '',
                    returnInvoice: null,
                    returnModal: null,

                    async searchProducts() {
                        this.isLoading = true;
                        try {
                            const params = new URLSearchParams({
                                query: this.searchQuery,
                                category_id: this.activeCategory,
                                warehouse_id: this.warehouseId
                            });
                            const res = await axios.get('/pos/products/search?' + params.toString());
                            this.products = res.data;
                        } catch (e) {
                            console.error("Search Fail:", e);
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    handleBarcodeSearch() {
                        if (!this.searchQuery) return;
                        axios.get('/pos/products/search?barcode=' + this.searchQuery).then(res => {
                            if (res.data.length === 1) {
                                this.addToCart(res.data[0]);
                                this.searchQuery = '';
                            }
                        });
                    },

                    addToCart(product) {
                        const existing = this.cart.find(i => i.product_id === product.id);
                        if (existing) {
                            existing.quantity++;
                        } else {
                            this.cart.push({
                                product_id: product.id,
                                name: product.name,
                                price: parseFloat(product.price),
                                quantity: 1,
                                tax_rate: product.tax_rate || 0
                            });
                        }
                        this.playBeep();
                    },

                    removeFromCart(index) {
                        // Sensitive action: Require PIN for deletions
                        this.pendingAction = () => {
                            this.cart.splice(index, 1);
                            this.pinModal.hide();
                        };
                        this.pinModal.show();
                    },

                    validatePin() {
                        axios.post('/pos/pin/validate', { pin: this.securityPin }).then(res => {
                            if (res.data.success) {
                                this.securityPin = '';
                                if (this.pendingAction) this.pendingAction();
                                this.pendingAction = null;
                            } else {
                                alert('الرقم السري غير صحيح');
                            }
                        }).catch(() => alert('فشل الاتصال بنظام الأمان'));
                    },

                    cancelPin() {
                        this.securityPin = '';
                        this.pendingAction = null;
                        this.pinModal.hide();
                    },

                    clearCart() {
                        if (confirm('هل تريد مسح السلة بالكامل؟')) this.cart = [];
                    },

                    validateQty(item) {
                        if (item.quantity < 1) item.quantity = 1;
                    },

                    async loadCustomerBrief() {
                        if (!this.customerId) { this.customerBrief = null; return; }
                        const res = await axios.get('/pos/customers/' + this.customerId + '/brief');
                        this.customerBrief = res.data;

                        // Auto-fill delivery data if exists
                        if (this.customerBrief) {
                            this.deliveryAddress = this.customerBrief.address || '';
                            // If delivery fee is stored in settings/customer, we could pull it too. 
                            // For now we just pre-fill address to save time.
                        }
                    },

                    get subtotal() {
                        return Number(this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2));
                    },

                    get total() {
                        return Number(Math.max(0, this.subtotal - this.globalDiscount).toFixed(2));
                    },

                    showPaymentModal() {
                        this.paymentAmount = this.total;
                        this.paymentModal.show();
                    },

                    showDiscountModal() {
                        this.discountModal.show();
                    },

                    async processCheckout() {
                        if (this.isProcessing) return;
                        this.isProcessing = true;
                        try {
                            const res = await axios.post('/pos/checkout', {
                                items: this.cart,
                                payments: [{ method: this.activePaymentMethod, amount: this.paymentAmount, account_id: this.accountId }],
                                customer_id: this.customerId,
                                discount: this.globalDiscount,
                                account_id: this.accountId, // Ensure top-level account_id is sent too
                                warehouse_id: this.warehouseId,
                                is_delivery: this.isDelivery,
                                delivery_driver_id: this.deliveryDriverId,
                                delivery_address: this.deliveryAddress,
                                delivery_fee: this.deliveryFee
                            });

                            if (res.data.success) {
                                this.playSuccess();
                                this.paymentModal.hide();
                                this.cart = [];
                                this.globalDiscount = 0;
                                this.customerId = '';
                                this.customerBrief = null;
                                if (confirm('تمت العملية بنجاح! هل تريد طباعة الإيصال؟')) {
                                    window.open('/pos/receipt/' + res.data.invoice.id, '_blank');
                                }
                            }
                        } catch (e) {
                            alert('فشل إتمام العملية: ' + (e.response?.data?.message || 'خطأ في النظام'));
                        } finally {
                            this.isProcessing = false;
                        }
                    },

                    parkSale() {
                        const sale = {
                            id: Date.now(),
                            items: JSON.parse(JSON.stringify(this.cart)),
                            customerId: this.customerId,
                            discount: this.globalDiscount,
                            date: new Date()
                        };
                        this.parkedSales.push(sale);
                        localStorage.setItem('pos_parked_sales', JSON.stringify(this.parkedSales));
                        this.cart = [];
                        this.customerId = '';
                        this.globalDiscount = 0;
                        alert('تم تعليق الفاتورة بنجاح.');
                    },

                    resumeSale(idx) {
                        const sale = this.parkedSales[idx];
                        this.cart = JSON.parse(JSON.stringify(sale.items));
                        this.customerId = sale.customerId;
                        this.globalDiscount = sale.discount;
                        this.deleteParkedSale(idx);
                        this.heldModal.hide();
                        this.loadCustomerBrief();
                    },

                    deleteParkedSale(idx) {
                        this.parkedSales.splice(idx, 1);
                        localStorage.setItem('pos_parked_sales', JSON.stringify(this.parkedSales));
                    },

                    openShift() {
                        this.shiftAction = 'open';
                        this.shiftCash = 0;
                        this.shiftModal.show();
                    },

                    closeShift() {
                        this.shiftAction = 'close';
                        this.shiftCash = 0;
                        this.shiftModal.show();
                    },

                    submitShift() {
                        const url = this.shiftAction === 'open' ? '/pos/shift/open' : '/pos/shift/close';
                        const payload = this.shiftAction === 'open'
                            ? { opening_cash: parseFloat(this.shiftCash) }
                            : { closing_cash: parseFloat(this.shiftCash) };

                        axios.post(url, payload).then(() => {
                            location.reload();
                        }).catch(e => {
                            alert('خطأ في العملية: ' + (e.response?.data?.message || 'فشل الاتصال'));
                        });
                    },

                    showDeliveryModal() {
                        this.deliveryModal.show();
                    },

                    showAddCustomerModal() {
                        this.newCustomer = { name: '', mobile: '' };
                        this.customerModal.show();
                    },

                    async quickCreateCustomer() {
                        try {
                            const res = await axios.post('/pos/customers/quick-create', this.newCustomer);
                            if (res.data.success) {
                                // Add to the select dropdown (local update)
                                const select = document.querySelector('select[x-model="customerId"]');
                                const opt = document.createElement('option');
                                opt.value = res.data.customer.id;
                                opt.text = res.data.customer.name;
                                select.add(opt);

                                this.customerId = res.data.customer.id;
                                this.loadCustomerBrief();
                                this.customerModal.hide();
                            }
                        } catch (e) {
                            alert('Failed to create customer');
                        }
                    },

                    showReturnModal() {
                        this.returnInvoiceNum = '';
                        this.returnInvoice = null;
                        this.returnModal.show();
                    },

                    async searchReturnInvoice() {
                        try {
                            const res = await axios.get('/pos/invoice/search?invoice_number=' + this.returnInvoiceNum);
                            this.returnInvoice = res.data;
                        } catch (e) {
                            alert('Invoice not found');
                        }
                    },

                    confirmReturn() {
                        this.pendingAction = async () => {
                            try {
                                const res = await axios.post('/pos/sales-return', {
                                    invoice_id: this.returnInvoice.id
                                });
                                if (res.data.success) {
                                    alert('تم معالجة المرتجع بنجاح');
                                    this.returnModal.hide();
                                    this.playSuccess();
                                }
                            } catch (e) {
                                alert('فشل المرتجع: ' + (e.response?.data?.message || 'خطأ'));
                            }
                        };
                        this.pinModal.show();
                    },

                    formatMoney(v) {
                        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'EGP' }).format(v);
                    },

                    formatDate(d) {
                        return new Date(d).toLocaleString();
                    },

                    setupKeyBindings() {
                        window.addEventListener('keydown', (e) => {
                            if (e.key === 'F2') { e.preventDefault(); document.getElementById('main-pos-search').focus(); }
                            if (e.key === 'F9') { e.preventDefault(); this.showDiscountModal(); }
                            if (e.key === 'F10') { e.preventDefault(); this.showPaymentModal(); }
                            if (e.key === 'Escape') { this.searchQuery = ''; }
                        });
                    },

                    playBeep() { /* beep logic if audio assets exist */ },
                    playSuccess() { /* success logic */ }
                };
            }
        </script>
    @endpush