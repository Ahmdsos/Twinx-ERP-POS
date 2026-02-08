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

                    <button
                        class="btn btn-slate-800 border border-slate-700 text-white h-100 d-flex align-items-center gap-2"
                        @click="openRecentTransactions()" title="آخر العمليات / مرتجع">
                        <i class="bi bi-clock-history"></i>
                    </button>

                </div>

                <!-- CUSTOMER SEARCH -->
                <div class="position-relative">
                    <div class="input-group input-group-slate shadow-sm border border-slate-700">
                        <span class="input-group-text bg-transparent border-0 text-slate-400 ps-3"><i
                                class="bi bi-person-circle fs-5"></i></span>
                        <input type="text" id="product-search"
                            class="form-control bg-transparent border-0 text-white shadow-none py-2"
                            placeholder="اختر عميل أو ابحث بالاسم/الكود/الموبايل..." x-model="searchQuery"
                            @focus="loadRecentCustomers(); showCustomerDropdown = true"
                            @input.debounce.300ms="searchCustomer()"
                            @blur="setTimeout(() => showCustomerDropdown = false, 200)">
                        <button class="btn btn-link text-emerald-400 decoration-none fw-bold small pe-3"
                            @click="openAddCustomerModal()">
                            <i class="bi bi-plus-lg"></i> جديد
                        </button>
                    </div>

                    <!-- Search Results Dropdown (OUTSIDE input-group) -->
                    <div x-show="showCustomerDropdown && customerResults.length > 0" x-transition
                        class="position-absolute w-100 bg-slate-800 border border-slate-700 rounded shadow-lg p-1"
                        style="top: 100%; left: 0; z-index: 9999; max-height: 300px; overflow-y: auto;">
                        <template x-for="c in customerResults" :key="c.id">
                            <div class="p-2 rounded d-flex justify-content-between align-items-center cursor-pointer"
                                style="cursor: pointer;" @mousedown.prevent="selectCustomer(c)"
                                @mouseover="$el.style.backgroundColor = '#334155'"
                                @mouseout="$el.style.backgroundColor = 'transparent'">
                                <div>
                                    <div class="text-white fw-bold" x-text="c.name"></div>
                                    <div class="text-slate-400" style="font-size: 11px;"
                                        x-text="c.mobile || c.phone || '---'"></div>
                                </div>
                                <div class="text-emerald-400 font-monospace" style="font-size: 10px;" x-text="c.code"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Selected Customer Badge -->
                    <div x-show="selectedCustomer" x-cloak
                        class="mt-2 p-2 rounded d-flex justify-content-between align-items-center"
                        style="background: rgba(5, 150, 105, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-check-fill text-success me-2 fs-5"></i>
                            <div>
                                <div class="text-white fw-bold" style="font-size: 13px;" x-text="selectedCustomer?.name">
                                </div>
                                <div class="text-secondary" style="font-size: 11px;"
                                    x-text="'كود: ' + (selectedCustomer?.code || '---') + ' | رصيد: ' + formatNumber(selectedCustomer?.balance || 0)">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-link text-danger p-0" @click="clearSelectedCustomer()">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <div x-show="!selectedCustomer" class="mt-2 px-2 text-secondary"
                        style="font-size: 11px; font-style: italic;">
                        <i class="bi bi-info-circle me-1"></i> يتم التسبيع لعميل نقدي (Walk-in)
                    </div>
                </div>
            </header>

            <!-- DELIVERY INFO PANEL (Phase 3) -->
            <div x-show="isDeliveryMode" x-transition.opacity.duration.300ms
                class="p-3 bg-slate-900/80 border-bottom border-slate-800 backdrop-blur-sm">

                <div class="d-flex align-items-center mb-2 text-blue-400 fw-bold fs-xs text-uppercase tracking-wider">
                    <i class="bi bi-geo-alt-fill me-2"></i> بيانات التوصيل
                </div>

                <!-- Driver -->
                <div class="mb-2">
                    <select x-model="selectedDriver"
                        class="form-select form-select-sm bg-slate-800 border-slate-700 text-white fs-xs">
                        <option value="">-- اختر السائق --</option>
                        <template x-for="d in drivers" :key="d.id">
                            <option :value="d.id" x-text="d.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Address & Fee -->
                <div class="row g-2">
                    <div class="col-8">
                        <input type="text" x-model="recipientName"
                            class="form-control form-control-sm bg-slate-800 border-slate-700 text-white placeholder-slate-500 fs-xs mb-2"
                            placeholder="اسم المستلم...">
                        <input type="text" x-model="recipientPhone"
                            class="form-control form-control-sm bg-slate-800 border-slate-700 text-white placeholder-slate-500 fs-xs mb-2"
                            placeholder="رقم الموبايل...">
                        <textarea x-model="shippingAddress" rows="1"
                            class="form-control form-control-sm bg-slate-800 border-slate-700 text-white placeholder-slate-500 fs-xs"
                            placeholder="العنوان بالتفصيل..."></textarea>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <input type="number" x-model.number="deliveryFee"
                                class="form-control bg-slate-800 border-slate-700 text-white text-center fs-xs"
                                placeholder="رسوم">
                            <span class="input-group-text bg-slate-800 border-slate-700 text-slate-500 fs-xs">EGP</span>
                        </div>
                    </div>
                </div>
            </div>

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

                            <button
                                class="btn btn-sm text-slate-400 hover:text-white fs-xs font-mono px-2 py-1 rounded bg-slate-950/50 border border-slate-800/50 hover:border-emerald-500/50 transition-all d-flex align-items-center gap-2"
                                @click.stop="openPriceOverride(index)" title="تعديل السعر">
                                <span x-text="formatMoney(item.price)"></span>
                                <i class="bi bi-pencil-square text-slate-500"></i>
                            </button>

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
                        <span>الضريبة ({{ $taxRatePercent }}%):</span>
                        <span class="text-white fw-bold font-mono" x-text="formatMoney(cartTax)"></span>
                    </div>
                    <!-- Phase 3: Cart Discount Input -->
                    <div class="col-12 d-flex justify-content-between align-items-center mt-1" x-show="cart.length > 0">
                        <span class="text-rose-400"><i class="bi bi-percent me-1"></i>خصم:</span>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number"
                                class="form-control form-control-sm bg-slate-800 border-slate-700 text-white text-center"
                                style="width: 80px;" min="0" step="1" x-model.number="cartDiscount" placeholder="0.00">
                            <span class="text-rose-400 font-mono small">EGP</span>
                        </div>
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
                    <button
                        class="btn btn-slate-800 py-3 text-white fw-bold hover:bg-slate-700 border border-slate-700 position-relative"
                        @click="showHeldSales()" x-show="heldCount > 0">
                        <i class="bi bi-collection-play me-2"></i>معلقة
                        <span
                            class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-amber-500 text-dark"
                            x-text="heldCount"></span>
                    </button>
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
                                <div class="twinx-pill"
                                    :class="{
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
        <aside
            class="d-none d-xxl-flex flex-column bg-slate-950 border-start border-slate-800 p-3 gap-3 overflow-y-auto custom-scrollbar"
            style="width: 280px; min-width: 280px; max-height: 100vh;">
            <!-- CLOCK & INFO -->
            <div class="bg-slate-900 rounded p-3 border border-slate-800 text-center shadow-sm">
                <div class="text-white fs-4 fw-black font-mono tracking-widest" x-text="currentTime">00:00:00</div>
                <div class="text-slate-400 fs-xs text-uppercase mt-1">{{ now()->format('l, d F Y') }}</div>
            </div>

            <h6 class="text-slate-500 fw-bold fs-xs text-uppercase tracking-wider mt-2 mb-1 px-1">العمليات</h6>

            <div class="d-grid gap-2">
                <button class="btn btn-slate-action py-3 text-start px-3 transition-all relative overflow-hidden"
                    @click="isDeliveryMode = !isDeliveryMode; if(isDeliveryMode) refundMode = false;"
                    :class="{'bg-blue-900/20 border-blue-500 text-blue-400': isDeliveryMode}">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-slate-800 d-flex align-items-center justify-content-center me-3 border border-slate-700">
                            <i class="bi bi-truck"></i>
                        </div>
                        <span class="fw-bold">توصيل منزلي</span>
                    </div>
                </button>

                <button class="btn btn-slate-action py-3 text-start px-3" @click="showDeliveryModal()">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-slate-800 d-flex align-items-center justify-content-center me-3 border border-slate-700">
                            <i class="bi bi-truck-flatbed"></i>
                        </div>
                        <span class="fw-bold">إدارة التوصيل</span>
                    </div>
                </button>

                <button class="btn btn-slate-action py-3 text-start px-3 relative overflow-hidden"
                    @click="openRecentTransactions()" :class="{'bg-rose-900/20 border-rose-500 text-rose-400': refundMode}">
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

            <!-- Phase 3: Additional Actions -->
            <h6 class="text-slate-500 fw-bold fs-xs text-uppercase tracking-wider mt-3 mb-1 px-1">التقارير</h6>
            <div class="d-grid gap-2">
                <button class="btn btn-slate-action py-2 text-start px-3" @click="showXReport()">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-blue-900/30 d-flex align-items-center justify-content-center me-3 border border-blue-700/50">
                            <i class="bi bi-file-earmark-bar-graph text-blue-400"></i>
                        </div>
                        <span class="fw-bold text-sm">X-Report (ملخص)</span>
                    </div>
                </button>

                <button class="btn btn-slate-action py-2 text-start px-3" @click="showLastTransactions()">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-purple-900/30 d-flex align-items-center justify-content-center me-3 border border-purple-700/50">
                            <i class="bi bi-receipt text-purple-400"></i>
                        </div>
                        <span class="fw-bold text-sm">آخر المعاملات</span>
                    </div>
                </button>

                <button class="btn btn-slate-action py-2 text-start px-3" @click="openCashDrawer()">
                    <div class="d-flex align-items-center">
                        <div
                            class="w-8 h-8 rounded bg-amber-900/30 d-flex align-items-center justify-content-center me-3 border border-amber-700/50">
                            <i class="bi bi-box-arrow-up text-amber-400"></i>
                        </div>
                        <span class="fw-bold text-sm">فتح الدرج</span>
                    </div>
                </button>
            </div>

            <h6
                class="text-white fw-bold fs-xs text-uppercase tracking-wider mt-4 mb-3 px-1 border-top border-slate-800 pt-3">
                <i class="bi bi-keyboard me-2 text-emerald-400"></i> اختصارات الكيبورد
            </h6>

            <div class="d-grid gap-2 mb-3">
                <!-- F2: Search -->
                <button
                    class="d-flex justify-content-between align-items-center px-3 py-3 bg-slate-800 rounded border border-slate-600 hover:border-emerald-400 hover:bg-slate-700 transition-all shadow-sm group"
                    @click="document.getElementById('product-search').focus()">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-search text-slate-400 group-hover:text-emerald-300 fs-5 transition-colors"></i>
                        <span class="text-slate-200 fs-sm fw-bold group-hover:text-white transition-colors">بحث منتج</span>
                    </div>
                    <kbd
                        class="bg-slate-900 text-emerald-400 border border-emerald-500/50 rounded px-2 py-1 fs-xs font-mono fw-bold shadow-[0_0_8px_rgba(16,185,129,0.3)]">F2</kbd>
                </button>

                <!-- F10: Pay -->
                <button
                    class="d-flex justify-content-between align-items-center px-3 py-3 bg-slate-800 rounded border border-slate-600 hover:border-emerald-400 hover:bg-slate-700 transition-all shadow-sm group"
                    @click="activePaymentTab = 'cash'; openCheckoutModal()">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-credit-card text-slate-400 group-hover:text-emerald-300 fs-5 transition-colors"></i>
                        <span class="text-slate-200 fs-sm fw-bold group-hover:text-white transition-colors">دفع سريع</span>
                    </div>
                    <kbd
                        class="bg-slate-900 text-emerald-400 border border-emerald-500/50 rounded px-2 py-1 fs-xs font-mono fw-bold shadow-[0_0_8px_rgba(16,185,129,0.3)]">F10</kbd>
                </button>

                <!-- F8: Print Last -->
                <button
                    class="d-flex justify-content-between align-items-center px-3 py-3 bg-slate-800 rounded border border-slate-600 hover:border-emerald-400 hover:bg-slate-700 transition-all shadow-sm group"
                    @click="printLastInvoice()">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-printer text-slate-400 group-hover:text-emerald-300 fs-5 transition-colors"></i>
                        <span class="text-slate-200 fs-sm fw-bold group-hover:text-white transition-colors">طباعة
                            فاتورة</span>
                    </div>
                    <kbd
                        class="bg-slate-900 text-emerald-400 border border-emerald-500/50 rounded px-2 py-1 fs-xs font-mono fw-bold shadow-[0_0_8px_rgba(16,185,129,0.3)]">F8</kbd>
                </button>
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
                        <!-- SHIFT STATUS / INFO -->
                        <div x-show="activeShiftId" class="fade-in">
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
                                    <span class="text-white font-mono fw-bold" x-text="'#' + activeShiftId"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-slate-400">وقت البدء:</span>
                                    <span class="text-white font-mono" x-text="shiftStartTime"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-slate-400">المبيعات (كاش):</span>
                                    <span class="text-emerald-400 font-mono fw-bold"
                                        x-text="formatMoney(activeShiftStats.total_cash || 0)"></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-slate-400 small fw-bold">النقدية الفعلية (Closing
                                    Cash)</label>
                                <div class="input-group input-group-slate border border-slate-700">
                                    <span class="input-group-text bg-transparent border-0 text-emerald-500">EGP</span>
                                    <input type="number" x-model.number="closingCash"
                                        class="form-control bg-transparent border-0 text-white text-center font-mono fw-bold fs-5"
                                        placeholder="0.00">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-slate-400 small fw-bold">رمز الإغلاق (PIN)</label>
                                <input type="password" x-model="shiftPin"
                                    class="form-control bg-slate-800 border-slate-700 text-white text-center font-mono fw-bold fs-5"
                                    placeholder="****">
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-rose-500 hover:bg-rose-900/20 py-2" @click="closeShift()"
                                    :disabled="isProcessing">
                                    <i class="bi bi-door-closed me-2"></i> إغلاق الوردية (Z-Report)
                                </button>
                            </div>
                        </div>

                        <!-- OPEN SHIFT UI -->
                        <div x-show="!activeShiftId" class="fade-in">
                            <div class="text-center mb-4">
                                <div
                                    class="d-inline-flex align-items-center justify-content-center w-16 h-16 rounded-circle bg-amber-500/10 text-amber-400 mb-3 border border-amber-500/20">
                                    <i class="bi bi-door-open fs-3"></i>
                                </div>
                                <h5 class="text-white fw-bold">لا توجد وردية مفتوحة</h5>
                                <div class="text-slate-500 fs-sm">يرجى بدء وردية جديدة لمباشرة العمل</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-slate-400 small fw-bold">نقدية البداية (Opening Cash)</label>
                                <div class="input-group input-group-slate border border-slate-700">
                                    <span class="input-group-text bg-transparent border-0 text-amber-500">EGP</span>
                                    <input type="number" x-model.number="openingCash"
                                        class="form-control bg-transparent border-0 text-white text-center font-mono fw-bold fs-5"
                                        placeholder="0.00">
                                </div>
                            </div>

                            <button class="btn btn-amber-600 w-100 py-3 fw-bold" @click="openShift()"
                                :disabled="isProcessing">
                                <span x-show="!isProcessing"><i class="bi bi-play-fill me-2"></i> بدء الوردية</span>
                                <span x-show="isProcessing" class="spinner-border spinner-border-sm"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="expenseModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content slate-modal shadow-lg border-amber-900/50">
                    <div class="modal-header border-slate-800 bg-amber-950/10">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-wallet2 me-2 text-amber-500"></i> تسجيل
                            مصروف</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-900">
                        <div class="mb-3">
                            <label class="form-label text-slate-400 small fw-bold">المبلغ</label>
                            <input type="number" x-model.number="expenseAmount"
                                class="form-control form-control-lg bg-slate-800 border-slate-700 text-white text-center font-mono fw-bold fs-4"
                                placeholder="0.00">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-slate-400 small fw-bold">ملاحظات / سبب الصرف</label>
                            <textarea x-model="expenseNotes" class="form-control bg-slate-800 border-slate-700 text-white"
                                rows="3" placeholder="مثلاً: شراء أدوات نظافة"></textarea>
                        </div>
                        <button class="btn btn-amber-600 w-100 py-3 fw-bold" @click="saveExpense()"
                            :disabled="!expenseAmount || !expenseNotes">
                            <span x-show="!isProcessing"><i class="bi bi-save me-2"></i> حفظ المصروف</span>
                            <span x-show="isProcessing" class="spinner-border spinner-border-sm"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT TRANSACTIONS MODAL (Phase 3.5) -->
        <div class="modal fade" id="recentTransactionsModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content slate-modal shadow-lg border-slate-700">
                    <div class="modal-header border-slate-800 bg-slate-900">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-clock-history me-2 text-info"></i> آخر
                            العمليات</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0 bg-slate-900">
                        <div class="table-responsive" style="max-height: 50vh;">
                            <table class="table table-dark table-hover mb-0">
                                <thead class="bg-slate-950">
                                    <tr>
                                        <th>#</th>
                                        <th>التاريخ</th>
                                        <th>العميل</th>
                                        <th>المبلغ</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="inv in recentTransactions" :key="inv.id">
                                        <tr>
                                            <td class="font-mono text-xs" x-text="inv.invoice_number"></td>
                                            <td class="text-xs" x-text="inv.created_at"></td>
                                            <td class="text-xs" x-text="inv.customer_name"></td>
                                            <td class="font-mono text-emerald-400 fw-bold" x-text="formatMoney(inv.total)">
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" @click="openReturnModal(inv)">
                                                    <i class="bi bi-arrow-return-left"></i> استرجاع
                                                </button>
                                                <button class="btn btn-sm btn-outline-info"
                                                    @click="window.open('/pos/receipt/' + inv.id, '_blank')">
                                                    <i class="bi bi-printer"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RETURN MODAL (Phase 3.5) -->
        <div class="modal fade" id="returnModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content slate-modal shadow-lg border-danger">
                    <div class="modal-header border-slate-800 bg-rose-950/20">
                        <h5 class="modal-title fw-bold text-white"><i
                                class="bi bi-arrow-return-left me-2 text-rose-500"></i> مرتجع مبيعات</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-900">
                        <div class="alert alert-info bg-slate-800 border-slate-700 text-slate-300 fs-sm">
                            <i class="bi bi-info-circle me-2"></i> حدد الكميات المراد إرجاعها من الفاتورة رقم <span
                                class="fw-bold font-mono text-white" x-text="returnInvoice?.invoice_number"></span>
                        </div>

                        <div class="table-responsive mb-3 border border-slate-700 rounded" style="max-height: 45vh;">
                            <table class="table table-dark table-sm mb-0">
                                <thead>
                                    <tr class="bg-slate-800/80">
                                        <th class="p-2">الصنف</th>
                                        <th class="p-2 text-center">الكمية</th>
                                        <th class="p-2 text-center text-emerald-400">المتاح</th>
                                        <th class="p-2 text-center" style="width: 100px;">الاسترجاع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in returnItems" :key="item.line_id">
                                        <tr class="border-bottom border-slate-800/50">
                                            <td class="p-2">
                                                <div class="fw-bold text-white small" x-text="item.product_name"></div>
                                                <div class="text-slate-500 fs-xs" x-text="formatMoney(item.price)"></div>
                                            </td>
                                            <td class="p-2 text-center text-slate-400 font-mono" x-text="item.original_qty">
                                            </td>
                                            <td class="p-2 text-center text-emerald-400 font-mono fw-bold"
                                                x-text="item.max_qty"></td>
                                            <td class="p-2">
                                                <input type="number" x-model.number="item.return_qty"
                                                    class="form-control form-control-sm bg-slate-800 border-slate-600 text-white text-center font-mono"
                                                    min="0" :max="item.max_qty">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-slate-400 small fw-bold">رمز الإغلاق (PIN)</label>
                            <input type="password" x-model="returnPin"
                                class="form-control bg-slate-800 border-slate-700 text-white text-center font-mono placeholder-slate-600"
                                placeholder="****">
                        </div>

                        <button class="btn btn-rose-600 w-100 py-3 fw-bold" @click="submitReturn()"
                            :disabled="isProcessing">
                            <span x-show="!isProcessing"><i class="bi bi-check-circle me-2"></i> تأكيد المرتجع</span>
                            <span x-show="isProcessing" class="spinner-border spinner-border-sm"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 0.5 PRICE OVERRIDE MODAL (Phase 3) -->
        <div class="modal fade" id="priceOverrideModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content slate-modal shadow-lg border-rose-900/50">
                    <div class="modal-header border-slate-800 bg-rose-950/10">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-tag-fill me-2 text-rose-500"></i> تعديل
                            السعر</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-900">
                        <!-- Product Info -->
                        <div class="bg-slate-950 rounded p-3 border border-slate-800 mb-4 d-flex gap-3 align-items-center">
                            <div
                                class="w-12 h-12 rounded bg-slate-800 d-flex align-items-center justify-content-center border border-slate-700">
                                <i class="bi bi-box-seam text-slate-400 fs-4"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold mb-1" x-text="overrideItem?.name"></div>
                                <div class="text-slate-500 fs-xs font-mono">
                                    السعر الأصلي: <span class="text-emerald-400"
                                        x-text="formatMoney(overrideItem?.original_price)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Price Input -->
                        <div class="mb-4">
                            <label class="form-label text-slate-400 small fw-bold">السعر الجديد (EGP)</label>
                            <input type="number" x-model.number="overrideNewPrice" step="0.5"
                                class="form-control form-control-lg bg-slate-800 border-slate-700 text-white text-center font-mono fw-bold fs-4 focus:ring-rose-500 focus:border-rose-500">
                        </div>

                        <!-- Security Section (Shows only if price is lower) -->
                        <div x-show="isPriceLower" x-transition.opacity>
                            <div
                                class="alert alert-danger bg-rose-900/20 border-rose-900/50 text-rose-200 fs-sm d-flex align-items-center mb-3">
                                <i class="bi bi-shield-lock-fill me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">تصريح المدير مطلوب</div>
                                    <div class="text-rose-300/70 fs-xs">تخفيض السعر يتطلب موافقة إدارية</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-slate-400 small fw-bold">رمز المدير (Manager PIN)</label>
                                <input type="password" x-model="overridePin"
                                    class="form-control bg-slate-800 border-slate-700 text-white text-center font-mono letter-spacing-4 placeholder-slate-600"
                                    placeholder="••••">
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-slate-400 small fw-bold">سبب التعديل</label>
                                <select x-model="overrideReason"
                                    class="form-select bg-slate-800 border-slate-700 text-white">
                                    <option value="">اختر السبب...</option>
                                    <option value="damage">عطب / تلف بسيط</option>
                                    <option value="expiry">قرب انتهاء الصلاحية</option>
                                    <option value="bulk">خصم كميات</option>
                                    <option value="manager">قرار إداري مباشر</option>
                                    <option value="other">أخرى</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-rose-500 w-100 py-3 fw-bold shadow-lg hover:bg-rose-600 transition-colors"
                            @click="submitPriceOverride()" :disabled="isPriceLower && !overridePin">
                            <span x-show="!isProcessingOverride">حفظ التغييرات</span>
                            <span x-show="isProcessingOverride" class="spinner-border spinner-border-sm"></span>
                        </button>
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
                    <div class="modal-body p-0 d-flex bg-slate-900" style="height: 650px;">
                        <!-- Payment Methods (Left) -->
                        <div class="w-25 border-end border-slate-800 bg-slate-950 p-2 d-flex flex-column gap-2">
                            <!-- Cashier Selection (Manager Override) -->
                            <div class="mb-2" x-show="cashiers.length > 0">
                                <label class="text-slate-500 x-small mb-1 d-block">الكاشير</label>
                                <select class="form-select form-select-sm bg-slate-900 border-slate-700 text-white fs-xs"
                                    x-model="selectedCashierId">
                                    <template x-for="cashier in cashiers" :key="cashier.id">
                                        <option :value="cashier.id" x-text="cashier.name"
                                            :selected="cashier.id == currentUserId"></option>
                                    </template>
                                </select>
                            </div>
                            <button class="btn btn-slate-action py-3 text-start transition-all"
                                @click="activePaymentTab = 'cash'; paymentAmount = remainingAmount;"
                                :class="{'bg-emerald-900/20 border-emerald-500 text-white': activePaymentTab === 'cash'}">
                                <i class="bi bi-cash-stack me-2 text-emerald-500"></i> كاش
                            </button>
                            <button class="btn btn-slate-action py-3 text-start transition-all"
                                @click="activePaymentTab = 'card'; paymentAmount = remainingAmount;"
                                :class="{'bg-blue-900/20 border-blue-500 text-white': activePaymentTab === 'card'}">
                                <i class="bi bi-credit-card me-2 text-blue-500"></i> بطاقة بنكية
                            </button>
                            <button class="btn btn-slate-action py-3 text-start transition-all"
                                @click="activePaymentTab = 'credit'; paymentAmount = remainingAmount;"
                                :class="{'bg-amber-900/20 border-amber-500 text-white': activePaymentTab === 'credit'}">
                                <i class="bi bi-person-badge me-2 text-amber-500"></i> أجل (عميل)
                            </button>
                        </div>

                        <!-- Payment Input (Right) -->
                        <div class="w-75 p-4 d-flex flex-column relative">

                            <!-- INPUT AREA -->
                            <div class="flex-grow-0 mb-3">
                                <!-- CASH VIEW -->
                                <div x-show="activePaymentTab === 'cash'" class="fade-in">
                                    <label class="form-label text-slate-400 small">المبلغ (نقد)</label>
                                    <div
                                        class="input-group input-group-lg input-group-slate mb-3 border border-emerald-500/30">
                                        <span
                                            class="input-group-text bg-transparent border-0 text-emerald-500 fs-4">EGP</span>
                                        <input type="number"
                                            class="form-control bg-transparent border-0 text-white fs-3 font-mono fw-bold"
                                            x-model="paymentAmount" id="payment-input">
                                    </div>
                                    <!-- FAST CASH -->
                                    <div class="d-grid grid-cols-4 gap-2 mb-3">
                                        <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                            @click="paymentAmount = 50">50</button>
                                        <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                            @click="paymentAmount = 100">100</button>
                                        <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                            @click="paymentAmount = 200">200</button>
                                        <button class="btn btn-slate-800 py-2 font-mono fw-bold"
                                            @click="paymentAmount = remainingAmount">Baki</button>
                                    </div>
                                </div>

                                <!-- CARD VIEW -->
                                <div x-show="activePaymentTab === 'card'" class="fade-in">
                                    <label class="form-label text-slate-400 small">المبلغ (بطاقة)</label>
                                    <div
                                        class="input-group input-group-lg input-group-slate mb-3 border border-blue-500/30">
                                        <span class="input-group-text bg-transparent border-0 text-blue-500 fs-4">EGP</span>
                                        <input type="number"
                                            class="form-control bg-transparent border-0 text-white fs-3 font-mono fw-bold"
                                            x-model="paymentAmount">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-slate-500 fs-xs">رقم العملية / Auth Code
                                            (اختياري)</label>
                                        <input type="text" x-model="cardTxnId"
                                            class="form-control bg-slate-800 border-slate-700 text-white font-mono"
                                            placeholder="xxxx">
                                    </div>
                                </div>

                                <!-- CREDIT VIEW -->
                                <div x-show="activePaymentTab === 'credit'" class="fade-in">
                                    <label class="form-label text-slate-400 small">المبلغ (آجل)</label>
                                    <div
                                        class="input-group input-group-lg input-group-slate mb-3 border border-amber-500/30">
                                        <span
                                            class="input-group-text bg-transparent border-0 text-amber-500 fs-4">EGP</span>
                                        <input type="number"
                                            class="form-control bg-transparent border-0 text-white fs-3 font-mono fw-bold"
                                            x-model="paymentAmount">
                                    </div>
                                    <div class="bg-amber-900/10 border border-amber-900/30 p-2 rounded mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-amber-500 small">العميل:</span>
                                            <span class="fw-bold text-white"
                                                x-text="selectedCustomer ? selectedCustomer.name : 'لم يتم تحديد عميل'"></span>
                                        </div>
                                        <div class="text-end mt-1" x-show="!selectedCustomer">
                                            <button class="btn btn-sm btn-outline-amber-500"
                                                @click="addCustomerModal.show()">اختر عميل</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- ADD PAYMENT BTN -->
                                <button
                                    class="btn btn-slate-800 w-100 border-dashed border-slate-600 hover:bg-slate-700 text-slate-300"
                                    @click="addPayment()">
                                    <i class="bi bi-plus-circle me-2"></i> إضافة دفعة (Split Payment)
                                </button>
                            </div>

                            <!-- PAYMENT LIST -->
                            <div class="flex-grow-1 overflow-auto custom-scrollbar border-top border-slate-800 py-2 mb-2"
                                style="max-height: 150px;">
                                <template x-for="(p, i) in payments" :key="i">
                                    <div
                                        class="d-flex justify-content-between align-items-center p-2 bg-slate-950 rounded mb-1 border border-slate-800 animate__animated animate__fadeIn">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge"
                                                :class="{
                                                                                                                                                                                                                                                                                                            'bg-emerald-900 text-emerald-400': p.method === 'cash',
                                                                                                                                                                                                                                                                                                            'bg-blue-900 text-blue-400': p.method === 'card',
                                                                                                                                                                                                                                                                                                            'bg-amber-900 text-amber-400': p.method === 'credit'
                                                                                                                                                                                                                                                                                                        }"
                                                x-text="p.label"></span>
                                            <span class="font-mono fw-bold text-white"
                                                x-text="formatMoney(p.amount)"></span>
                                            <span class="text-slate-500 fs-xs font-mono" x-show="p.note"
                                                x-text="'Ref: ' + p.note"></span>
                                        </div>
                                        <button class="btn btn-link text-rose-500 p-0" @click="removePayment(i)"><i
                                                class="bi bi-x-lg"></i></button>
                                    </div>
                                </template>
                                <div x-show="payments.length === 0" class="text-center text-slate-600 py-2 fs-xs italic">
                                    -- دفع مباشر (Single Payment) --
                                </div>
                            </div>

                            <!-- TOTALS -->
                            <div class="p-3 rounded bg-slate-950 border border-slate-800 mt-auto">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-slate-400">Total Due:</span>
                                    <span class="text-white font-mono" x-text="formatMoney(cartTotal)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-slate-400">Total Paid:</span>
                                    <span class="text-emerald-400 font-mono fw-bold" x-text="formatMoney(totalPaid)"></span>
                                </div>
                                <div class="border-top border-slate-800 my-2"></div>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold" :class="remainingAmount > 0 ? 'text-rose-400' : 'text-slate-400'">
                                        <template x-if="remainingAmount > 0">Remaining:</template>
                                        <template x-if="remainingAmount <= 0">Complete</template>
                                    </span>
                                    <span class="fw-black font-mono"
                                        :class="remainingAmount > 0 ? 'text-rose-400' : 'text-slate-400'"
                                        x-text="formatMoney(remainingAmount)"></span>
                                </div>
                                <div class="d-flex justify-content-between mt-2" x-show="changeAmount > 0">
                                    <span class="fw-bold text-blue-400">Change:</span>
                                    <span class="fw-black text-blue-400 font-mono"
                                        x-text="formatMoney(changeAmount)"></span>
                                </div>
                            </div>

                            <!-- FOOTER ACTIONS -->
                            <div class="mt-3 d-flex gap-2">
                                <button class="btn btn-slate-700 flex-grow-1 py-3" @click="closePaymentModal()">إلغاء
                                    (Esc)</button>
                                <button class="btn btn-emerald-600 flex-grow-1 py-3 fw-bold shadow-lg"
                                    :disabled="!canCheckout" @click="processCheckout()">
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

        <!-- HELD SALES MODAL (Inside Alpine Scope) -->
        <div class="modal fade" id="heldSalesModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-slate-900 border border-slate-700 text-white">
                    <div class="modal-header border-slate-700">
                        <h5 class="modal-title"><i class="bi bi-pause-circle me-2"></i>الفواتير المعلقة</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <template x-if="heldSales.length === 0">
                            <div class="text-center py-5 text-slate-400">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">لا توجد فواتير معلقة</p>
                            </div>
                        </template>
                        <template x-for="sale in heldSales" :key="sale.id">
                            <div
                                class="d-flex justify-content-between align-items-center p-3 mb-2 bg-slate-800 rounded border border-slate-700">
                                <div>
                                    <div class="fw-bold" x-text="sale.hold_number"></div>
                                    <div class="text-slate-400 small">
                                        <span x-text="sale.items?.length || 0"></span> أصناف -
                                        <span x-text="formatMoney(sale.total)"></span>
                                    </div>
                                    <div class="text-slate-500 text-xs" x-text="sale.created_at"></div>
                                </div>
                                <button class="btn btn-emerald-600 btn-sm" @click="resumeSale(sale.id)">
                                    <i class="bi bi-play-fill me-1"></i>استئناف
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- DELIVERY MANAGEMENT MODAL -->
        <div class="modal fade" id="deliveryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-slate-900 border border-slate-700 text-white shadow-2xl">
                    <div class="modal-header border-slate-700">
                        <h5 class="modal-title"><i class="bi bi-truck-flatbed me-2"></i>إدارة التوصيل</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead>
                                    <tr class="text-slate-400 border-bottom border-slate-800">
                                        <th class="p-3">رقم الفاتورة</th>
                                        <th class="p-3">العميل</th>
                                        <th class="p-3">العنوان</th>
                                        <th class="p-3">السائق</th>
                                        <th class="p-3">الحالة</th>
                                        <th class="p-3">الإجمالي</th>
                                        <th class="p-3 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="deliveryOrders.length === 0">
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-slate-500">لا توجد طلبات توصيل نشطة
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="order in deliveryOrders" :key="order.id">
                                        <tr class="border-bottom border-slate-800/50">
                                            <td class="p-3 font-mono" x-text="order.invoice_number"></td>
                                            <td class="p-3" x-text="order.customer_name"></td>
                                            <td class="p-3 small text-slate-400" x-text="order.address"></td>
                                            <td class="p-3">
                                                <span class="badge bg-slate-800 border border-slate-600"
                                                    x-text="order.driver_name"></span>
                                            </td>
                                            <td class="p-3">
                                                <span class="badge" :class="getDeliveryStatusClass(order.status)"
                                                    x-text="order.status_label"></span>
                                            </td>
                                            <td class="p-3 font-mono text-emerald-400" x-text="formatMoney(order.total)">
                                            </td>
                                            <td class="p-3 text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary"
                                                        x-show="order.status === 'ready'"
                                                        @click="updateDeliveryStatus(order.id, 'shipped')"
                                                        title="Mark as Shipped">
                                                        <i class="bi bi-send"></i> خرج
                                                    </button>
                                                    <button class="btn btn-outline-emerald-600"
                                                        x-show="order.status === 'shipped'"
                                                        @click="updateDeliveryStatus(order.id, 'delivered')"
                                                        title="Mark as Delivered">
                                                        <i class="bi bi-check-lg"></i> تسليم
                                                    </button>
                                                    <button class="btn btn-outline-rose-600"
                                                        @click="updateDeliveryStatus(order.id, 'cancelled')" title="Cancel">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-slate-700 bg-slate-950">
                        <button class="btn btn-slate-700" @click="fetchDeliveryOrders()"><i
                                class="bi bi-arrow-clockwise me-2"></i> تحديث</button>
                        <button class="btn btn-slate-600" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- X-REPORT MODAL (Professional Shift Summary) -->
        <div class="modal fade" id="xReportModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content slate-modal shadow-2xl border-emerald-500/20">
                    <div class="modal-header border-slate-700 bg-slate-950 p-4">
                        <h5 class="modal-title fw-black text-white">
                            <i class="bi bi-file-earmark-bar-graph me-2 text-emerald-500"></i>
                            X-Report (ملخص الوردية)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-slate-900 custom-scrollbar" style="max-height: 80vh; overflow-y: auto;">
                        <template x-if="xReportData">
                            <div class="fade-in">
                                <!-- Shift Header -->
                                <div class="bg-slate-950 rounded-xl p-3 border border-slate-800 mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-slate-500 small">رقم الوردية:</span>
                                        <span class="text-white font-mono fw-bold"
                                            x-text="'#' + xReportData.shift_id"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-slate-500 small">وقت البدء:</span>
                                        <span class="text-white font-mono" x-text="xReportData.started_at"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-slate-500 small">المدة:</span>
                                        <span class="text-white font-mono" x-text="xReportData.duration"></span>
                                    </div>
                                </div>

                                <!-- Financial Grid -->
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div class="bg-slate-800/40 p-3 rounded-xl border border-slate-700">
                                            <div class="text-slate-500 fs-xs mb-1 text-uppercase fw-bold ls-1">المبيعات
                                                الإجمالية</div>
                                            <div class="text-white fs-4 fw-black font-mono"
                                                x-text="formatNumber(xReportData.total_sales)"></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-slate-800/40 p-3 rounded-xl border border-slate-700">
                                            <div class="text-rose-400 fs-xs mb-1 text-uppercase fw-bold ls-1">المرتجع</div>
                                            <div class="text-rose-400 fs-4 fw-black font-mono"
                                                x-text="formatNumber(xReportData.total_returns)"></div>
                                            <div class="text-slate-500 fs-xs mt-1"
                                                x-text="xReportData.returns_count + ' معاملة'"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Breakdown -->
                                <h6 class="text-slate-400 small fw-bold mb-3 text-uppercase ls-1">تفاصيل التحصيل</h6>
                                <div class="bg-slate-950 rounded-xl border border-slate-800 overflow-hidden mb-4">
                                    <div class="d-flex justify-content-between p-3 border-bottom border-slate-800/50">
                                        <div class="d-flex align-items-center">
                                            <div class="w-2 h-2 rounded-circle bg-emerald-500 me-2"></div>
                                            <span class="text-slate-300">نقداً (Cash)</span>
                                        </div>
                                        <span class="text-white font-mono fw-bold"
                                            x-text="formatNumber(xReportData.total_cash)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between p-3 border-bottom border-slate-800/50">
                                        <div class="d-flex align-items-center">
                                            <div class="w-2 h-2 rounded-circle bg-blue-500 me-2"></div>
                                            <span class="text-slate-300">بطاقة (Card)</span>
                                        </div>
                                        <span class="text-white font-mono fw-bold"
                                            x-text="formatNumber(xReportData.total_card)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="w-2 h-2 rounded-circle bg-amber-500 me-2"></div>
                                            <span class="text-slate-300">آجل (Credit)</span>
                                        </div>
                                        <span class="text-white font-mono fw-bold"
                                            x-text="formatNumber(xReportData.total_credit)"></span>
                                    </div>
                                </div>

                                <!-- Outflow -->
                                <h6 class="text-slate-400 small fw-bold mb-3 text-uppercase ls-1">المصروفات</h6>
                                <div class="bg-rose-900/10 rounded-xl p-3 border border-rose-900/20 mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-rose-300">إجمالي المصروفات:</span>
                                        <span class="text-rose-400 font-mono fw-bold fs-5"
                                            x-text="formatNumber(xReportData.total_expenses)"></span>
                                    </div>
                                </div>

                                <!-- Final Summary -->
                                <div
                                    class="bg-emerald-500/10 rounded-xl p-4 border border-emerald-500/20 shadow-emerald-glow">
                                    <div class="text-emerald-400 fs-xs mb-1 text-uppercase fw-bold ls-1 text-center">النقدية
                                        المتوقعة بالدرج</div>
                                    <div class="text-emerald-400 fs-2 fw-black font-mono text-center"
                                        x-text="formatNumber(xReportData.expected_cash) + ' EGP'"></div>
                                    <div class="text-slate-500 fs-xs text-center mt-2">(Opening + Cash - Expenses - Returns)
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!xReportData">
                            <div class="text-center py-5">
                                <div class="spinner-border text-emerald-500 mb-3"></div>
                                <div class="text-slate-400">جاري تحميل البيانات...</div>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer border-slate-700 bg-slate-950">
                        <button class="btn btn-emerald-600 px-4 fw-bold" @click="window.print()">
                            <i class="bi bi-printer me-2"></i> طباعة (Ctrl+P)
                        </button>
                        <button class="btn btn-slate-700 px-4" data-bs-dismiss="modal">إغلاق</button>
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
                drivers: @json($drivers),

                // Shift Management
                activeShiftId: {{ $activeShift->id ?? 'null' }},
                shiftStartTime: '{{ $activeShift?->opened_at?->format('H:i A') ?? '--' }}',
                activeShiftStats: { total_cash: 0 },
                openingCash: 0,
                shiftPin: '',
                closingCash: 0,
                cashiers: @json($cashiers), // Phase 3: Cashier Selection
                warehouseId: {{ $activeShift->warehouse_id ?? auth()->user()->warehouse_id ?? 1 }}, // Stock Truth

                // Cashier Selection (Phase 3)
                selectedCashierId: {{ auth()->id() }},
                currentUserId: {{ auth()->id() }},

                // Delivery State (Phase 3)
                isDeliveryMode: false,
                shippingAddress: '',
                recipientName: '',
                recipientPhone: '',
                selectedDriver: null,
                deliveryFee: 0,

                searchQuery: '',
                productSearch: '',
                isLoading: false,
                isProcessing: false,

                activeCategory: null,
                selectedItemIndex: -1,

                // Payment State (Phase 3 Multi-Payment)
                paymentAmount: 0,
                activePaymentTab: 'cash',
                cartDiscount: 0,
                payments: [], // For split payments
                cardTxnId: '', // For card payments
                creditNote: '', // For credit payments
                expenseModal: null,

                // Delivery Management (Phase 3)
                deliveryOrders: [],
                deliveryModalInstance: null,

                // Customer Selection (F-05)
                selectedCustomer: null,
                customerResults: [],
                isSearchingCustomer: false,
                showCustomerDropdown: false,

                selectCustomer(customer) {
                    this.selectedCustomer = customer;

                    // Auto-fill Delivery Info
                    this.recipientName = customer.name;
                    this.recipientPhone = customer.mobile || customer.phone || '';
                    this.shippingAddress = customer.address || '';

                    this.customerResults = [];
                    this.searchQuery = '';
                    this.showCustomerDropdown = false;
                    this.playBeep();
                },

                // Payment State
                paymentModal: null,
                addCustomerModal: null,
                shiftModal: null,
                activePaymentTab: 'cash',
                paymentAmount: 0,

                // New Customer State
                newCustomer: { name: '', mobile: '' },

                // Held Sales State (C-04 DB Persistence)
                heldSales: [],
                heldSalesModal: null,
                heldCount: 0,
                xReportModal: null,
                xReportData: null,

                currentTime: new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }),

                // Precision Helpers
                roundMoney(val) { return Math.round((val + Number.EPSILON) * 100) / 100; },
                roundNet(val) { return Math.round((val + Number.EPSILON) * 10000) / 10000; },

                calculateLine(qty, price, discount = 0, productTaxRate = null) {
                    const rate = (productTaxRate !== null && productTaxRate !== undefined) ? productTaxRate : {{ $taxRatePercent }};
                    const inclusive = {{ $taxInclusive ? 'true' : 'false' }};
                    let unitPrice = price - (discount / qty);
                    let netAmount, taxAmount, lineTotal;

                    if (inclusive) {
                        // Backend: netPrice = round(unitPrice / (1 + (rate/100)), 4)
                        let netPrice = this.roundNet(unitPrice / (1 + (rate / 100)));
                        lineTotal = this.roundMoney(netPrice * qty * (1 + (rate / 100)));
                        taxAmount = this.roundMoney(lineTotal - (netPrice * qty));
                        netAmount = this.roundNet(netPrice * qty);
                    } else {
                        // Backend: netAmount = round(unitPrice * qty, 2)
                        netAmount = this.roundMoney(unitPrice * qty);
                        taxAmount = this.roundMoney(netAmount * (rate / 100));
                        lineTotal = this.roundMoney(netAmount + taxAmount);
                    }

                    return {
                        qty: qty,
                        price: price,
                        // Original price passed
                        unit_price_net: inclusive ? this.roundNet(unitPrice / (1 + (rate / 100))) : unitPrice,
                        discount: discount,
                        net_amount: netAmount,
                        tax_amount: taxAmount,
                        subtotal: lineTotal, // This is the gross amount shown in cart
                        tax_rate: rate
                    };
                },

                // Computed
                get cartSubtotal() { return this.cart.reduce((a, b) => a + Number(b.net_amount || 0), 0); },
                get cartTax() { return this.cart.reduce((a, b) => a + Number(b.tax_amount || 0), 0); },
                get cartTotal() {
                    let total = this.cart.reduce((a, b) => a + Number(b.subtotal || 0), 0);

                    // Phase 3: Apply cart discount
                    let finalAmount = Math.max(0, total - (this.cartDiscount || 0));

                    // Phase 3: Apply Delivery Fee
                    if (this.isDeliveryMode && this.deliveryFee) {
                        finalAmount += parseFloat(this.deliveryFee) || 0;
                    }

                    return this.roundMoney(finalAmount);
                },

                get remainingAmount() { return Math.max(0, this.cartTotal - this.totalPaid); },
                get changeAmount() { return Math.max(0, this.totalPaid - this.cartTotal); },

                // Phase 3: Multi-Payment Logic
                get totalPaid() {
                    // Start with current input if no split payments added yet
                    if (this.payments.length === 0) return this.paymentAmount;
                    // Otherwise sum up the list + current input (optional, but let's encourage adding to list)
                    return this.payments.reduce((a, b) => a + Number(b.amount), 0);
                },

                addPayment() {
                    if (this.paymentAmount <= 0) return;

                    const method = this.activePaymentTab;
                    const amount = parseFloat(this.paymentAmount);
                    const note = method === 'card' ? this.cardTxnId : (method === 'credit' ? this.creditNote : '');

                    if (method === 'credit' && !this.selectedCustomer) {
                        alert('يجب اختيار عميل للدفع الآجل');
                        return;
                    }

                    this.payments.push({
                        method: method,
                        amount: amount,
                        note: note,
                        label: method === 'cash' ? 'نقد' : (method === 'card' ? 'بطاقة' : 'آجل')
                    });

                    // Reset input for next payment
                    this.paymentAmount = Math.max(0, this.cartTotal - this.totalPaid);
                    this.cardTxnId = '';
                    this.creditNote = '';
                },

                removePayment(index) {
                    this.payments.splice(index, 1);
                    this.paymentAmount = Math.max(0, this.cartTotal - this.totalPaid);
                },

                get canCheckout() {
                    if (this.cart.length === 0) return false;
                    // If split payments exist, must fully pay
                    if (this.payments.length > 0) {
                        return this.remainingAmount <= 0.1;
                    }
                    // Simple mode
                    if (this.activePaymentTab === 'credit' && !this.selectedCustomer) return false;
                    return this.remainingAmount <= 0.1 || this.activePaymentTab === 'credit';
                },

                initPOS() {
                    this.fetchProducts();

                    // Initialize ALL Modals to prevent trigger errors
                    this.paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    this.addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
                    this.shiftModal = new bootstrap.Modal(document.getElementById('shiftModal'));
                    this.heldSalesModal = new bootstrap.Modal(document.getElementById('heldSalesModal'));
                    this.deliveryModalInstance = new bootstrap.Modal(document.getElementById('deliveryModal'));
                    this.expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
                    this.recentTransactionsModal = new bootstrap.Modal(document.getElementById('recentTransactionsModal'));
                    this.returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
                    this.priceOverrideModalInstance = new bootstrap.Modal(document.getElementById('priceOverrideModal'));
                    this.xReportModal = new bootstrap.Modal(document.getElementById('xReportModal'));

                    this.initDeliveryWatcher();
                    this.fetchHeldSales();
                    if (this.activeShiftId) this.fetchShiftStats();

                    setInterval(() => {
                        this.currentTime = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
                    }, 1000);

                    // Reliability: Warn on unsaved changes
                    window.onbeforeunload = (e) => {
                        if (this.cart && this.cart.length > 0) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    };
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
                    const currentQty = existing ? existing.qty : 0;

                    // Phase 3: Stock warning before adding
                    if (product.stock <= 0) {
                        alert('⚠️ هذا المنتج نفد من المخزون');
                        return;
                    }
                    if (currentQty + 1 > product.stock) {
                        alert(`⚠️ الكمية المتاحة من (${product.name}) هي ${product.stock} فقط`);
                        return;
                    }

                    if (existing) {
                        const calc = this.calculateLine(existing.qty + 1, existing.price, existing.discount || 0, product.tax_rate);
                        Object.assign(existing, calc);
                    } else {
                        const calc = this.calculateLine(1, Number(product.price), 0, product.tax_rate);
                        this.cart.push({
                            ...product,
                            ...calc,
                            original_price: Number(product.price)
                        });
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
                    const calc = this.calculateLine(item.qty + delta, item.price, item.discount || 0, item.tax_rate);
                    Object.assign(item, calc);
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

                    // FIX: Map cart items to match backend validation
                    const mappedItems = this.cart.map(item => ({
                        product_id: item.id,
                        quantity: item.qty,
                        // TRUTH: Send the price as handled in calculateLine (Gross/Net based on setting)
                        // Backend (POSService) will handle tax extraction/addition correctly.
                        price: item.price,
                        discount: item.discount || 0,
                    }));

                    // Phase 3: Multi-Payment Logic
                    let finalPayments = [];
                    if (this.payments.length > 0) {
                        finalPayments = this.payments;
                    } else {
                        // Simple Mode (Single Payment)
                        finalPayments = [{
                            method: this.activePaymentTab,
                            amount: this.paymentAmount,
                            note: this.activePaymentTab === 'card' ? this.cardTxnId : (this.activePaymentTab === 'credit' ? this.creditNote : '')
                        }];
                    }

                    const payload = {
                        items: mappedItems,
                        customer_id: this.selectedCustomer ? this.selectedCustomer.id : null,
                        payments: finalPayments,
                        discount: this.cartDiscount || 0,
                        notes: '',
                        warehouse_id: this.warehouseId,
                        // Phase 3: Delivery Data
                        is_delivery: this.isDeliveryMode,
                        driver_id: this.isDeliveryMode ? this.selectedDriver : null,
                        delivery_fee: this.isDeliveryMode ? (this.deliveryFee || 0) : 0,
                        shipping_address: this.isDeliveryMode ? this.shippingAddress : null,
                        // Phase 3: Cashier Selection
                        cashier_id: this.selectedCashierId,
                    };

                    try {
                        // F-01 FIX: Real API call instead of simulation
                        const res = await axios.post('{{ route("pos.checkout") }}', payload);

                        if (res.data.success) {
                            this.playSuccess();
                            this.closePaymentModal();
                            this.cart = [];
                            this.paymentAmount = 0;
                            this.cartDiscount = 0; // Reset discount after checkout

                            // Show success and offer to print receipt
                            if (confirm('تمت العملية بنجاح! فاتورة رقم: ' + res.data.invoice.number + '\n\nهل تريد طباعة الفاتورة؟')) {
                                window.open('{{ url("pos/receipt") }}/' + res.data.invoice.id, '_blank');
                            }
                        }
                    } catch (e) {
                        alert('فشل العملية: ' + (e.response?.data?.message || e.message));
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // --- CUSTOMER LOGIC (F-05) ---
                async loadRecentCustomers() {
                    // Load recent customers when input is focused (for dropdown behavior)
                    if (this.customerResults.length > 0 || this.selectedCustomer) {
                        return; // Already have results or customer selected
                    }
                    this.isSearchingCustomer = true;
                    try {
                        const res = await axios.get('/pos/customers/search', {
                            params: { q: '' } // Empty query to get recent customers
                        });
                        this.customerResults = res.data;
                    } catch (e) {
                        console.error('Load recent customers failed', e);
                    } finally {
                        this.isSearchingCustomer = false;
                    }
                },

                async searchCustomer() {
                    // Search with any query length (including empty to show all)
                    this.isSearchingCustomer = true;
                    try {
                        const res = await axios.get('/pos/customers/search', {
                            params: { q: this.searchQuery }
                        });
                        this.customerResults = res.data;
                    } catch (e) {
                        console.error('Customer search failed', e);
                    } finally {
                        this.isSearchingCustomer = false;
                    }
                },



                clearSelectedCustomer() {
                    this.selectedCustomer = null;
                },

                openAddCustomerModal() { this.addCustomerModal.show(); },

                async quickCreateCustomer() {
                    try {
                        const res = await axios.post('/pos/customers/quick-create', this.newCustomer);
                        if (res.data.success) {
                            this.selectCustomer(res.data.customer);
                            this.addCustomerModal.hide();
                            this.newCustomer = { name: '', mobile: '' };
                        }
                    } catch (e) { alert('فشل إضافة العميل'); }
                },

                // --- HELD SALES LOGIC (C-04 DB Persistence) ---
                async holdSale() {
                    if (this.cart.length === 0) {
                        alert('السلة فارغة!');
                        return;
                    }
                    try {
                        const res = await axios.post('{{ route("pos.hold") }}', {
                            items: this.cart,
                            customer_id: null, // TODO: selected customer
                            warehouse_id: this.warehouseId,
                            subtotal: this.cartSubtotal,
                            tax: this.cartTax,
                            total: this.cartTotal,
                            notes: ''
                        });
                        if (res.data.success) {
                            alert('تم تعليق الفاتورة: ' + res.data.held_sale.hold_number);
                            this.cart = [];
                            this.fetchHeldSales();
                        }
                    } catch (e) {
                        alert('فشل تعليق الفاتورة: ' + (e.response?.data?.message || e.message));
                    }
                },

                async fetchHeldSales() {
                    try {
                        const res = await axios.get('{{ route("pos.held") }}');
                        this.heldSales = res.data;
                        this.heldCount = this.heldSales.length;
                    } catch (e) {
                        console.error('Failed to fetch held sales:', e);
                    }
                },

                showHeldSales() {
                    this.fetchHeldSales();
                    if (this.heldSalesModal) {
                        this.heldSalesModal.show();
                    }
                },

                async resumeSale(holdId) {
                    try {
                        const res = await axios.post('{{ route("pos.resume") }}', { hold_id: holdId });
                        if (res.data.success) {
                            // Load cart with resumed items
                            this.cart = res.data.items || [];
                            this.fetchHeldSales();
                            if (this.heldSalesModal) {
                                this.heldSalesModal.hide();
                            }
                            alert('تم استئناف الفاتورة');
                        }
                    } catch (e) {
                        alert('فشل استئناف الفاتورة: ' + (e.response?.data?.error || e.message));
                    }
                },

                // --- SHIFT LOGIC ---
                showShiftModal() {
                    if (this.activeShiftId) this.fetchShiftStats();
                    this.shiftModal.show();
                },

                async fetchShiftStats() {
                    try {
                        const res = await axios.get('{{ route("pos.shift.stats") }}');
                        if (res.data.success) {
                            this.activeShiftStats = res.data.shift;
                        }
                    } catch (e) { console.error('Failed to fetch shift stats'); }
                },

                async openShift() {
                    this.isProcessing = true;
                    try {
                        const res = await axios.post('{{ route("pos.shift.open") }}', {
                            opening_cash: this.openingCash
                        });
                        if (res.data.success) {
                            this.activeShiftId = res.data.shift.id;
                            this.shiftStartTime = new Date(res.data.shift.opened_at).toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
                            alert('تم فتح الوردية بنجاح!');
                            this.shiftModal.hide();
                            location.reload(); // Refresh to update $activeShift in blade
                        }
                    } catch (e) {
                        alert('فشل فتح الوردية: ' + (e.response?.data?.message || e.message));
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async closeShift() {
                    if (this.closingCash < 0) {
                        alert('رقم غير صحيح للنقدية');
                        return;
                    }
                    if (!this.shiftPin) {
                        alert('الرجاء إدخال رمز الإغلاق (PIN)');
                        return;
                    }
                    if (!confirm('تأكيد إغلاق الوردية؟ سيتم ترحيل الفروقات إن وجدت.')) return;

                    try {
                        // F-03 FIX: Real API call for shift close
                        const res = await axios.post('{{ route("pos.shift.close") }}', {
                            closing_cash: this.closingCash,
                            pin: this.shiftPin,
                            notes: ''
                        });

                        if (res.data.success) {
                            alert('تم إغلاق الوردية بنجاح!');
                            // Redirect to shift report (Z-Report)
                            if (res.data.report_url) {
                                window.location.href = res.data.report_url;
                            } else {
                                window.location.href = '{{ route("dashboard") }}';
                            }
                        }
                    } catch (e) {
                        alert('فشل إغلاق الوردية: ' + (e.response?.data?.message || e.message));
                    }
                },

                // --- PHASE 3: X-Report, Transactions, Cash Drawer ---
                async showXReport() {
                    this.xReportData = null; // Reset for loader
                    if (this.xReportModal) this.xReportModal.show();
                    try {
                        const res = await axios.get('{{ route("pos.xReport") }}');
                        if (res.data.success) {
                            this.xReportData = res.data.data;
                        }
                    } catch (e) {
                        alert('فشل تحميل التقرير: ' + (e.response?.data?.message || e.message));
                        if (this.xReportModal) this.xReportModal.hide();
                    }
                },

                async showLastTransactions() {
                    try {
                        const res = await axios.get('{{ route("pos.lastTransactions") }}');
                        if (res.data.success) {
                            const txs = res.data.data;
                            if (txs.length === 0) {
                                alert('لا توجد معاملات في هذه الوردية');
                                return;
                            }
                            let list = "═══════ آخر المعاملات ═══════\n\n";
                            txs.forEach(t => {
                                list += `• ${t.created_at} | #${t.invoice_number} | ${t.customer_name}\n`;
                                list += `  المبلغ: ${this.formatNumber(t.total)} EGP (${t.payment_method})\n\n`;
                            });
                            alert(list);
                        }
                    } catch (e) {
                        alert('فشل تحميل المعاملات: ' + (e.response?.data?.message || e.message));
                    }
                },

                async openCashDrawer() {
                    const reason = prompt('سبب فتح الدرج (اختياري):');
                    try {
                        const res = await axios.post('{{ route("pos.drawer.open") }}', { reason });
                        if (res.data.success) {
                            alert('✅ ' + res.data.message);
                        }
                    } catch (e) {
                        alert('فشل فتح الدرج: ' + (e.response?.data?.message || e.message));
                    }
                },

                // Enhanced removeItem with deletion logging
                async removeItem(index) {
                    const item = this.cart[index];
                    if (!item) return;

                    // Log the deletion to backend for audit trail
                    try {
                        await axios.post('{{ route("pos.cart.logDeletion") }}', {
                            product_id: item.id,
                            product_name: item.name,
                            quantity: item.qty,
                            price: item.price,
                            reason: null // Could add reason prompt for high-value items
                        });
                    } catch (e) {
                        console.warn('Failed to log cart deletion', e);
                    }

                    this.cart.splice(index, 1);
                },

                // --- PHASE 3: Price Override ---
                overrideItem: null,
                overrideIndex: -1,
                overrideNewPrice: 0,
                overridePin: '',
                overrideReason: '',
                isProcessingOverride: false,

                get isPriceLower() {
                    return this.overrideItem && this.overrideNewPrice < this.overrideItem.original_price;
                },

                openPriceOverride(index) {
                    const item = this.cart[index];
                    this.overrideIndex = index;
                    // Fallback to current price if original not set (legacy items)
                    const original = item.original_price || item.price;

                    this.overrideItem = { ...item, original_price: original };
                    this.overrideNewPrice = item.price;
                    this.overridePin = '';
                    this.overrideReason = '';

                    const modal = new bootstrap.Modal(document.getElementById('priceOverrideModal'));
                    modal.show();
                    this.priceOverrideModalInstance = modal;
                },

                async submitPriceOverride() {
                    if (this.isPriceLower && !this.overridePin) {
                        alert('مطلوب رمز المدير');
                        return;
                    }

                    this.isProcessingOverride = true;
                    try {
                        // Backend validation
                        const res = await axios.post('{{ route("pos.pin.priceOverride") }}', {
                            pin: this.overridePin,
                            product_id: this.overrideItem.id,
                            original_price: this.overrideItem.original_price,
                            override_price: this.overrideNewPrice,
                            reason: this.overrideReason
                        });

                        if (res.data.success) {
                            // Update cart
                            const item = this.cart[this.overrideIndex];
                            const calc = this.calculateLine(item.qty, parseFloat(this.overrideNewPrice), item.discount || 0, item.tax_rate);
                            Object.assign(item, calc);

                            // If price is lower, ensure we keep original_price for future checks
                            if (!item.original_price) {
                                item.original_price = this.overrideItem.original_price;
                            }

                            this.priceOverrideModalInstance.hide();
                            this.playSuccess();
                        }
                    } catch (e) {
                        alert('فشل تغيير السعر: ' + (e.response?.data?.message || e.message));
                        this.playBeep();
                    } finally {
                        this.isProcessingOverride = false;
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
                    if (e.key === 'F8') { e.preventDefault(); this.printLastInvoice(); }
                },

                // --- PHASE 3: Refund Mode ---
                setRefundMode(enable) {
                    this.refundMode = enable;
                    if (enable) {
                        this.playBeep();
                        // Show visual indicator
                        document.body.style.borderTop = '4px solid #ef4444';
                    } else {
                        document.body.style.borderTop = 'none';
                    }
                },

                // --- PHASE 3: Expense Modal ---
                expenseAmount: 0,
                expenseNotes: '',

                openExpenseModal() {
                    this.expenseAmount = '';
                    this.expenseNotes = '';
                    if (!this.expenseModal) {
                        const el = document.getElementById('expenseModal');
                        if (el) this.expenseModal = new bootstrap.Modal(el);
                    }
                    if (this.expenseModal) this.expenseModal.show();
                    else alert('Error: Expense Modal element not found');
                },

                async saveExpense() {
                    this.isProcessing = true;
                    try {
                        const res = await axios.post('{{ route("pos.expenses.store") }}', {
                            amount: this.expenseAmount,
                            notes: this.expenseNotes
                        });

                        if (res.data.success) {
                            alert('✅ تم تسجيل المصروف بنجاح. رصيد الدرج المتوقع: ' + this.formatMoney(res.data.balance));
                            this.expenseModal.hide();
                            this.expenseAmount = '';
                            this.expenseNotes = '';
                        }
                    } catch (e) {
                        console.error(e);
                        alert('❌ فشل تسجيل المصروف: ' + (e.response?.data?.message || e.message));
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // --- PHASE 3.5: Delivery Reliability ---
                async updateDeliveryStatus(id, status) {
                    if (!confirm('تغيير حالة الطلب إلى ' + status + '؟')) return;

                    try {
                        const res = await axios.post('{{ route("pos.delivery.status") }}', { id, status });
                        if (res.data.success) {
                            // Fix: Remove from list immediately if delivered/cancelled
                            if (['delivered', 'cancelled', 'returned'].includes(status)) {
                                this.deliveryOrders = this.deliveryOrders.filter(o => o.id !== id);
                            } else {
                                this.fetchDeliveryOrders();
                            }
                            // alert('تم تحديث الحالة');
                        }
                    } catch (e) {
                        alert('فشل تحديث الحالة: ' + (e.response?.data?.message || e.message));
                    }
                },

                initDeliveryWatcher() {
                    this.$watch('selectedCustomer', (val) => {
                        if (this.isDeliveryMode && val) {
                            if (!this.shippingAddress && val.address) {
                                this.shippingAddress = val.address;
                            }
                        }
                    });
                },

                // --- PHASE 3.5: Returns Logic ---
                recentTransactions: [],
                recentTransactionsModal: null,
                returnModal: null,
                returnInvoice: null,
                returnItems: [], // {line_id, quantity, max_qty, price}
                returnReason: '',
                returnPin: '',

                openRecentTransactions() {
                    this.fetchRecentTransactions();
                    if (this.recentTransactionsModal) this.recentTransactionsModal.show();
                },

                async fetchRecentTransactions() {
                    this.isLoading = true;
                    try {
                        const res = await axios.get('{{ route("pos.lastTransactions") }}?limit=20');
                        // Correctly handle the 'data' wrapper from POSController@lastTransactions
                        this.recentTransactions = res.data.data || res.data;
                    } catch (e) {
                        console.error(e);
                        alert('فشل تحميل العمليات الأخيرة');
                    } finally {
                        this.isLoading = false;
                    }
                },

                openReturnModal(invoice) {
                    this.returnInvoice = invoice;
                    // Fix: Use correct line mapping for invoice items
                    const lines = invoice.lines || invoice.items || [];
                    this.returnItems = lines.map(line => ({
                        line_id: line.id,
                        product_name: line.product ? (line.product.name || line.product_name) : (line.product_name || 'Unknown'),
                        original_qty: line.quantity || line.qty,
                        max_qty: line.remaining_quantity !== undefined ? line.remaining_quantity : (line.quantity || line.qty),
                        return_qty: 0,
                        price: line.unit_price || line.price || 0
                    }));
                    this.returnReason = '';
                    this.returnPin = '';

                    if (this.recentTransactionsModal) this.recentTransactionsModal.hide();
                    if (this.returnModal) this.returnModal.show();
                },

                async submitReturn() {
                    const itemsToReturn = this.returnItems
                        .filter(i => i.return_qty > 0)
                        .map(i => ({
                            line_id: i.line_id,
                            quantity: i.return_qty
                        }));

                    if (itemsToReturn.length === 0) {
                        alert('الرجاء تحديد كميات للاسترجاع');
                        return;
                    }
                    if (!this.returnPin) {
                        alert('الرجاء إدخال رمز الإغلاق (PIN)');
                        return;
                    }

                    if (!confirm('تأكيد عملية المرتجع؟')) return;

                    this.isProcessing = true;
                    try {
                        const res = await axios.post('{{ route("pos.return") }}', {
                            invoice_id: this.returnInvoice.id,
                            items: itemsToReturn,
                            reason: this.returnReason || 'POS Return',
                            pin: this.returnPin
                        });

                        if (res.data.success) {
                            alert('تم إرجاع الأصناف بنجاح\nرقم المرتجع: ' + (res.data.return?.number || ''));
                            this.returnModal.hide();
                            this.openRecentTransactions(); // Re-open list
                        }
                    } catch (e) {
                        alert('فشل المرتجع: ' + (e.response?.data?.message || e.message));
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // --- PHASE 3: Fullscreen Toggle ---
                toggleFullScreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error('Fullscreen error:', err);
                        });
                        this.isFullscreen = true;
                    } else {
                        document.exitFullscreen();
                        this.isFullscreen = false;
                    }
                },

                // --- PHASE 3: Print Last Invoice (F8) ---
                async printLastInvoice() {
                    try {
                        const res = await axios.get('{{ route("pos.lastTransactions") }}?limit=1');
                        const transactions = res.data.data || res.data;
                        if (transactions && transactions.length > 0) {
                            const lastInvoice = transactions[0];
                            window.open('/pos/receipt/' + lastInvoice.id, '_blank');
                        } else {
                            alert('لا توجد فواتير سابقة');
                        }
                    } catch (e) {
                        alert('فشل جلب آخر فاتورة');
                    }
                },

                // --- PHASE 3: Cart Discount ---
                cartDiscount: 0,
                applyCartDiscount(amount) {
                    this.cartDiscount = parseFloat(amount) || 0;
                },

                // --- PHASE 3: Delivery Management ---
                showDeliveryModal() {
                    if (!this.deliveryModalInstance) {
                        this.deliveryModalInstance = new bootstrap.Modal(document.getElementById('deliveryModal'));
                    }
                    this.fetchDeliveryOrders();
                    this.deliveryModalInstance.show();
                },

                async fetchDeliveryOrders() {
                    try {
                        const res = await axios.get('{{ route("pos.delivery.list") }}');
                        if (res.data.success) {
                            this.deliveryOrders = res.data.data;
                        }
                    } catch (e) {
                        console.error('Failed to fetch delivery orders:', e);
                    }
                },

                async updateDeliveryStatus(id, status) {
                    if (!confirm('تغيير حالة الطلب إلى ' + status + '؟')) return;

                    try {
                        const res = await axios.post('{{ route("pos.delivery.status") }}', { id, status });
                        if (res.data.success) {
                            // alert('تم تحديث الحالة');
                            this.fetchDeliveryOrders();
                        }
                    } catch (e) {
                        alert('فشل تحديث الحالة: ' + (e.response?.data?.message || e.message));
                    }
                },

                getDeliveryStatusClass(status) {
                    switch (status) {
                        case 'ready': return 'bg-primary';
                        case 'shipped': return 'bg-warning text-dark';
                        case 'delivered': return 'bg-success';
                        case 'returned': return 'bg-danger';
                        case 'cancelled': return 'bg-secondary';
                        default: return 'bg-secondary';
                    }
                }
            }
        }
    </script>
@endsection