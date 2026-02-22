@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;</script>
    <!-- POS CLEAN SLATE: GLOBAL STANDARD RECONSTRUCTION v3.3 (Layout Fixes) -->
    <div class="pos-workspace d-flex vh-100 overflow-hidden" style="background-color: var(--body-bg);" x-data="posStore()"
        x-init="initPOS()" @keydown.window="handleGlobalKeys($event)">

        <!-- ==================== LEFT: CART & ACTION HUB (Fixed Width) ==================== -->
        <aside class="pos-cart-panel bg-slate-950 border-end border-slate-800 d-flex flex-column shadow-2xl z-30"
            style="width: 400px; min-width: 400px;">

            <header class="p-3 border-bottom border-slate-800 shadow-sm"
                style="background-color: var(--card-bg); border-color: var(--border-color) !important;">
                <!-- CUSTOMER SEARCH -->
                <div class="position-relative">
                    <div class="input-group input-group-slate shadow-sm border border-slate-700">
                        <span class="input-group-text bg-transparent border-0 text-slate-400 ps-3"><i
                                class="bi bi-person-circle fs-5"></i></span>
                        <!-- Fake fields to trick browser autofill -->
                        <input type="text" style="display:none">
                        <input type="password" style="display:none">

                        <input type="text" id="customer-search-input"
                            class="form-control bg-transparent border-0 text-white shadow-none py-2"
                            placeholder="{{ __('Select customer or search by name/code/mobile...') }}" x-model="searchQuery"
                            autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                            @focus="loadRecentCustomers(); showCustomerDropdown = true"
                            @input.debounce.300ms="searchCustomer()"
                            @blur="setTimeout(() => showCustomerDropdown = false, 200)">
                        <button class="btn btn-link text-emerald-400 decoration-none fw-bold small pe-3"
                            @click="openAddCustomerModal()">
                            <i class="bi bi-plus-lg"></i>{{ __('New') }}</button>
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
                        <i class="bi bi-info-circle me-1"></i> {{ __('Walk-in Customer (Cash)') }}
                    </div>
                </div>
            </header>

            <!-- DELIVERY INFO PANEL (Compact Grid) -->
            <div x-show="isDeliveryMode" x-transition.opacity
                class="p-2.5 bg-blue-600/5 border-bottom border-blue-500/20 backdrop-blur-sm">

                <div class="row g-2">
                    <!-- Driver & Fee Row -->
                    <div class="col-6">
                        <div class="input-group input-group-sm border border-slate-700 rounded overflow-hidden">
                            <span class="input-group-text bg-slate-800 border-0 text-blue-400 px-2"><i
                                    class="bi bi-truck"></i></span>
                            <select x-model="selectedDriver"
                                class="form-select bg-slate-900 border-0 text-white fs-[11px] py-1 shadow-none">
                                <option value="">{{ __('Select Driver...') }}</option>
                                <template x-for="d in drivers" :key="d.id">
                                    <option :value="d.id" x-text="d.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group input-group-sm border border-slate-700 rounded overflow-hidden">
                            <span class="input-group-text bg-slate-800 border-0 text-amber-400 px-2"><i
                                    class="bi bi-currency-exchange"></i></span>
                            <input type="number" x-model.number="deliveryFee"
                                class="form-control bg-slate-900 border-0 text-white text-center fs-[11px] py-1 shadow-none"
                                placeholder="{{ __('Fees') }}">
                            <span class="input-group-text bg-slate-800 border-0 text-slate-500 text-[9px] px-1">EGP</span>
                        </div>
                    </div>

                    <!-- Recipient details -->
                    <div class="col-6">
                        <div class="input-group input-group-sm border border-slate-700 rounded overflow-hidden">
                            <span class="input-group-text bg-slate-800 border-0 text-slate-400 px-2"><i
                                    class="bi bi-person"></i></span>
                            <input type="text" x-model="recipientName"
                                class="form-control bg-slate-900 border-0 text-white fs-[11px] py-1 shadow-none"
                                placeholder="{{ __('Receiver Name...') }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group input-group-sm border border-slate-700 rounded overflow-hidden">
                            <span class="input-group-text bg-slate-800 border-0 text-slate-400 px-2"><i
                                    class="bi bi-phone"></i></span>
                            <input type="text" x-model="recipientPhone"
                                class="form-control bg-slate-900 border-0 text-white fs-[11px] py-1 shadow-none"
                                placeholder="{{ __('Mobile...') }}">
                        </div>
                    </div>

                    <!-- Full Address -->
                    <div class="col-12">
                        <div class="input-group input-group-sm border border-slate-700 rounded overflow-hidden">
                            <span class="input-group-text bg-slate-800 border-0 text-slate-400 px-2"><i
                                    class="bi bi-geo-alt"></i></span>
                            <input type="text" x-model="shippingAddress"
                                class="form-control bg-slate-900 border-0 text-white fs-[11px] py-1 shadow-none"
                                placeholder="{{ __('Delivery Address Details...') }}">
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
                        <p class="fw-bold fs-5">{{ __('Cart is empty') }}</p>
                        <p class="small">{{ __('Scan barcode to start selling') }}</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.id + '-' + index">
                    <div class="cart-item group px-2.5 py-2 mb-1.5 rounded-xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-600/50 hover:bg-slate-800/30 transition-all cursor-pointer relative overflow-hidden"
                        :class="{'border-emerald-500/50 bg-emerald-900/10 ring-1 ring-emerald-500/20': selectedItemIndex === index}"
                        @click="selectItem(index)">

                        <!-- Progress Bar for selection feedback -->
                        <div x-show="selectedItemIndex === index"
                            class="position-absolute top-0 start-0 h-1 bg-emerald-500/50 transition-all w-100"></div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-white text-[13px] truncate" x-text="item.name"></div>
                                <div class="text-[10px] text-slate-500 font-mono d-flex gap-2">
                                    <span x-show="item.sku">SKU: <span x-text="item.sku"></span></span>
                                    <span class="text-blue-400/70"
                                        x-text="formatMoney(item.price) + ' × ' + item.qty"></span>
                                </div>
                            </div>
                            <div class="text-end ms-2">
                                <div class="fw-black text-emerald-400 text-sm font-mono"
                                    x-text="formatMoney(item.subtotal)"></div>
                            </div>
                        </div>

                        <div
                            class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top border-slate-800/30">
                            <!-- QTY CONTROL (Mini) -->
                            <div
                                class="d-flex align-items-center bg-slate-950/50 rounded-lg border border-slate-800/50 p-0.5">
                                <button class="btn btn-xs text-slate-500 hover:text-white px-1.5"
                                    @click.stop="updateQty(index, -1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="px-2 fw-black text-white font-mono text-xs" x-text="item.qty"></span>
                                <button class="btn btn-xs text-slate-500 hover:text-white px-1.5"
                                    @click.stop="updateQty(index, 1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-xs text-slate-500 hover:text-emerald-400 transition-colors"
                                    @click.stop="openPriceOverride(index)" title="{{ __('Edit Price') }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button
                                    class="btn btn-xs text-slate-600 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all"
                                    @click.stop="removeItem(index)">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- TOTALS & ACTION PAD (Premium Revamp) -->
            <footer
                class="p-3 bg-slate-950 border-top border-slate-800 shadow-[0_-15px_40px_rgba(0,0,0,0.6)] mt-auto z-10 relative">
                <div
                    class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-emerald-500/20 to-transparent">
                </div>

                <!-- Values Grid -->
                <div class="bg-slate-900/50 rounded-xl p-3 border border-slate-800/50 mb-3">
                    <div class="row g-3 fs-[11px] fw-bold text-slate-400">
                        <div class="col-6">
                            <div class="d-flex justify-content-between mb-1 opacity-70">
                                <span>{{ __('Total') }}:</span>
                                <span class="text-white font-mono" x-text="formatMoney(cartSubtotal)"></span>
                            </div>
                            <div class="d-flex justify-content-between opacity-70">
                                <span>{{ __('Tax') }}:</span>
                                <span class="text-white font-mono" x-text="formatMoney(cartTax)"></span>
                            </div>
                        </div>
                        <div class="col-6 border-start border-slate-800/50 ps-3">
                            <div class="d-flex justify-content-between align-items-center h-100">
                                <div class="flex-grow-1">
                                    <span class="text-rose-400 d-block fs-[10px] text-uppercase mb-1"><i
                                            class="bi bi-tag-fill me-1"></i>{{ __('Discount') }} (EGP)</span>
                                    <div class="input-group input-group-sm">
                                        <input type="number"
                                            class="form-control bg-slate-800 border-rose-900/30 text-rose-400 text-center fw-black font-mono shadow-none"
                                            x-model.number="cartDiscount" placeholder="0.0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grand Total Highlight -->
                    <div class="mt-3 pt-3 border-top border-slate-800/80 d-flex justify-content-between align-items-center">
                        <div class="lh-1">
                            <span class="text-slate-500 text-[10px] fw-black text-uppercase tracking-widest">الإجمالي
                                النهائي</span>
                            <div class="text-emerald-500 text-[11px] mt-1 font-mono fw-bold">TAX INCLUDED</div>
                        </div>
                        <div class="text-end">
                            <span class="fs-1 fw-black text-white font-mono tracking-tighter"
                                x-text="formatMoney(cartTotal)"></span>
                        </div>
                    </div>
                </div>

                <!-- Action Pad -->
                <div class="row g-2">
                    <div class="col-4">
                        <button
                            class="btn btn-slate-800 w-100 py-2.5 text-slate-300 border-slate-700/50 rounded-xl hover:bg-slate-700 transition-all d-flex flex-column align-items-center gap-1"
                            @click="showHeldSales()">
                            <i class="bi bi-collection-play text-amber-500 fs-5"></i>
                            <span class="text-[10px] fw-bold">{{ __('Pending') }}</span>
                        </button>
                    </div>
                    <div class="col-4">
                        <button
                            class="btn btn-slate-800 w-100 py-2.5 text-slate-300 border-slate-700/50 rounded-xl hover:bg-slate-700 transition-all d-flex flex-column align-items-center gap-1"
                            @click="holdSale()">
                            <i class="bi bi-pause-circle text-blue-400 fs-5"></i>
                            <span class="text-[10px] fw-bold">{{ __('Suspend') }}</span>
                        </button>
                    </div>
                    <div class="col-4">
                        <button
                            class="btn btn-slate-800 w-100 py-2.5 text-slate-500 border-slate-700/50 rounded-xl hover:bg-rose-900/20 hover:text-rose-400 transition-all d-flex flex-column align-items-center gap-1"
                            @click="clearCart()">
                            <i class="bi bi-trash3 fs-5"></i>
                            <span class="text-[10px] fw-bold">{{ __('Cancel') }}</span>
                        </button>
                    </div>
                    <div class="col-12 mt-1">
                        <button @click="showPaymentModal()" :disabled="cart.length === 0"
                            class="btn btn-emerald-600 w-100 py-3 rounded-2xl shadow-emerald-glow hover:bg-emerald-500 active:scale-[0.98] transition-all border-0 overflow-hidden relative group">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-emerald-400/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-3 relative z-10">
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-white/10 d-flex align-items-center justify-content-center shadow-inner">
                                        <i class="bi bi-credit-card-2-back fs-4"></i>
                                    </div>
                                    <div class="text-start lh-1">
                                        <span class="d-block fs-5 fw-black">{{ __('Pay & Checkout') }}</span>
                                        <span class="text-[10px] text-white/60 fw-bold">SHORTCUT (F10)</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-4 fw-black font-mono shadow-sm" x-text="formatMoney(cartTotal)"></span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </footer>
        </aside>


        <!-- ==================== CENTER: PRODUCT GRID (Dynamic) ==================== -->
        <main class="flex-grow-1 d-flex flex-column position-relative border-end border-slate-800 overflow-hidden"
            style="background-color: var(--body-bg); border-color: var(--border-color) !important; background-image: radial-gradient(var(--border-color) 1px, transparent 1px); background-size: 20px 20px;">

            <!-- SYSTEM-INTEGRATED POS HEADER (Aesthetic Convergence) -->
            <div class="px-5 py-3.5 border-bottom border-slate-800 d-flex gap-4 sticky-top z-20 align-items-center justify-content-between"
                style="background-color: var(--body-bg); border-color: var(--border-color) !important;">

                <!-- LEFT COMMANDS: SYSTEM-CARD STYLE -->
                <div class="d-flex gap-3 align-items-center">

                    <!-- HISTORY BUTTON (System Square) -->
                    <button
                        class="btn btn-slate-action d-flex align-items-center justify-content-center w-12 h-12 shadow-sm"
                        @click="openRecentTransactions()" title="{{ __('Transaction History') }}">
                        <i class="bi bi-clock-history fs-4"></i>
                    </button>

                    <!-- WAREHOUSE SELECTOR (System Card) -->
                    <div class="dropdown">
                        <button class="btn btn-slate-action d-flex align-items-center gap-3 px-4 h-12 shadow-sm"
                            data-bs-toggle="dropdown">
                            <div
                                class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 d-flex align-items-center justify-content-center border border-blue-500/20 shadow-inner">
                                <i class="bi bi-box-seam-fill fs-6"></i>
                            </div>
                            <div class="text-start d-none d-lg-block">
                                <div class="text-[9px] text-slate-500 fw-black uppercase tracking-widest leading-none mb-1">
                                    {{ __('Warehouse') }}
                                </div>
                                <div class="text-[13px] fw-black text-white leading-none" x-text="getWarehouseName()">---
                                </div>
                            </div>
                            <i class="bi bi-chevron-down text-slate-600 ms-1 opacity-50 scale-75"></i>
                        </button>
                        <ul
                            class="dropdown-menu dropdown-menu-dark shadow-2xl border-slate-700 fs-xs rounded-xl p-2 bg-slate-900 mt-2 text-end">
                            <li
                                class="px-3 py-2 text-[10px] text-slate-500 fw-black uppercase tracking-widest border-bottom border-slate-800 mb-2 opacity-60">
                                اختر المستودع</li>
                            @foreach($warehouses as $w)
                                <li><a class="dropdown-item py-2.5 px-3 rounded-lg hover:bg-slate-800 hover:text-emerald-400 transition-all font-bold d-flex align-items-center justify-content-end gap-3 mb-1"
                                        href="#" @click.prevent="setWarehouse({{ $w->id }}, '{{ $w->name }}')">
                                        <span class="fs-6">{{ $w->name }}</span>
                                        <i class="bi bi-building text-[11px]"></i>
                                    </a></li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- SHIFT INFO (System Card) -->
                    <div class="btn-slate-action d-flex align-items-center gap-4 px-4 h-12 shadow-sm cursor-pointer"
                        @click="showShiftModal()">
                        <div
                            class="flex-shrink-0 order-last ps-3 border-start border-slate-800 d-flex align-items-center gap-3">
                            <div class="text-center">
                                <div class="text-[8px] text-slate-500 fw-black uppercase leading-none mb-0.5">رقم</div>
                                <div class="text-[12px] font-mono fw-black text-emerald-500 leading-none">
                                    #{{ $activeShift->id ?? '---' }}</div>
                            </div>
                            <div class="relative">
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 d-flex align-items-center justify-content-center border border-emerald-500/20 shadow-inner">
                                    <i class="bi bi-person-fill fs-6"></i>
                                </div>
                                <span
                                    class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full animate-pulse"></span>
                            </div>
                        </div>
                        <div class="text-end d-none d-xl-block">
                            <div
                                class="text-[9px] text-emerald-500/70 fw-black uppercase tracking-widest leading-none mb-1">
                                الوردية نشطة</div>
                            <div class="text-[13px] fw-black text-white leading-none">{{ auth()->user()->name }}</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SEARCH: PREMIUM ACTIVE FIELD -->
                <div class="flex-grow-1 max-w-[450px]">
                    <div class="position-relative group">
                        <!-- Main Input Container -->
                        <div class="d-flex align-items-center bg-slate-900/50 border border-slate-700 h-14 px-4 shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] transition-all focus-within:bg-slate-900 focus-within:ring-2 focus-within:ring-emerald-500/30 focus-within:border-emerald-500/50 hover:border-slate-600 rounded-full"
                            style="border-radius: 9999px !important;">

                            <!-- Search Icon (Fixed) -->
                            <div class="text-slate-500 ps-1 pe-3">
                                <i class="bi bi-search fs-5 group-focus-within:text-emerald-400 transition-colors"></i>
                            </div>

                            <!-- Input Field -->
                            <input type="text"
                                class="form-control bg-transparent border-0 text-white shadow-none h-100 fs-5 fw-bold text-end ps-1 pe-2 placeholder-slate-600 tracking-wide"
                                placeholder="ابحث عن منتج..." x-model="productSearch" id="product-search-input"
                                autocomplete="off" @input.debounce.300ms="fetchProducts()"
                                @keydown.enter.prevent="handleBarcodeScan(productSearch)">

                        </div>
                    </div>
                </div>
            </div>

            <!-- CATEGORY PILLS (Scrollable) -->
            <div class="px-3 py-3 d-flex gap-2 align-items-center w-100 border-bottom border-slate-800 bg-slate-950/50"
                style="overflow-x: auto; white-space: nowrap; min-height: 65px; scrollbar-width: thin;">
                <button class="category-pill active fw-bold" @click="filterCategory(null)">{{ __('All') }}</button>
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
                                <!-- Image/Placeholder -->
                                <template x-if="product.image">
                                    <img :src="product.image" class="twinx-img">
                                </template>
                                <template x-if="!product.image">
                                    <div class="twinx-img d-flex align-items-center justify-content-center"
                                        style="background-color: var(--bg-secondary, var(--input-bg)); color: var(--text-secondary);">
                                        <span class="fs-1 fw-bold text-uppercase"
                                            x-text="product.name.substring(0,2)"></span>
                                    </div>
                                </template>

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
                                <h6 class="fw-bold text-sm leading-snug line-clamp-2 min-h-[2.5em] mb-1 group-hover:text-emerald-300 transition-colors"
                                    style="color: var(--text-heading);" x-text="product.name"></h6>

                                <div class="mb-2">
                                    <span class="text-[10px] font-mono tracking-widest px-1.5 py-0.5 rounded"
                                        style="background-color: var(--input-bg); color: var(--text-muted); border: 1px solid var(--border-subtle);"
                                        x-text="product.sku || '---'"></span>
                                </div>

                                <div class="mt-auto d-flex justify-content-center pt-2 border-top border-slate-700/50">
                                    <div class="d-flex align-items-baseline gap-1">
                                        <span class="fw-bold text-xl leading-none tracking-tight"
                                            style="color: var(--text-heading);" x-text="formatNumber(product.price)"></span>
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

        <!-- Hidden Iframe for Auto-Printing -->
        <iframe id="receipt-frame"
            style="position: absolute; top: 0; left: 0; width: 0px; height: 0px; border: 0; visibility: hidden;"></iframe>


        <!-- ==================== RIGHT: QUICK ACCESS & KEYBOARD (Fixed Width) ==================== -->
        <aside
            class="d-none d-xxl-flex flex-column bg-slate-950 border-start border-slate-800 p-3 gap-3 overflow-y-auto custom-scrollbar"
            style="width: 280px; min-width: 280px; max-height: 100vh;">
            <!-- CLOCK & INFO -->
            <div class="bg-slate-900 rounded p-3 border border-slate-800 text-center shadow-sm">
                <div class="text-white fs-4 fw-black font-mono tracking-widest" x-text="currentTime">00:00:00</div>
                <div class="text-slate-400 fs-xs text-uppercase mt-1">{{ now()->format('l, d F Y') }}</div>
            </div>

            <h6 class="text-slate-500 fw-bold fs-xs text-uppercase tracking-wider mt-2 mb-1 px-1">{{ __('Operations') }}
            </h6>

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
                        <span class="fw-bold">{{ __('Delivery Management') }}</span>
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
            <h6 class="text-slate-500 fw-bold fs-xs text-uppercase tracking-wider mt-3 mb-1 px-1">{{ __('Reports') }}</h6>
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
                            <label class="form-label text-slate-400 small fw-bold">{{ __('Amount') }}</label>
                            <input type="text" inputmode="decimal" x-model="expenseAmount"
                                @input="expenseAmount = expenseAmount.replace(/[^0-9.]/g, '')"
                                class="form-control form-control-lg bg-slate-800 border-slate-700 text-white text-center font-mono fw-bold fs-4"
                                placeholder="0.00">
                            Suggest
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
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Actions') }}</th>
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
                                                    @click="window.open('{{ url('/pos/receipt') }}/' + inv.id, '_blank')">
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
                                        <th class="p-2">{{ __('Item') }}</th>
                                        <th class="p-2 text-center">{{ __('Quantity') }}</th>
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
                            <span x-show="!isProcessingOverride">{{ __('Save Changes') }}</span>
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
                            <label class="form-label text-slate-400 small fw-bold">{{ __('Customer Name') }}</label>
                            <input type="text" x-model="newCustomer.name"
                                class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                placeholder="مثلاً: شركة التميز">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-slate-400 small fw-bold">رقم الموبايل *</label>
                                <input type="text" x-model="newCustomer.mobile"
                                    class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                    placeholder="01xxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-400 small fw-bold">{{ __('Email') }}</label>
                                <input type="email" x-model="newCustomer.email"
                                    class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                    placeholder="client@example.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-slate-400 small fw-bold">{{ __('Address') }}</label>
                                <textarea x-model="newCustomer.address" rows="2"
                                    class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                    placeholder="تفاصيل العنوان..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-400 small fw-bold">{{ __('Tax Number') }}</label>
                                <input type="text" x-model="newCustomer.tax_number"
                                    class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                    placeholder="اختياري">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-400 small fw-bold">الحد الائتماني</label>
                                <input type="number" x-model="newCustomer.credit_limit"
                                    class="form-slate-control w-100 p-2 rounded bg-slate-800 text-white border border-slate-700"
                                    placeholder="0 = مفتوح">
                            </div>
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
                        <h5 class="modal-title"><i class="bi bi-truck-flatbed me-2"></i>{{ __('Delivery Management') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead>
                                    <tr class="text-slate-400 border-bottom border-slate-800">
                                        <th class="p-3">{{ __('Invoice Number') }}</th>
                                        <th class="p-3">{{ __('Customer') }}</th>
                                        <th class="p-3">{{ __('Address') }}</th>
                                        <th class="p-3">السائق</th>
                                        <th class="p-3">{{ __('Status') }}</th>
                                        <th class="p-3">{{ __('Total') }}</th>
                                        <th class="p-3 text-end">{{ __('Actions') }}</th>
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
                                class="bi bi-arrow-clockwise me-2"></i>{{ __('Update') }}</button>
                        <button class="btn btn-slate-600" data-bs-dismiss="modal">{{ __('Close') }}</button>
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
                                <h6 class="text-slate-400 small fw-bold mb-3 text-uppercase ls-1">{{ __('Expenses') }}</h6>
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
                        <button class="btn btn-slate-700 px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        /* ... Existing CSS + New Fixes ... */
        :root {
            --slate-900: var(--body-bg);
            --slate-950: var(--body-bg);
            --slate-800: var(--card-bg);
            --slate-700: var(--border-color);
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
            color: var(--success-text) !important;
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
            box-shadow: 0 0 0 3px var(--focus-ring-color) !important;
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
            background: var(--card-bg);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 8px 20px;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .category-pill:hover {
            background: var(--slate-700);
            color: var(--text-primary);
            transform: translateY(-1px);
        }

        .category-pill.active {
            background: var(--emerald-600);
            color: var(--text-primary);
            border-color: var(--emerald-500);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* --- TWINX MOTION CARD CSS (Manual Implementation) --- */
        .twinx-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            /* rounded-2xl */
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* LIGHT MODE SPECIFIC OVERRIDES */
        [data-theme="light"] .twinx-card {
            background-color: #ffffff;
            border-color: #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .twinx-body h6 {
            color: #1e293b !important;
        }

        [data-theme="light"] .twinx-body span.fw-bold {
            color: #0f172a !important;
        }

        [data-theme="light"] .twinx-overlay {
            background: linear-gradient(to top, #ffffff, transparent);
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
            background-color: var(--input-bg);
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
            background: linear-gradient(to top, var(--card-bg), transparent);
            pointer-events: none;
        }


        .twinx-pill {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--glass-bg);
            backdrop-filter: blur(4px);
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid var(--btn-glass-border);
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
            color: var(--text-primary);
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
            color: var(--text-secondary);
            transition: 0.2s;
            border-radius: 12px;
        }

        .btn-slate-action:hover {
            background: var(--slate-800);
            color: var(--text-primary);
            border-color: var(--slate-600);
            transform: translateX(-3px);
        }

        .modal-content.slate-modal {
            background-color: var(--slate-900);
            border: 1px solid var(--slate-700);
            color: var(--text-primary);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .btn-close-white {
            filter: invert(1);
        }

        .shadow-emerald-glow {
            box-shadow: 0 0 20px var(--focus-ring-color);
        }
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

    <script>    function posStore() {

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
                pos_print_receipt: {{ \Modules\Core\Models\Setting::getValue('pos_print_receipt', 'pos') ? 'true' : 'false' }},
                quotationPrices: {}, // Stores product_id -> unit_price mapping

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

                    // Fetch Quotation Prices
                    this.fetchQuotationPrices();
                },

                clearSelectedCustomer() {
                    this.selectedCustomer = null;
                    this.quotationPrices = {};
                    this.applyQuotationToCart(); // Revert to original prices
                    this.playBeep();
                },

                async fetchQuotationPrices() {
                    if (!this.selectedCustomer) {
                        this.quotationPrices = {};
                        return;
                    }

                    try {
                        const url = `{{ url('/pos/customers') }}/${this.selectedCustomer.id}/quotation-prices`;
                        const res = await axios.get(url);

                        // Convert array to object for O(1) lookup
                        const prices = {};
                        res.data.forEach(p => {
                            prices[p.product_id] = parseFloat(p.unit_price);
                        });
                        this.quotationPrices = prices;

                        // Apply prices to existing cart items
                        this.applyQuotationToCart();

                    } catch (e) {
                        console.error('Error fetching quotation prices:', e);
                    }
                },

                applyQuotationToCart() {
                    this.cart.forEach(item => {
                        if (this.quotationPrices[item.id]) {
                            item.price = this.quotationPrices[item.id];
                            const calc = this.calculateLine(item.qty, item.price, item.discount || 0, item.tax_rate);
                            Object.assign(item, calc);
                        } else {
                            // Revert to original price if no quotation exists
                            item.price = item.original_price;
                            const calc = this.calculateLine(item.qty, item.price, item.discount || 0, item.tax_rate);
                            Object.assign(item, calc);
                        }
                    });
                },

                // Payment State
                paymentModal: null,
                addCustomerModal: null,
                shiftModal: null,

                // New Customer State
                newCustomer: {
                    name: '', mobile: '',
                    email: '',
                    address: '',
                    tax_number: '',
                    credit_limit: ''
                },

                // Held Sales State (C-04 DB Persistence)
                heldSales: [],
                heldSalesModal: null,
                heldCount: 0,
                xReportModal: null,
                xReportData: null,

                // Phase 15: Global Scanner State
                barcodeBuffer: '',
                lastBarcodeTime: 0,

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
                        Swal.fire({ icon: 'warning', title: '{{ __('Customer Required') }}', text: '{{ __('Please select a customer for credit payment') }}', background: 'var(--card-bg)', color: 'var(--text-primary)' });
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
                        // Block if any split payment is credit but no customer selected
                        const hasCreditPayment = this.payments.some(p => p.method === 'credit');
                        if (hasCreditPayment && !this.selectedCustomer) return false;
                        return this.remainingAmount <= 0.01;
                    }
                    // Simple mode
                    if (this.activePaymentTab === 'credit' && !this.selectedCustomer) return false;
                    return this.remainingAmount <= 0.01 || this.activePaymentTab === 'credit';
                },

                initPOS() {
                    // Disable global scanner (invoice search) on POS page
                    window.blockGlobalScanner = true;

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
                        const url = "{{ route('pos.products.search') }}";
                        const res = await axios.get(url, {
                            params: {
                                q: this.productSearch,
                                category_id: this.activeCategory,
                                warehouse_id: this.warehouseId
                            }
                        });

                        this.products = res.data;
                    } catch (e) {
                        console.error('Fetch error details:', e);
                        let msg = 'خطأ غير معروف';
                        if (e.response && e.response.data && e.response.data.message) {
                            msg = e.response.data.message;
                        } else if (e.message) {
                            msg = e.message;
                        }
                        Swal.fire({ icon: 'error', title: '{{ __('Error') }}', text: '{{ __('Failed to load products') }}: ' + msg, background: 'var(--card-bg)', color: 'var(--text-primary)' });
                    } finally {
                        this.isLoading = false;
                    }
                },

                addToCart(product) {
                    const existing = this.cart.find(i => i.id === product.id);
                    const currentQty = existing ? existing.qty : 0;

                    // Phase 3: Stock warning before adding
                    if (product.stock <= 0) {
                        Swal.fire({ icon: 'warning', title: '{{ __('Out of Stock') }}', text: '⚠️ {{ __('This product is out of stock') }}', background: 'var(--card-bg)', color: 'var(--text-primary)' });
                        return;
                    }
                    if (currentQty + 1 > product.stock) {
                        Swal.fire({ icon: 'warning', title: '{{ __('Limited Quantity') }}', text: `⚠️ {{ __('Available quantity for') }} (${product.name}) {{ __('is') }} ${product.stock} {{ __('only') }}`, background: 'var(--card-bg)', color: 'var(--text-primary)' });
                        return;
                    }

                    if (existing) {
                        const calc = this.calculateLine(existing.qty + 1, existing.price, existing.discount || 0, product.tax_rate);
                        Object.assign(existing, calc);
                    } else {
                        // Use quotation price if available
                        const unitPrice = this.quotationPrices[product.id] || Number(product.price);
                        const calc = this.calculateLine(1, unitPrice, 0, product.tax_rate);
                        this.cart.push({
                            ...product,
                            ...calc,
                            original_price: Number(product.price)
                        });
                    }
                    this.playBeep();
                    this.scrollToBottom();
                },

                async updateQty(index, delta) {
                    const item = this.cart[index];
                    if (item.qty + delta <= 0) {
                        const result = await window.confirmAction({
                            title: '{{ __('Remove from Cart') }}',
                            text: '{{ __('Are you sure you want to remove this item?') }}',
                            confirmText: '{{ __('Yes, remove') }}',
                            confirmColor: '#ef4444'
                        });
                        if (result.isConfirmed) this.cart.splice(index, 1);
                        return;
                    }
                    const calc = this.calculateLine(item.qty + delta, item.price, item.discount || 0, item.tax_rate);
                    Object.assign(item, calc);
                },

                // removeItem: See enhanced version with audit logging below
                // holdSale: See full DB-persistence version below

                async clearCart() {
                    const result = await window.confirmAction({
                        title: '{{ __('Cancel Order') }}',
                        text: '{{ __('All items will be removed. Are you sure?') }}',
                        confirmText: '{{ __('Yes, clear all') }}',
                        confirmColor: '#ef4444'
                    });
                    if (result.isConfirmed) this.cart = [];
                },

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
                        recipient_name: this.isDeliveryMode ? this.recipientName : null,
                        recipient_phone: this.isDeliveryMode ? this.recipientPhone : null,
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

                            // Smart Printing Dispatcher (Phase 39 Enhanced)
                            if (this.pos_print_receipt) {
                                console.log('Checkout complete. Triggering print for invoice:', res.data.invoice.id);
                                POS_Printer.dispatch(res.data.invoice.id);
                            }

                            // Show success toast (Non-blocking)
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                background: '#1e293b',
                                color: '#fff'
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'تمت العملية بنجاح',
                                text: 'فاتورة #' + res.data.invoice.number
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('Operation Failed') }}',
                            text: (e.response?.data?.message || e.message),
                            background: 'var(--card-bg)',
                            color: 'var(--text-primary)',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: '{{ __('OK') }}'
                        });
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
                        const res = await axios.get('{{ route("pos.customers.search") }}', {
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
                        const res = await axios.get('{{ route("pos.customers.search") }}', {
                            params: { q: this.searchQuery }
                        });
                        this.customerResults = res.data;
                    } catch (e) {
                        console.error('Customer search failed', e);
                    } finally {
                        this.isSearchingCustomer = false;
                    }
                },



                // clearSelectedCustomer: Defined above with quotation price revert logic

                openAddCustomerModal() { this.addCustomerModal.show(); },

                async quickCreateCustomer() {
                    try {
                        const res = await axios.post('{{ route("pos.customers.quick-create") }}', this.newCustomer);
                        if (res.data.success) {
                            this.selectCustomer(res.data.customer);
                            this.addCustomerModal.hide();
                            this.newCustomer = {
                                name: '',
                                mobile: '',
                                email: '',
                                address: '',
                                tax_number: '',
                                credit_limit: ''
                            };
                        }
                    } catch (e) { Swal.fire({ icon: 'error', title: 'خطأ', text: (e.response?.data?.message || e.message || 'فشل إضافة العميل'), background: '#1e293b', color: '#fff' }); }
                },

                // --- HELD SALES LOGIC (C-04 DB Persistence) ---
                async holdSale() {
                    if (this.cart.length === 0) {
                        Swal.fire({ icon: 'info', title: 'السلة فارغة', text: 'الرجاء إضافة أصناف قبل إتمام العملية', background: '#1e293b', color: '#fff' });
                        return;
                    }
                    try {
                        const res = await axios.post('{{ route("pos.hold") }}', {
                            items: this.cart,
                            customer_id: this.selectedCustomer ? this.selectedCustomer.id : null,
                            warehouse_id: this.warehouseId,
                            subtotal: this.cartSubtotal,
                            tax: this.cartTax,
                            total: this.cartTotal,
                            notes: ''
                        });
                        if (res.data.success) {
                            Swal.fire({ icon: 'success', title: 'تم تعليق الفاتورة', text: 'رقم الطلب المعلق: ' + res.data.held_sale.hold_number, background: '#1e293b', color: '#fff' });
                            this.cart = [];
                            this.fetchHeldSales();
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'فشل التعليق', text: (e.response?.data?.message || e.message), background: '#1e293b', color: '#fff' });
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
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الاستئناف',
                                text: 'تم استئناف الفاتورة بنجاح',
                                timer: 1500,
                                showConfirmButton: false,
                                background: '#1e293b',
                                color: '#fff'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل الاستئناف',
                            text: (e.response?.data?.error || e.message),
                            background: '#1e293b',
                            color: '#fff'
                        });
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

                            Swal.fire({
                                icon: 'success',
                                title: 'تم فتح الوردية',
                                text: 'نتمنى لك عملاً موفقاً!',
                                timer: 2000,
                                showConfirmButton: false,
                                background: '#1e293b',
                                color: '#fff'
                            });

                            this.shiftModal.hide();
                            setTimeout(() => location.reload(), 1500);
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في فتح الوردية',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'موافق'
                        });
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async closeShift() {
                    if (this.closingCash < 0) {
                        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'رقم غير صحيح للنقدية', background: '#1e293b', color: '#fff' });
                        return;
                    }
                    if (!this.shiftPin) {
                        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'الرجاء إدخال رمز الإغلاق (PIN)', background: '#1e293b', color: '#fff' });
                        return;
                    }

                    const result = await window.confirmAction({
                        title: 'إغلاق الوردية',
                        text: 'تأكيد إغلاق الوردية؟ سيتم ترحيل الفروقات إن وجدت.',
                        confirmText: 'نعم، أغلق الوردية',
                        confirmColor: '#ef4444'
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const res = await axios.post('{{ route("pos.shift.close") }}', {
                            closing_cash: this.closingCash,
                            pin: this.shiftPin,
                            notes: ''
                        });

                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم إغلاق الوردية بنجاح',
                                showConfirmButton: false,
                                timer: 2000,
                                background: '#1e293b',
                                color: '#fff'
                            });

                            setTimeout(() => {
                                if (res.data.report_url) {
                                    window.location.href = res.data.report_url;
                                } else {
                                    window.location.href = '{{ route("dashboard") }}';
                                }
                            }, 1500);
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في إغلاق الوردية',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'موافق'
                        });
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
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل تحميل التقرير',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'موافق'
                        });
                        if (this.xReportModal) this.xReportModal.hide();
                    }
                },

                async showLastTransactions() {
                    try {
                        const res = await axios.get('{{ route("pos.lastTransactions") }}');
                        if (res.data.success) {
                            const txs = res.data.data;
                            if (txs.length === 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'لا توجد معاملات',
                                    text: 'لم يتم العثور على أي معاملات سابقة في هذه الوردية.',
                                    background: '#1e293b',
                                    color: '#fff',
                                    confirmButtonColor: '#3b82f6',
                                    confirmButtonText: 'موافق'
                                });
                                return;
                            }
                            let list = "═══════ آخر المعاملات ═══════\n\n";
                            txs.forEach(t => {
                                list += `• ${t.created_at} | #${t.invoice_number} | ${t.customer_name}\n`;
                                list += `  المبلغ: ${this.formatNumber(t.total)} EGP (${t.payment_method})\n\n`;
                            });
                            Swal.fire({
                                title: 'آخر المعاملات',
                                html: '<pre style="text-align: right; font-family: monospace; white-space: pre-wrap;">' + list + '</pre>',
                                background: '#1e293b',
                                color: '#fff',
                                confirmButtonText: 'إغلاق',
                                confirmButtonColor: '#3b82f6'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في التحميل',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff'
                        });
                    }
                },

                async openCashDrawer() {
                    const reason = prompt('سبب فتح الدرج (اختياري):');
                    try {
                        const res = await axios.post('{{ route("pos.drawer.open") }}', { reason });
                        if (res.data.success) {
                            // Swal.fire({ icon: 'success', title: 'تم الحفظ', text: res.data.message, timer: 1000, showConfirmButton: false, background: '#1e293b', color: '#fff' });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في الدرج',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff',
                            confirmButtonText: 'موافق',
                            confirmButtonColor: '#3b82f6'
                        });
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
                        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'مطلوب رمز المدير للقيام بهذه العملية', background: '#1e293b', color: '#fff' });
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
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في تغيير السعر',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'موافق'
                        });
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
                },

                // Keys
                handleGlobalKeys(e) {
                    // Ignore shortcuts if the user is typing in an input, textarea or select
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

                    // Standard Shortcuts
                    if (e.key === 'F2') { e.preventDefault(); document.getElementById('product-search-input').focus(); return; }
                    if (e.key === 'F10') { e.preventDefault(); this.showPaymentModal(); return; }
                    if (e.key === 'F8') { e.preventDefault(); this.printLastInvoice(); return; }

                    // --- GLOBAL BARCODE SCANNER LOGIC (Phase 15) ---
                    const char = e.key;
                    const now = Date.now();

                    // If time between keys is too long (> 50ms), reset buffer (likely manual typing or new scan)
                    // Scanners typically send chars with < 20-30ms delay
                    if (now - this.lastBarcodeTime > 100) {
                        this.barcodeBuffer = '';
                    }
                    this.lastBarcodeTime = now;

                    if (char === 'Enter') {
                        if (this.barcodeBuffer.length >= 3) {
                            e.preventDefault();
                            this.handleBarcodeScan(this.barcodeBuffer);
                            this.barcodeBuffer = '';
                        }
                    } else if (char.length === 1) {
                        // Only buffer printable characters
                        this.barcodeBuffer += char;
                    }
                },

                // Phase 15: Handle Scanned Code
                async handleBarcodeScan(code) {
                    console.log('Scanner Input:', code);

                    // 1. Set search and show loading
                    this.productSearch = code;
                    this.isSearching = true;

                    try {
                        // 2. Fetch products with exact match priority
                        const res = await axios.get('{{ route("pos.products.search") }}', {
                            params: { q: code, exact: 1 }
                        });

                        const products = res.data;

                        if (products.length === 1) {
                            // 3. Perfect Match -> Auto Add
                            const product = products[0];
                            this.addToCart(product);

                            // Visual feedback
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'تمت الإضافة: ' + product.name,
                                showConfirmButton: false,
                                timer: 1500,
                                background: '#1e293b',
                                color: '#fff'
                            });

                            // Clear search and reload default product grid
                            this.productSearch = '';
                            this.fetchProducts();
                        } else if (products.length > 1) {
                            // Multiple matches -> Show them
                            this.products = products;
                            this.playBeep();
                        } else {
                            // No match -> Show not found AND clear buffer to avoid stuck state
                            this.playBeep();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'المنتج غير موجود',
                                showConfirmButton: false,
                                timer: 1500,
                                background: '#1e293b',
                                color: '#fff'
                            });
                            this.productSearch = '';
                        }
                    } catch (e) {
                        console.error('Scan Error', e);
                    } finally {
                        this.isLoading = false;
                        this.isSearching = false;
                    }
                },

                // ... Rest of methods


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
                    if (this.expenseModal) {
                        this.expenseModal.show();
                        // Auto-focus the amount field
                        setTimeout(() => {
                            const input = document.querySelector('#expenseModal input');
                            if (input) input.focus();
                        }, 500);
                    }
                },

                async saveExpense() {
                    this.isProcessing = true;
                    try {
                        const res = await axios.post('{{ route("pos.expenses.store") }}', {
                            amount: this.expenseAmount,
                            notes: this.expenseNotes
                        });

                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم تسجيل المصروف',
                                text: 'رصيد الدرج المتوقع: ' + this.formatMoney(res.data.balance),
                                background: '#1e293b',
                                color: '#fff',
                                confirmButtonColor: '#10b981'
                            });
                            this.expenseModal.hide();
                            this.expenseAmount = '';
                            this.expenseNotes = '';
                        }
                    } catch (e) {
                        console.error(e);
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: '❌ فشل تسجيل المصروف: ' + (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff'
                        });
                    } finally {
                        this.isProcessing = false;
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
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'فشل تحميل العمليات الأخيرة',
                            background: '#1e293b',
                            color: '#fff'
                        });
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
                        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'الرجاء تحديد كميات للاسترجاع', background: '#1e293b', color: '#fff' });
                        return;
                    }
                    if (!this.returnPin) {
                        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'الرجاء إدخال رمز الإغلاق (PIN)', background: '#1e293b', color: '#fff' });
                        return;
                    }

                    const returnResult = await window.confirmAction({
                        title: 'تأكيد المرتجع',
                        text: 'هل أنت متأكد من إتمام عملية المرتجع؟',
                        confirmText: 'نعم، أتم المرتجع',
                        confirmColor: '#ef4444'
                    });
                    if (!returnResult.isConfirmed) return;

                    this.isProcessing = true;
                    try {
                        const res = await axios.post('{{ route("pos.return") }}', {
                            invoice_id: this.returnInvoice.id,
                            items: itemsToReturn,
                            reason: this.returnReason || 'POS Return',
                            pin: this.returnPin
                        });

                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم إرجاع الأصناف',
                                text: 'رقم المرتجع: ' + (res.data.return?.number || ''),
                                background: '#1e293b',
                                color: '#fff',
                                confirmButtonColor: '#10b981'
                            });
                            this.returnModal.hide();
                            this.openRecentTransactions();
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل المرتجع',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff'
                        });
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

                // --- PHASE 3: Print Last Invoice (F8 / Manual) ---
                async printLastInvoice() {
                    try {
                        const res = await axios.get('{{ route("pos.lastTransactions") }}?limit=1');
                        const transactions = res.data.data || res.data;
                        if (transactions && transactions.length > 0) {
                            const lastInvoice = transactions[0];
                            const receiptUrl = '{{ url("/pos/receipt") }}/' + lastInvoice.id;

                            // Use Smart Dispatcher for Reprint
                            POS_Printer.dispatch(lastInvoice.id);
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'تنبيه',
                                text: 'لا توجد فواتير سابقة للطباعة.',
                                background: '#1e293b',
                                color: '#fff'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في الطباعة',
                            text: 'فشل جلب آخر فاتورة من النظام.',
                            background: '#1e293b',
                            color: '#fff'
                        });
                    }
                },

                // --- PHASE 3: Cart Discount --- (initialized above)
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
                    const result = await window.confirmAction({
                        title: 'تغيير حالة الطلب',
                        text: 'تغيير حالة الطلب إلى ' + status + '؟',
                        confirmText: 'نعم، قم بالتغيير',
                        confirmColor: '#3b82f6'
                    });
                    if (!result.isConfirmed) return;

                    try {
                        const res = await axios.post('{{ route("pos.delivery.status") }}', { id, status });
                        if (res.data.success) {
                            // alert('تم تحديث الحالة');
                            this.fetchDeliveryOrders();
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في التحديث',
                            text: (e.response?.data?.message || e.message),
                            background: '#1e293b',
                            color: '#fff'
                        });
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

        /**
         * Smart POS Printer Dispatcher
         * Automatically detects environment:
         * - EXE (Electron): Silent Print
         * - Browser: Standard Print Dialog
         */
        const POS_Printer = {
            /**
             * Dispatch a print job
             * @param {number|string} invoiceId
             */
            dispatch: function (invoiceId) {
                console.log('POS_Printer: Dispatching job for Invoice #' + invoiceId);

                const receiptUrl = '{{ url("pos/receipt") }}/' + invoiceId;

                // 1. Detect EXE / Electron Environment
                const isEXE = window.ipcRenderer || (window.process && window.process.type);

                if (isEXE) {
                    this.printViaEXE(receiptUrl, invoiceId);
                    return;
                }

                // 2. Browser Mode: Use hidden iframe (more reliable than window.open in async flows)
                this.triggerIframePrint(receiptUrl, 'receipt-frame');
            },

            /**
             * Trigger printing via a hidden iframe
             */
            triggerIframePrint: function (url, iframeId) {
                let iframe = document.getElementById(iframeId);
                if (!iframe) {
                    iframe = document.createElement('iframe');
                    iframe.id = iframeId;
                    iframe.style.display = 'none';
                    document.body.appendChild(iframe);
                }
                console.log('POS_Printer: Loading iframe:', url);
                iframe.src = url;
            },

            /**
             * Native EXE Bridge (Silent)
             */
            printViaEXE: function (url, invoiceId) {
                console.log('POS_Printer: Triggering Native Silent Print for EXE.');

                if (window.ipcRenderer) {
                    window.ipcRenderer.send('print-silent', {
                        url: url,
                        invoiceId: invoiceId
                    });
                } else if (typeof window.NativePrint === 'function') {
                    window.NativePrint(url);
                } else {
                    console.warn('POS_Printer: EXE detected but no native bridge found. Falling back to window.');
                    window.open(url, '_blank');
                }
            }
        };
    </script>
@endsection