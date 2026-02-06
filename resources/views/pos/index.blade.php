@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- POS CLEAN SLATE: GLOBAL STANDARD RECONSTRUCTION v3.3 (Layout Fixes) -->
    <div class="pos-workspace d-flex vh-100 overflow-hidden bg-slate-900" x-data="posStore()" x-init="initPOS()"
        @keydown.window="handleGlobalKeys($event)">

        <!-- ==================== LEFT: CART & ACTION HUB (Fixed Width) ==================== -->
        <aside class="pos-cart-panel bg-slate-950 border-end border-slate-800 d-flex flex-column shadow-2xl z-30"
            style="width: 400px; min-width: 400px;">

            <!-- HEADER: SHIFT & WAREHOUSE -->
            <header class="p-3 border-bottom border-slate-800 bg-slate-900 shadow-sm">
                <div class="d-flex gap-2 mb-3">
                    <!-- SHIFT INDICATOR -->
                    <div class="d-flex align-items-center gap-2 cursor-pointer hover:bg-slate-800 p-2 rounded transition-all flex-grow-1 border border-slate-800"
                        @click="showShiftModal()">
                        <div
                            class="w-8 h-8 rounded-circle bg-emerald-500/20 text-emerald-400 d-flex align-items-center justify-content-center border border-emerald-500/30">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="lh-1">
                            <div class="text-xs text-slate-400 fw-bold text-uppercase">الوردية
                                #{{ $activeShift->id ?? 'VOID' }}</div>
                            <div class="text-white text-xs fw-bold mt-1">{{ auth()->user()->name }}</div>
                        </div>
                    </div>

                    <!-- WAREHOUSE FILTER -->
                    <div class="dropdown">
                        <button
                            class="btn btn-slate-800 border border-slate-700 text-white h-100 d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i>
                            <span class="fs-xs" x-text="getWarehouseName()">المخزن</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-slate-700 font-mono fs-sm"
                            style="max-height: 200px; overflow-y: auto;">
                            @foreach($warehouses as $w)
                                <li><a class="dropdown-item" href="#"
                                        @click.prevent="setWarehouse({{ $w->id }}, '{{ $w->name }}')">{{ $w->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- CUSTOMER SEARCH -->
                <div class="position-relative">
                    <div class="input-group input-group-slate shadow-sm border border-slate-700">
                        <span class="input-group-text bg-transparent border-0 text-slate-400 ps-3"><i
                                class="bi bi-person-circle fs-5"></i></span>
                        <input type="text" class="form-control bg-transparent border-0 text-white shadow-none py-2"
                            placeholder="ابحث عن عميل (F2)..." x-model="searchQuery" @keydown.enter="searchCustomer()">
                        <button class="btn btn-link text-emerald-400 decoration-none fw-bold small pe-3"
                            @click="openAddCustomerModal()">
                            <i class="bi bi-plus-lg"></i> جديد
                        </button>
                    </div>
                </div>
            </header>

            <!-- CART ITEMS (SCROLLABLE) -->
            <div class="flex-grow-1 overflow-auto custom-scrollbar p-2" id="cart-items-container">
                <template x-if="cart.length === 0">
                    <div
                        class="h-100 d-flex flex-column align-items-center justify-content-center text-slate-600 opacity-50 select-none">
                        <div
                            class="w-24 h-24 rounded-circle bg-slate-800/50 d-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-cart3 fs-1 text-slate-700"></i>
                        </div>
                        <p class="fw-bold fs-5">السلة فارغة</p>
                        <p class="small">امسح الباركود لبدء البيع</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.id + '-' + index">
                    <div class="cart-item p-3 mb-2 rounded-3 bg-slate-900 border border-slate-800 position-relative group hover:border-slate-600 transition-all cursor-pointer"
                        :class="{'border-emerald-500/50 bg-emerald-900/10 ring-1 ring-emerald-500/30': selectedItemIndex === index}"
                        @click="selectItem(index)">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-white text-md w-100 truncate mb-1" x-text="item.name"></div>
                                <div class="text-xs text-slate-500 font-mono" x-show="item.sku">SKU: <span
                                        x-text="item.sku"></span></div>
                            </div>
                            <div class="fw-black text-emerald-400 text-lg font-mono" x-text="formatMoney(item.subtotal)">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <!-- QTY CONTROL -->
                            <div
                                class="d-flex align-items-center bg-slate-950 rounded border border-slate-800 p-1 shadow-sm">
                                <button
                                    class="btn btn-sm btn-icon text-slate-400 hover:text-white hover:bg-slate-800 rounded"
                                    @click.stop="updateQty(index, -1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="mx-3 fw-bold text-white font-mono fs-5" x-text="item.qty"></span>
                                <button
                                    class="btn btn-sm btn-icon text-slate-400 hover:text-white hover:bg-slate-800 rounded"
                                    @click.stop="updateQty(index, 1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            <div
                                class="text-slate-400 fs-xs font-mono px-2 py-1 rounded bg-slate-950/50 border border-slate-800/50">
                                <span x-text="formatMoney(item.price)"></span>
                            </div>

                            <button
                                class="btn btn-sm text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-rose-900/20 rounded p-1"
                                @click.stop="removeItem(index)">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- TOTALS & ACTION PAD -->
            <footer class="p-3 bg-slate-900 border-top border-slate-800 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] mt-auto z-10">
                <!-- Summary Grid -->
                <div class="row g-2 mb-3 text-slate-400 fs-sm px-1">
                    <div class="col-6 d-flex justify-content-between">
                        <span>المجموع الفرعي:</span>
                        <span class="text-white fw-bold font-mono" x-text="formatMoney(cartSubtotal)"></span>
                    </div>
                    <div class="col-6 d-flex justify-content-between">
                        <span>الضريبة (14%):</span>
                        <span class="text-white fw-bold font-mono" x-text="formatMoney(cartTax)"></span>
                    </div>
                    <div class="col-12 border-top border-slate-800 my-2"></div>
                    <div class="col-12 d-flex justify-content-between align-items-end">
                        <span class="fs-5 fw-bold text-white">الإجمالي النهائي</span>
                        <span class="fs-2 fw-black text-emerald-400 font-mono tracking-tight"
                            x-text="formatMoney(cartTotal)"></span>
                    </div>
                </div>

                <!-- Big Buttons -->
                <div class="d-grid gap-2 grid-cols-2">
                    <button class="btn btn-slate-800 py-3 text-white fw-bold hover:bg-slate-700 border border-slate-700"
                        @click="holdSale()">
                        <i class="bi bi-pause-circle me-2"></i> تعليق (Hold)
                    </button>
                    <button
                        class="btn btn-rose-900/20 text-rose-400 py-3 fw-bold border border-rose-900/30 hover:bg-rose-900/40 hover:text-rose-200"
                        @click="clearCart()">
                        <i class="bi bi-trash3 me-2"></i> إلغاء
                    </button>
                    <button
                        class="btn btn-emerald-600 py-4 text-white fw-black col-span-2 shadow-emerald-glow hover:bg-emerald-500 active:scale-[0.98] transition-all rounded-xl relative overflow-hidden"
                        @click="showPaymentModal()" :disabled="cart.length === 0">
                        <div class="d-flex justify-content-between px-4 align-items-center relative z-10">
                            <span class="fs-5"><i class="bi bi-credit-card-2-back me-2"></i> سداد (F10)</span>
                            <span class="btn-spinner" x-show="isProcessing"></span>
                            <span class="fs-5 font-mono bg-emerald-700/50 px-2 py-1 rounded"
                                x-text="formatMoney(cartTotal)"></span>
                        </div>
                    </button>
                </div>
            </footer>
        </aside>


        <!-- ==================== CENTER: PRODUCT GRID (Dynamic) ==================== -->
        <main
            class="flex-grow-1 bg-slate-900 d-flex flex-column position-relative border-end border-slate-800 overflow-hidden"
            style="background-image: radial-gradient(#1e293b 1px, transparent 1px); background-size: 20px 20px;">

            <!-- SEARCH BAR -->
            <div class="p-3 bg-slate-900/95 backdrop-blur border-bottom border-slate-800 d-flex gap-3 sticky-top z-20">
                <div class="input-group input-group-lg input-group-slate flex-grow-1 shadow-sm border border-slate-700">
                    <span class="input-group-text bg-transparent border-0 text-slate-400 ps-3"><i
                            class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-transparent border-0 text-white shadow-none"
                        placeholder="بحث عن منتج (اسم / كود / باركود)..." x-model="productSearch" id="product-search-input"
                        @input.debounce.300ms="fetchProducts()">
                    <button class="btn btn-link text-slate-400 hover:text-white" x-show="productSearch"
                        @click="productSearch=''; fetchProducts()"><i class="bi bi-x-lg"></i></button>
                </div>
                <button class="btn btn-slate-800 text-white px-4 border border-slate-700 rounded-lg"
                    @click="scanMode = !scanMode"
                    :class="{'bg-emerald-900/50 border-emerald-500 text-emerald-400': scanMode}">
                    <i class="bi bi-qr-code-scan fs-5"></i>
                </button>
            </div>

            <!-- CATEGORY PILLS (Scrollable) -->
            <div class="px-3 py-3 d-flex gap-2 align-items-center w-100 border-bottom border-slate-800 bg-slate-950/50"
                style="overflow-x: auto; white-space: nowrap; min-height: 65px; scrollbar-width: thin;">
                <button class="category-pill active fw-bold" @click="filterCategory(null)">الكل</button>
                @foreach($categories as $cat)
                    <button class="category-pill fw-bold" @click="filterCategory({{ $cat->id }})">{{ $cat->name }}</button>
                @endforeach
            </div>

            <!-- PRODUCTS GRID -->
            <div class="flex-grow-1 overflow-auto p-4 custom-scrollbar" id="product-grid">
                <!-- SPINNER OVERLAY -->
                <div x-show="isLoading" class="flex-column justify-content-center align-items-center h-100 w-100"
                    :class="{'d-flex': isLoading, 'd-none': !isLoading}">
                    <div class="spinner-border text-emerald-500 mb-3" style="width: 3rem; height: 3rem;" role="status">
                    </div>
                    <div class="text-slate-400 animate-pulse">جاري تحميل المنتجات...</div>
                </div>

                <div class="d-grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));"
                    x-show="!isLoading">
                    <template x-for="product in products" :key="product.id">
                        <!-- Card: Twinx Motion v3 (CSS-Based) -->
                        <div class="twinx-card group" @click="addToCart(product)">

                            <!-- Image Area -->
                            <div class="twinx-img-container">
                                <!-- Image/Placeholder -->
                                <img :src="product.image || `https://ui-avatars.com/api/?name=${encodeURIComponent(product.name)}&background=0f172a&color=94a3b8&bold=true&length=2&size=256&font-size=0.35`"
                                    class="twinx-img">

                                <div class="twinx-overlay"></div>

                                <!-- Overlay Add Button (Animated) -->
                                <div class="twinx-add-btn">
                                    <i class="bi bi-cart-plus-fill fs-5"></i>
                                </div>

                                <!-- Stock Pill -->
                                <div class="twinx-pill" :class="{
                                                                             'text-emerald-400 border-emerald-500/50': product.stock > 10,
                                                                             'text-amber-400 border-amber-500/50': product.stock <= 10 && product.stock > 0,
                                                                             'text-rose-400 border-rose-500/50': product.stock <= 0
                                                                         }">
                                    <i class="bi" :class="product.stock > 0 ? 'bi-box-seam' : 'bi-x-circle'"></i>
                                    <span x-text="product.stock > 0 ? product.stock : 'نفذ'"></span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="twinx-body">
                                <h6 class="text-white fw-bold text-sm leading-snug line-clamp-2 min-h-[2.5em] mb-1 group-hover:text-emerald-300 transition-colors"
                                    x-text="product.name"></h6>

                                <div class="mb-2">
                                    <span
                                        class="text-[10px] text-slate-500 font-mono tracking-widest bg-slate-950/30 px-1.5 py-0.5 rounded border border-slate-800/50"
                                        x-text="product.sku || '---'"></span>
                                </div>

                                <div class="mt-auto d-flex justify-content-center pt-2 border-top border-slate-700/50">
                                    <div class="d-flex align-items-baseline gap-1">
                                        <span class="text-white fw-bold text-xl leading-none tracking-tight"
                                            x-text="formatNumber(product.price)"></span>
                                        <span class="text-[11px] text-emerald-500 fw-bold uppercase">EGP</span>
                                    </div>

                                    <!-- Button Removed -->
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </main>


        <!-- ==================== RIGHT: QUICK ACCESS & KEYBOARD (Fixed Width) ==================== -->
        <aside class="d-none d-xxl-flex flex-column bg-slate-950 border-start border-slate-800 p-3 gap-3"
            style="width: 280px; min-width: 280px;">
            <!-- CLOCK & INFO -->
            <div class="bg-slate-900 rounded p-3 border border-slate-800 text-center shadow-sm">
                <div class="text-white fs-4 fw-black font-mono tracking-widest" x-text="currentTime">00:00:00</div>
                <div class="text-slate-400 fs-xs text-uppercase mt-1">{{ now()->format('l, d F Y') }}</div>
            </div>

            <h6 class="text-slate-500 fw-bold fs-xs text-uppercase tracking-wider mt-2 mb-1 px-1">العمليات</h6>

            <div class="d-grid gap-2">
                <button class="btn btn-slate-action py-3 text-start px-3 relative overflow-hidden"
                    @click="setRefundMode(!refundMode)"
                    :class="{'bg-rose-900/20 border-rose-500 text-rose-400': refundMode}">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-slate-800 d-flex align-items-center justify-content-center me-3 border border-slate-700">
                            <i class="bi bi-arrow-return-left"></i>
                        </div>
                        <span class="fw-bold">مرتجع (RMA)</span>
                    </div>
                </button>

                <button class="btn btn-slate-action py-3 text-start px-3" @click="openExpenseModal()">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-slate-800 d-flex align-items-center justify-content-center me-3 border border-slate-700">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <span class="fw-bold">مصروفات نثرية</span>
                    </div>
                </button>

                <button class="btn btn-slate-action py-3 text-start px-3" @click="toggleFullScreen()">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-slate-800 d-flex align-items-center justify-content-center me-3 border border-slate-700">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </div>
                        <span class="fw-bold">ملء الشاشة</span>
                    </div>
                </button>
            </div>

            <h6 class="text-slate-500 fw-bold fs-xs text-uppercase tracking-wider mt-4 mb-1 px-1">اختصارات</h6>

            <div class="d-grid gap-2">
                <div
                    class="d-flex justify-content-between align-items-center px-2 py-1 bg-slate-900/50 rounded border border-slate-800/50">
                    <span class="text-slate-400 fs-sm">بحث</span>
                    <kbd class="bg-slate-800 text-slate-300 border-slate-600">F2</kbd>
                </div>
                <div
                    class="d-flex justify-content-between align-items-center px-2 py-1 bg-slate-900/50 rounded border border-slate-800/50">
                    <span class="text-slate-400 fs-sm">دفع</span>
                    <kbd class="bg-slate-800 text-slate-300 border-slate-600">F10</kbd>
                </div>
                <div
                    class="d-flex justify-content-between align-items-center px-2 py-1 bg-slate-900/50 rounded border border-slate-800/50">
                    <span class="text-slate-400 fs-sm">طباعة آخر فاتورة</span>
                    <kbd class="bg-slate-800 text-slate-300 border-slate-600">F8</kbd>
                </div>
            </div>

            <div class="mt-auto">
                <div class="bg-slate-900 rounded p-3 border border-slate-800 mb-2">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-circle bg-emerald-500 animate-pulse shadow-[0_0_10px_#10b981]"></div>
                        <span class="text-emerald-400 fw-bold fs-sm">النظام متصل</span>
                    </div>
                    <div class="text-slate-500 fs-xs font-mono">Terminal: TWINX-POS-01</div>
                </div>
                <a href="{{ route('dashboard') }}"
                    class="btn btn-outline-rose-500/50 text-rose-400 w-100 py-2 border-slate-800 hover:bg-rose-900/20">
                    <i class="bi bi-box-arrow-left me-2"></i> خروج
                </a>
            </div>
        </aside>

        <!-- ==================== MODALS HUB ==================== -->

        <!-- 0. SHIFT MODAL -->
        <div class="modal fade" id="shiftModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content slate-modal shadow-lg">
                    <div class="modal-header border-slate-800">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-clock-history me-2"></i> إدارة الوردية
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-900">
                        <div class="text-center mb-4">
                            <div
                                class="d-inline-flex align-items-center justify-content-center w-16 h-16 rounded-circle bg-emerald-500/10 text-emerald-400 mb-3 border border-emerald-500/20">
                                <i class="bi bi-person-badge fs-3"></i>
                            </div>
                            <h5 class="text-white fw-bold">{{ auth()->user()->name }}</h5>
                            <div class="text-slate-500 fs-sm">مدير النظام</div>
                        </div>

                        <div class="bg-slate-950 rounded p-3 border border-slate-800 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-slate-400">رقم الوردية:</span>
                                <span class="text-white font-mono fw-bold">#{{ $activeShift->id ?? 'NEW' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-slate-400">وقت البدء:</span>
                                <span
                                    class="text-white font-mono">{{ $activeShift->created_at->format('H:i A') ?? '--' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-slate-400">المبيعات (كاش):</span>
                                <span class="text-emerald-400 font-mono fw-bold">EGP 0.00</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-rose-500 hover:bg-rose-900/20 py-2" @click="closeShift()">
                                <i class="bi bi-door-closed me-2"></i> إغلاق الوردية (Z-Report)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. PAYMENT MODAL (Global Standard Multi-Tender) -->
        <div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content slate-modal shadow-2xl overflow-hidden">
                    <div class="modal-header border-slate-700 bg-slate-950 p-4">
                        <div>
                            <h4 class="modal-title fw-black text-white">إتمام الدفع</h4>
                            <div class="text-slate-400 fs-sm mt-1">فاتورة رقم: <span
                                    class="font-mono text-emerald-400">#NEW</span></div>
                        </div>
                        <div class="text-end">
                            <div class="fs-6 text-slate-400">الإجمالي المستحق</div>
                            <div class="fs-2 fw-black text-emerald-400 font-mono" x-text="formatMoney(cartTotal)"></div>
                        </div>
                    </div>
                    <div class="modal-body p-0 d-flex bg-slate-900" style="height: 500px;">
                        <!-- Payment Methods (Left) -->
                        <div class="w-25 border-end border-slate-800 bg-slate-950 p-2 d-flex flex-column gap-2">
                            <button class="btn btn-slate-action py-3 text-start active" @click="activePaymentTab = 'cash'"
                                :class="{'bg-emerald-900/20 border-emerald-500 text-white': activePaymentTab === 'cash'}">
                                <i class="bi bi-cash-stack me-2 text-emerald-500"></i> كاش
                            </button>
                            <button class="btn btn-slate-action py-3 text-start" @click="activePaymentTab = 'card'"
                                :class="{'bg-blue-900/20 border-blue-500 text-white': activePaymentTab === 'card'}">
                                <i class="bi bi-credit-card me-2 text-blue-500"></i> بطاقة بنكية
                            </button>
                            <button class="btn btn-slate-action py-3 text-start" @click="activePaymentTab = 'credit'"
                                :class="{'bg-amber-900/20 border-amber-500 text-white': activePaymentTab === 'credit'}">
                                <i class="bi bi-person-badge me-2 text-amber-500"></i> أجل (عميل)
                            </button>
                        </div>

                        <!-- Payment Input (Right) -->
                        <div class="w-75 p-4 d-flex flex-column relative">
                            <!-- CASH VIEW -->
                            <div x-show="activePaymentTab === 'cash'" class="fade-in">
                                <label class="form-label text-slate-400">المبلغ المدفوع</label>
                                <div
                                    class="input-group input-group-lg input-group-slate mb-4 border border-emerald-500/50 shadow-[0_0_15px_rgba(16,185,129,0.1)]">
                                    <span class="input-group-text bg-transparent border-0 text-emerald-500 fs-4">EGP</span>
                                    <input type="number"
                                        class="form-control bg-transparent border-0 text-white fs-3 font-mono fw-bold"
                                        x-model="paymentAmount" id="payment-input">
                                </div>

                                <!-- FAST CASH -->
                                <div class="d-grid grid-cols-4 gap-2 mb-4">
                                    <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                        @click="paymentAmount = 50">50</button>
                                    <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                        @click="paymentAmount = 100">100</button>
                                    <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                        @click="paymentAmount = 200">200</button>
                                    <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                        @click="paymentAmount = cartTotal">PL. All</button>
                                </div>

                                <div class="p-3 rounded bg-slate-950 border border-slate-800">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-slate-400">المطلوب:</span>
                                        <span class="text-white font-mono" x-text="formatMoney(cartTotal)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-slate-400">المدفوع:</span>
                                        <span class="text-emerald-400 font-mono"
                                            x-text="formatMoney(paymentAmount || 0)"></span>
                                    </div>
                                    <div class="border-top border-slate-800 my-2"></div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold"
                                            :class="remainingAmount > 0 ? 'text-rose-400' : 'text-slate-400'">المتبقي:</span>
                                        <span class="fw-black font-mono"
                                            :class="remainingAmount > 0 ? 'text-rose-400' : 'text-slate-400'"
                                            x-text="formatMoney(remainingAmount)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2" x-show="changeAmount > 0">
                                        <span class="fw-bold text-blue-400">الباقي للعميل (Change):</span>
                                        <span class="fw-black text-blue-400 font-mono"
                                            x-text="formatMoney(changeAmount)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <button class="btn btn-slate-700 flex-grow-1 py-3" @click="closePaymentModal()">إلغاء
                                    (Esc)</button>
                                <button class="btn btn-emerald-600 flex-grow-1 py-3 fw-bold shadow-lg"
                                    :disabled="remainingAmount > 0.1 && activePaymentTab !== 'credit'"
                                    @click="processCheckout()">
                                    <span x-show="!isProcessing"><i class="bi bi-check-lg me-2"></i> تأكيد وطباعة
                                        (Enter)</span>
                                    <span x-show="isProcessing" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. ADD CUSTOMER MODAL -->
        <div class="modal fade" id="addCustomerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content slate-modal shadow-2xl">
                    <div class="modal-header border-slate-800">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-person-plus me-2"></i> إضافة عميل جديد
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-900/50">
                        <div class="mb-3">
                            <label class="form-label text-slate-400 small fw-bold">اسم العميل</label>
                            <input type="text" x-model="newCustomer.name"
                                class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                placeholder="مثلاً: شركة التميز">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-slate-400 small fw-bold">رقم الموبايل</label>
                            <input type="text" x-model="newCustomer.mobile"
                                class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                placeholder="01xxxxxxxxx">
                        </div>
                        <button class="btn btn-primary w-100 py-3 fw-bold" @click="quickCreateCustomer()"
                            :disabled="!newCustomer.name">حفظ وإضافة</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* ... Existing CSS + New Fixes ... */
        :root {
            --slate-900: #0f172a;
            --slate-950: #020617;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --rose-500: #f43f5e;
        }

        body {
            background-color: var(--slate-950);
            font-family: 'Tajawal', sans-serif;
        }

        .bg-slate-900 {
            background-color: var(--slate-900) !important;
        }

        .bg-slate-950 {
            background-color: var(--slate-950) !important;
        }

        .bg-slate-800 {
            background-color: var(--slate-800) !important;
        }

        .text-emerald-400 {
            color: #34d399 !important;
        }

        .border-slate-800 {
            border-color: var(--slate-800) !important;
        }

        .border-slate-700 {
            border-color: var(--slate-700) !important;
        }

        .input-group-slate {
            background: var(--slate-800);
            border-radius: 12px;
            overflow: hidden;
            transition: 0.2s;
        }

        .input-group-slate:focus-within {
            border-color: var(--emerald-500) !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: var(--slate-950);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--slate-700);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--slate-600);
        }

        .category-pill {
            background: var(--slate-800);
            color: #94a3b8;
            border: 1px solid var(--slate-700);
            padding: 8px 20px;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .category-pill:hover {
            background: var(--slate-700);
            color: white;
            transform: translateY(-1px);
        }

        .category-pill.active {
            background: var(--emerald-600);
            color: white;
            border-color: var(--emerald-500);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* --- TWINX MOTION CARD CSS (Manual Implementation) --- */
        .twinx-card {
            background-color: var(--slate-800);
            border: 1px solid var(--slate-700);
            border-radius: 16px;
            /* rounded-2xl */
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .twinx-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color: var(--emerald-500);
        }

        .twinx-img-container {
            width: 100%;
            aspect-ratio: 4/3;
            /* Enforce uniform height */
            background-color: var(--slate-900);
            position: relative;
            overflow: hidden;
        }

        .twinx-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .twinx-card:hover .twinx-img {
            transform: scale(1.1);
        }

        .twinx-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.9), transparent);
            pointer-events: none;
        }

        .twinx-pill {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .twinx-body {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .twinx-add-btn {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%) translateY(20px) scale(0.8);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--emerald-600);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--slate-900);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 20;
            cursor: pointer;
        }

        .twinx-card:hover .twinx-add-btn {
            opacity: 1;
            transform: translateX(-50%) translateY(0) scale(1);
        }

        .twinx-card:hover .twinx-overlay {
            height: 100%;
            background: rgba(15, 23, 42, 0.4);
        }

        .twinx-add-btn:hover {
            transform: translateX(-50%) scale(1.15) !important;
            background: white;
            color: var(--emerald-600);
        }



        .btn-slate-action {
            background: var(--slate-900);
            border: 1px solid var(--slate-800);
            color: #cbd5e1;
            transition: 0.2s;
            border-radius: 12px;
        }

        .btn-slate-action:hover {
            background: var(--slate-800);
            color: white;
            border-color: var(--slate-600);
            transform: translateX(-3px);
        }

        .modal-content.slate-modal {
            background-color: var(--slate-900);
            border: 1px solid var(--slate-700);
            color: white;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .btn-close-white {
            filter: invert(1);
        }

        .shadow-emerald-glow {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        [x-cloak] {
            display: none !important;
        }

        .fs-xs {
            font-size: 0.75rem;
        }

        .fs-sm {
            font-size: 0.875rem;
        }

        kbd {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            border-radius: 0.25rem;
        }
    </style>

    <script>
        function posStore() {
            return {
                cart: [],
                products: [],
                categories: @json($categories),
                warehouses: @json($warehouses),
                warehouseId: {{ $activeShift->warehouse_id ?? auth()->user()->warehouse_id ?? 1 }}, // Stock Truth

                searchQuery: '',
                productSearch: '',
                isLoading: false,
                isProcessing: false,

                scanMode: false,
                refundMode: false,
                activeCategory: null,

                // Payment State
                paymentModal: null,
                addCustomerModal: null,
                shiftModal: null,
                activePaymentTab: 'cash',
                paymentAmount: 0,

                // New Customer State
                newCustomer: { name: '', mobile: '' },

                currentTime: new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }),

                // Computed
                get cartSubtotal() { return this.cart.reduce((a, b) => a + Number(b.subtotal), 0); },
                get cartTax() { return this.cartSubtotal * 0.14; },
                get cartTotal() { return this.cartSubtotal + this.cartTax; },

                get remainingAmount() { return Math.max(0, this.cartTotal - this.paymentAmount); },
                get changeAmount() { return Math.max(0, this.paymentAmount - this.cartTotal); },

                initPOS() {
                    this.fetchProducts();
                    this.paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    this.addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
                    this.shiftModal = new bootstrap.Modal(document.getElementById('shiftModal'));

                    setInterval(() => {
                        this.currentTime = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
                    }, 1000);
                },

                setWarehouse(id, name) {
                    this.warehouseId = id;
                    this.fetchProducts();
                },

                getWarehouseName() {
                    const w = this.warehouses.find(x => x.id === this.warehouseId);
                    return w ? w.name : 'Unknown';
                },

                async fetchProducts() {
                    this.isLoading = true;
                    try {
                        // Start Debug
                        console.log('Starting product fetch...');
                        const url = "{{ route('pos.products.search') }}";
                        console.log('Target URL:', url);

                        const res = await axios.get(url, {
                            params: {
                                q: this.productSearch,
                                category_id: this.activeCategory,
                                warehouse_id: this.warehouseId
                            }
                        });

                        console.log('Loaded:', res.data.length);
                        this.products = res.data;
                    } catch (e) {
                        console.error('Fetch error details:', e);
                        let msg = 'خطأ غير معروف';
                        if (e.response && e.response.data && e.response.data.message) {
                            msg = e.response.data.message;
                        } else if (e.message) {
                            msg = e.message;
                        }
                        alert('فشل تحميل المنتجات: ' + msg);
                    } finally {
                        this.isLoading = false;
                    }
                },

                addToCart(product) {
                    const existing = this.cart.find(i => i.id === product.id);
                    if (existing) {
                        existing.qty++;
                        existing.subtotal = existing.qty * existing.price;
                    } else {
                        this.cart.push({ ...product, qty: 1, subtotal: Number(product.price), price: Number(product.price) });
                    }
                    this.playBeep();
                    this.scrollToBottom();
                },

                updateQty(index, delta) {
                    const item = this.cart[index];
                    if (item.qty + delta <= 0) {
                        if (confirm('حذف الصنف من السلة؟')) this.cart.splice(index, 1);
                        return;
                    }
                    item.qty += delta;
                    item.subtotal = item.qty * item.price;
                },

                removeItem(index) { this.cart.splice(index, 1); },

                holdSale() {
                    if (this.cart.length === 0) return;
                    alert('Sale Held (Simulation)');
                    this.cart = [];
                },

                clearCart() { if (confirm('إلغاء الفاتورة؟')) this.cart = []; },

                // --- PAYMENT LOGIC ---
                showPaymentModal() {
                    this.paymentAmount = this.cartTotal; // Default to exact amount
                    this.paymentModal.show();
                    setTimeout(() => document.getElementById('payment-input').focus(), 500);
                },

                closePaymentModal() { this.paymentModal.hide(); },

                async processCheckout() {
                    if (this.isProcessing) return;
                    this.isProcessing = true;

                    const payload = {
                        items: this.cart,
                        total: this.cartTotal,
                        payment: {
                            method: this.activePaymentTab,
                            amount: this.paymentAmount,
                            change: this.changeAmount
                        },
                        customer_id: null // TODO: Add Customer Selection
                    };

                    try {
                        // Simulate API Call
                        // const res = await axios.post('/pos/checkout', payload);
                        await new Promise(r => setTimeout(r, 1000)); // Mock delay

                        this.playSuccess();
                        this.closePaymentModal();
                        this.cart = [];
                        alert('تمت العملية بنجاح! (Simulation)');

                    } catch (e) {
                        alert('فشل العملية: ' + e.message);
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // --- CUSTOMER LOGIC ---
                openAddCustomerModal() { this.addCustomerModal.show(); },

                async quickCreateCustomer() {
                    try {
                        const res = await axios.post('/pos/customers/quick-create', this.newCustomer);
                        if (res.data.success) {
                            alert('Customer Added');
                            this.addCustomerModal.hide();
                            this.newCustomer = { name: '', mobile: '' };
                        }
                    } catch (e) { alert('Error'); }
                },

                // --- SHIFT LOGIC ---
                showShiftModal() { this.shiftModal.show(); },
                closeShift() {
                    if (confirm('تأكيد إغلاق الوردية؟')) {
                        alert('Closing Shift... (Redirect to Z-Report)');
                        // window.location.href = '/pos/shifts/close/' + activeShiftId;
                    }
                },

                // --- UTILS ---
                formatMoney(amount) { return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(amount); },
                formatNumber(amount) { return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount); },
                scrollToBottom() { setTimeout(() => { const c = document.getElementById('cart-items-container'); c.scrollTop = c.scrollHeight; }, 50); },
                playBeep() { const a = new Audio('/assets/sounds/beep.mp3'); a.play().catch(e => { }); },
                playSuccess() { const a = new Audio('/assets/sounds/success.mp3'); a.play().catch(e => { }); },

                filterCategory(id) {
                    this.activeCategory = id;
                    this.fetchProducts();
                    document.querySelectorAll('.category-pill').forEach(el => el.classList.remove('active'));
                    event.target.classList.add('active');
                },

                // Keys
                handleGlobalKeys(e) {
                    if (e.key === 'F2') { e.preventDefault(); document.getElementById('product-search-input').focus(); }
                    if (e.key === 'F10') { e.preventDefault(); this.showPaymentModal(); }
                }
            }
        }
    </script>
@endsection