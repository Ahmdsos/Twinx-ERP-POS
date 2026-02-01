<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Twinx POS v2</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Roboto+Mono:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (CDN for immediate reliability) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                        mono: ['Roboto Mono', 'monospace'],
                    },
                    colors: {
                        primary: '#10b981', // Emerald 500
                        'primary-hover': '#059669', // Emerald 600
                        secondary: '#3b82f6', // Blue 500
                        danger: '#ef4444', // Red 500
                        warning: '#f59e0b', // Amber 500
                        dark: '#0f172a', // Slate 900
                        'dark-card': '#1e293b', // Slate 800
                        'dark-hover': '#334155', // Slate 700
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }
            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .touch-target {
                min-width: 44px;
                min-height: 44px;
            }
        }
        .product-card-hover:active {
            transform: scale(0.95);
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>

<body class="bg-dark text-white h-screen w-screen overflow-hidden selection:bg-primary selection:text-white"
    x-data="posSystem()">

    <!-- Main Container -->
    <div class="flex h-full">

        <!-- SIDEBAR (Navigation & Categories) -->
        <aside
            class="w-20 lg:w-24 flex-none border-l border-slate-700 bg-dark-card flex flex-col items-center py-4 z-20 shadow-xl">
            <!-- Logo -->
            <div class="mb-6 bg-primary text-white p-2 rounded-xl shadow-lg shadow-primary/20">
                <i class="bi bi-box-seam text-2xl"></i>
            </div>

            <!-- Nav Items -->
            <nav class="flex-1 w-full space-y-2 px-2 flex flex-col items-center overflow-y-auto no-scrollbar">

                <button @click="currentCategory = 'all'"
                    :class="currentCategory === 'all' ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex flex-col items-center justify-center transition-all duration-200 group">
                    <i class="bi bi-grid text-xl mb-1"></i>
                    <span class="text-[9px] lg:text-[10px]">الكل</span>
                </button>

                @foreach($categories as $category)
                    <button @click="currentCategory = {{ $category->id }}"
                        :class="currentCategory === {{ $category->id }} ? 'bg-secondary text-white shadow-lg shadow-secondary/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex flex-col items-center justify-center transition-all duration-200 group relative">
                        <i class="bi bi-tag text-xl mb-1"></i>
                        <span
                            class="text-[9px] lg:text-[10px] text-center leading-tight px-1 truncate w-full">{{ Str::limit($category->name, 10) }}</span>
                        <!-- Active Indicator -->
                        <div x-show="currentCategory === {{ $category->id }}"
                            class="absolute -left-2 w-1 h-8 bg-white rounded-r-full"></div>
                    </button>
                @endforeach
            </nav>

            <!-- Bottom Actions -->
            <div class="mt-auto space-y-3 pb-2 flex flex-col items-center">
                <button @click="toggleReturnMode()"
                    :class="isReturnMode ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-800 text-red-500 hover:bg-slate-700'"
                    class="w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-lg"
                    title="وضع المرتجع (Return Mode)">
                    <i class="bi bi-arrow-counterclockwise text-lg"></i>
                </button>
                <div x-show="isReturnMode" class="text-[8px] text-red-500 font-bold">مرتجع</div>

                <button @click="fetchAnalytics()"
                    class="w-10 h-10 rounded-full bg-slate-800 text-secondary hover:bg-secondary hover:text-white flex items-center justify-center transition-all shadow-lg"
                    title="الإحصائيات (Analytics)">
                    <i class="bi bi-graph-up text-lg"></i>
                </button>

                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit"
                        class="w-10 h-10 rounded-full bg-slate-800 hover:bg-danger hover:text-white text-danger flex items-center justify-center transition-colors shadow-lg"
                        title="تسجيل خروج">
                        <i class="bi bi-power text-lg"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN AREA -->
        <main class="flex-1 flex flex-col min-w-0 bg-dark relative transition-all duration-300"
            :class="isReturnMode ? 'border-4 border-red-500/50 shadow-[inset_0_0_50px_rgba(239,68,68,0.2)]' : ''">

            <!-- Return Mode Banner -->
            <div x-show="isReturnMode" class="bg-red-500 text-white text-center text-xs font-bold py-1 shadow-lg z-20">
                ⚠️ وضع المرتجع مفعل - المنتجات المضافة ستكون بالسالب (Refund)
            </div>

            <!-- HEADER -->
            <header
                class="h-16 lg:h-20 bg-dark-card/80 backdrop-blur-md border-b border-slate-700 flex items-center justify-between px-6 sticky top-0 z-10">

                <!-- Search -->
                <div class="flex-1 max-w-xl relative">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <i class="bi bi-search text-slate-400 text-lg"></i>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="بحث عن منتج (اسم، باركود)..."
                        class="w-full h-12 pr-12 pl-4 bg-slate-800 border-2 border-transparent focus:border-primary focus:bg-slate-900 rounded-2xl text-white placeholder-slate-500 transition-all outline-none shadow-inner"
                        @keydown.escape="searchQuery = ''">

                    <!-- Network Status -->
                    <div class="absolute inset-y-0 left-2 flex items-center gap-2">
                        <div x-show="!isOffline"
                            class="flex items-center gap-1 bg-slate-700 px-2 py-1 rounded text-xs text-green-400"
                            title="متصل">
                            <i class="bi bi-wifi"></i>
                        </div>
                        <div x-show="isOffline"
                            class="flex items-center gap-1 bg-red-500/20 px-2 py-1 rounded text-xs text-red-500 animate-pulse"
                            title="غير متصل">
                            <i class="bi bi-wifi-off"></i>
                            <span>Offline</span>
                        </div>
                        <div x-show="pendingSync.length > 0"
                            class="flex items-center gap-1 bg-warning/20 px-2 py-1 rounded text-xs text-warning"
                            title="جاري المزامنة">
                            <i class="bi bi-cloud-upload"></i>
                            <span x-text="pendingSync.length"></span>
                        </div>
                    </div>
                </div>

                <!-- Shift Info -->
                <div class="flex items-center gap-4 lg:gap-6">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs text-slate-400 mb-1">الوردية الحالية</div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="font-bold text-sm">{{ $activeShift->user->name ?? 'Guest' }}</span>
                        </div>
                    </div>

                    <div class="h-10 w-[1px] bg-slate-700 hidden sm:block"></div>

                    <!-- Time -->
                    <div class="text-center w-24" x-data="{ time: new Date() }"
                        x-init="setInterval(() => time = new Date(), 1000)">
                        <div class="font-mono text-xl font-bold tracking-wider"
                            x-text="time.toLocaleTimeString('en-US', {hour12: false, hour: '2-digit', minute: '2-digit'})">
                            00:00</div>
                        <div class="text-[10px] text-slate-400" x-text="time.toLocaleDateString('ar-EG')"></div>
                    </div>
                </div>
            </header>

            <!-- PRODUCTS GRID -->
            <div class="flex-1 p-4 lg:p-6 overflow-y-auto no-scrollbar scroll-smooth">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4">
                    <!-- Product Card Template -->
                    <template x-for="(product, index) in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)" x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                            class="group relative bg-dark-card border border-slate-700 hover:border-primary/50 hover:shadow-lg hover:shadow-primary/10 rounded-2xl p-3 cursor-pointer transition-all duration-200 product-card-hover select-none flex flex-col h-[180px] lg:h-[220px]">

                            <!-- Stock Badge -->
                            <div class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold z-10"
                                :class="product.stock_qty > 0 ? 'bg-slate-800 text-green-400' : 'bg-red-500/20 text-red-400'">
                                <span x-text="product.stock_qty > 0 ? product.stock_qty : 'نفذ'"></span>
                            </div>

                            <!-- Image/Icon -->
                            <div
                                class="flex-1 flex items-center justify-center mb-3 bg-slate-800/50 rounded-xl group-hover:bg-slate-800 transition-colors relative overflow-hidden">
                                <i
                                    class="bi bi-box-seam text-4xl text-slate-600 group-hover:text-primary transition-colors"></i>
                                <!-- Tap Ripple Effect Area -->
                            </div>

                            <!-- Info -->
                            <div class="space-y-1">
                                <h3 class="font-bold text-sm lg:text-base leading-tight line-clamp-2 h-10 group-hover:text-primary transition-colors"
                                    x-text="product.name"></h3>
                                <div class="flex justify-between items-end">
                                    <div class="font-mono font-bold text-lg text-secondary"
                                        x-text="formatMoney(product.selling_price)"></div>
                                    <div class="text-xs text-slate-500 mb-1" x-text="product.unit"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="filteredProducts.length === 0"
                    class="h-full flex flex-col items-center justify-center text-slate-500 opacity-60"
                    x-transition.opacity>
                    <i class="bi bi-search text-6xl mb-4 animate-bounce"></i>
                    <p class="text-xl">لا توجد منتجات مطابقة</p>
                </div>
            </div>
        </main>

        <!-- CART SIDEBAR -->
        <aside class="w-96 flex-none bg-dark-card border-r border-slate-700 flex flex-col shadow-2xl z-30 relative">

            <!-- Customer & Config Header -->
            <div class="p-4 border-b border-slate-700 bg-slate-800/50 space-y-3">
                <!-- Customer Select -->
                <div class="relative">
                    <label
                        class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1 block">العميل</label>
                    <div class="flex gap-2">
                        <select x-model="selectedCustomer"
                            class="flex-1 bg-dark border border-slate-600 rounded-lg h-9 px-2 text-sm focus:border-primary outline-none text-white appearance-none">
                            <option value="">عميل عادي (Walk-in)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <button
                            class="w-9 h-9 bg-slate-700 rounded-lg hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                            <i class="bi bi-person-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Warehouse & Account -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label
                            class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1 block">المخزن</label>
                        <select x-model="selectedWarehouse"
                            class="w-full bg-dark border border-slate-600 rounded-lg h-9 px-2 text-xs focus:border-primary outline-none text-white">
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1 block">حساب
                            الدفع</label>
                        <select x-model="selectedPaymentAccount"
                            class="w-full bg-dark border border-slate-600 rounded-lg h-9 px-2 text-xs focus:border-primary outline-none text-white">
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-2 no-scrollbar space-y-2" id="cart-items-container">
                <template x-for="(item, index) in cart" :key="item.id + (isReturnMode ? '_ret' : '')">
                    <div x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-4"
                        class="bg-dark rounded-xl p-3 border border-slate-700 group hover:border-slate-500 transition-all relative overflow-hidden"
                        :class="item.qty < 0 ? 'border-red-500/30 bg-red-500/5' : ''">
                        <div class="flex justify-between items-start mb-2 relative z-10">
                            <div class="flex-1">
                                <h4 class="font-bold text-sm text-white" x-text="item.name"></h4>
                                <div class="text-xs text-slate-400 mt-0.5" x-text="item.sku || 'SKU-001'"></div>
                            </div>
                            <div class="font-mono font-bold text-white" x-text="formatMoney(item.price * item.qty)">
                            </div>
                        </div>

                        <div class="flex items-center justify-between relative z-10">
                            <!-- Qty Controls -->
                            <div class="flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-600">
                                <button @click="updateQty(index, -1)"
                                    class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700 rounded transition-colors touch-target">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" x-model="item.qty"
                                    class="w-12 text-center bg-transparent border-none text-white font-mono text-sm focus:ring-0 p-0"
                                    @change="if(item.qty < 1) item.qty = 1">
                                <button @click="updateQty(index, 1)"
                                    class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700 rounded transition-colors touch-target">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            <!-- Remove -->
                            <button @click="removeFromCart(index)"
                                class="w-8 h-8 rounded-lg text-slate-500 hover:bg-red-500/20 hover:text-red-500 flex items-center justify-center transition-colors">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0"
                    class="h-full flex flex-col items-center justify-center text-slate-600 opacity-50">
                    <i class="bi bi-cart-x text-6xl mb-4"></i>
                    <p>السلة فارغة</p>
                </div>
            </div>

            <!-- Footer / Totals -->
            <div class="bg-dark-card border-t border-slate-700 p-4 shadow-[0_-5px_20px_rgba(0,0,0,0.3)] z-20">
                <!-- Summary Rows -->
                <div class="space-y-2 mb-4 text-sm">
                    <div class="flex justify-between text-slate-400">
                        <span>المجموع الفرعي</span>
                        <span class="font-mono" x-text="formatMoney(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>الضريبة (14%)</span>
                        <span class="font-mono" x-text="formatMoney(tax)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>الخصم</span>
                        <div class="flex items-center gap-2">
                            <input type="number" x-model.number="discount"
                                class="w-16 bg-dark border border-slate-600 rounded px-1 text-right text-xs focus:border-secondary outline-none">
                            <span class="font-mono" x-text="formatMoney(discount)"></span>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="flex justify-between items-center mb-4 p-3 bg-slate-800 rounded-xl border border-slate-700">
                    <span class="font-bold text-lg text-white">الإجمالي</span>
                    <span class="font-mono text-2xl font-bold text-green-400" x-text="formatMoney(total)"></span>
                </div>

                <!-- Main Actions -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        class="col-span-1 bg-slate-700 hover:bg-slate-600 text-white rounded-xl py-4 flex flex-col items-center justify-center transition-colors shadow-lg active:scale-95 relative"
                        @click="holdOrder()" title="تعليق (F8)">
                        <span x-show="heldOrders.length > 0"
                            class="absolute top-2 right-2 w-5 h-5 bg-warning text-black rounded-full text-xs font-bold flex items-center justify-center"
                            x-text="heldOrders.length"></span>
                        <i class="bi bi-pause-circle text-xl mb-1 text-warning"></i>
                        <span class="text-[10px]">تعليق (F8)</span>
                    </button>

                    <button
                        class="col-span-3 bg-gradient-to-r from-primary to-primary-hover hover:from-primary-hover hover:to-primary text-white rounded-xl py-3 flex items-center justify-center gap-3 transition-transform shadow-lg shadow-primary/30 active:scale-95 text-lg font-bold"
                        @click="openPaymentModal()">
                        <i class="bi bi-credit-card-2-front"></i>
                        <span>دفع</span>
                        <span class="bg-black/20 px-2 py-0.5 rounded text-sm font-mono"
                            x-text="formatMoney(total)"></span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Hold/Recall Modal -->
        <div x-show="showHeldOrdersModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
            x-transition.opacity>
            <div class="bg-dark-card w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-700 p-6 max-h-[80vh] flex flex-col"
                @click.away="showHeldOrdersModal = false">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-pause-circle text-warning"></i>
                    <span>الطلبات المعلقة</span>
                </h3>

                <div class="flex-1 overflow-y-auto space-y-3 no-scrollbar">
                    <template x-for="(order, index) in heldOrders" :key="order.id">
                        <div
                            class="bg-slate-800 p-4 rounded-xl flex justify-between items-center border border-slate-700 hover:border-warning transition-colors">
                            <div>
                                <div class="font-bold text-white" x-text="order.customer"></div>
                                <div class="text-xs text-slate-400">
                                    <span x-text="order.date"></span> •
                                    <span x-text="order.items.length"></span> منتجات
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="font-mono font-bold text-lg text-primary" x-text="formatMoney(order.total)">
                                </div>
                                <button @click="recallOrder(index)"
                                    class="bg-primary hover:bg-primary-hover text-white px-3 py-1.5 rounded-lg text-sm">استعادة</button>
                                <button @click="deleteHeldOrder(index)"
                                    class="text-red-500 hover:bg-red-500/10 px-2 py-1.5 rounded-lg"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </template>
                    <div x-show="heldOrders.length === 0" class="text-center text-slate-500 py-10">
                        لا توجد طلبات معلقة
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcuts Modal -->
        <div x-show="showShortcutsModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
            x-transition.opacity>
            <div class="bg-dark-card w-full max-w-lg rounded-3xl shadow-2xl border border-slate-700 p-6"
                @click.away="showShortcutsModal = false">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="bi bi-keyboard text-primary"></i>
                    <span>اختصارات لوحة المفاتيح</span>
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-800 p-3 rounded-xl flex justify-between items-center">
                        <span class="text-slate-300">مساعدة</span>
                        <kbd
                            class="bg-slate-700 px-2 py-1 rounded text-white font-mono border-b-2 border-slate-900">F1</kbd>
                    </div>
                    <div class="bg-slate-800 p-3 rounded-xl flex justify-between items-center">
                        <span class="text-slate-300">بحث</span>
                        <kbd
                            class="bg-slate-700 px-2 py-1 rounded text-white font-mono border-b-2 border-slate-900">F2</kbd>
                    </div>
                    <div class="bg-slate-800 p-3 rounded-xl flex justify-between items-center">
                        <span class="text-slate-300">تعليق الطلب</span>
                        <kbd
                            class="bg-slate-700 px-2 py-1 rounded text-white font-mono border-b-2 border-slate-900">F8</kbd>
                    </div>
                    <div class="bg-slate-800 p-3 rounded-xl flex justify-between items-center">
                        <span class="text-slate-300">استعادة طلب</span>
                        <kbd
                            class="bg-slate-700 px-2 py-1 rounded text-white font-mono border-b-2 border-slate-900">F9</kbd>
                    </div>
                    <div class="bg-slate-800 p-3 rounded-xl flex justify-between items-center">
                        <span class="text-slate-300">إتمام الدفع</span>
                        <kbd
                            class="bg-slate-700 px-2 py-1 rounded text-white font-mono border-b-2 border-slate-900">F12</kbd>
                    </div>
                    <div class="bg-slate-800 p-3 rounded-xl flex justify-between items-center">
                        <span class="text-slate-300">إغلاق</span>
                        <kbd
                            class="bg-slate-700 px-2 py-1 rounded text-white font-mono border-b-2 border-slate-900">Esc</kbd>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="showPaymentModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-dark-card w-full max-w-4xl h-[90vh] lg:h-auto rounded-3xl shadow-2xl border border-slate-700 flex overflow-hidden lg:flex-row flex-col transform transition-all"
                @click.away="showPaymentModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-10"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-10">

                <!-- Left: Methods & Summary -->
                <div class="w-full lg:w-1/2 p-6 border-l border-slate-700 flex flex-col">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <i class="bi bi-wallet2 text-primary"></i>
                        <span>إتمام الدفع</span>
                    </h2>

                    <!-- Total Display -->
                    <div class="bg-slate-800 rounded-2xl p-6 mb-6 text-center border border-slate-700">
                        <div class="text-slate-400 text-sm mb-1">المبلغ المطلوب</div>
                        <div class="text-4xl font-mono font-bold text-white tracking-wider" x-text="formatMoney(total)">
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <button @click="setMethod('cash')"
                            :class="paymentMethod === 'cash' ? 'bg-primary shadow-primary/20 scale-95' : 'bg-slate-700 hover:bg-slate-600'"
                            class="p-4 rounded-xl text-white flex flex-col items-center gap-2 transition-all shadow-lg">
                            <i class="bi bi-cash-stack text-2xl"></i>
                            <span class="font-bold">نقدي (Cash)</span>
                        </button>
                        <button @click="setMethod('card')"
                            :class="paymentMethod === 'card' ? 'bg-secondary shadow-secondary/20 scale-95' : 'bg-slate-700 hover:bg-slate-600'"
                            class="p-4 rounded-xl text-white flex flex-col items-center gap-2 transition-all">
                            <i class="bi bi-credit-card text-2xl"></i>
                            <span>بطاقة (Card)</span>
                        </button>
                        <button @click="setMethod('credit')"
                            :class="paymentMethod === 'credit' ? 'bg-warning shadow-warning/20 scale-95' : 'bg-slate-700 hover:bg-slate-600'"
                            class="p-4 rounded-xl text-white flex flex-col items-center gap-2 transition-all">
                            <i class="bi bi-person-badge text-2xl"></i>
                            <span>آجل (Credit)</span>
                        </button>
                        <button @click="setMethod('split')"
                            :class="paymentMethod === 'split' ? 'bg-purple-600 shadow-purple-600/20 scale-95' : 'bg-slate-700 hover:bg-slate-600'"
                            class="p-4 rounded-xl text-white flex flex-col items-center gap-2 transition-all">
                            <i class="bi bi-layers-half text-2xl"></i>
                            <span>تقسيم (Split)</span>
                        </button>
                    </div>

                    <!-- Inputs -->
                    <div class="mt-auto space-y-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">المبلغ المدفوع</label>
                            <div class="relative">
                                <input type="number" x-model="paidAmount"
                                    class="w-full bg-dark border-2 border-slate-600 focus:border-primary rounded-xl h-14 px-4 text-2xl font-mono text-white outline-none"
                                    placeholder="0.00">
                                <button
                                    class="absolute left-2 top-2 bottom-2 px-4 bg-slate-700 hover:bg-slate-600 rounded-lg text-xs"
                                    @click="setExact()">
                                    Exact
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-between items-center bg-slate-800 p-4 rounded-xl">
                            <span class="text-slate-400">الباقي</span>
                            <span class="text-2xl font-mono font-bold"
                                :class="change < 0 ? 'text-red-500' : 'text-warning'"
                                x-text="formatMoney(change)">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Numpad -->
                <div class="w-full lg:w-1/2 p-6 bg-slate-800/50 flex flex-col">
                    <div class="grid grid-cols-3 gap-3 flex-1 mb-4">
                        <!-- Numpad Keys -->
                        <button @click="addNumber(1)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">1</button>
                        <button @click="addNumber(2)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">2</button>
                        <button @click="addNumber(3)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">3</button>

                        <button @click="addNumber(4)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">4</button>
                        <button @click="addNumber(5)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">5</button>
                        <button @click="addNumber(6)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">6</button>

                        <button @click="addNumber(7)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">7</button>
                        <button @click="addNumber(8)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">8</button>
                        <button @click="addNumber(9)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">9</button>

                        <button @click="addNumber('.')"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">.</button>
                        <button @click="addNumber(0)"
                            class="bg-dark-card hover:bg-slate-700 text-2xl font-bold rounded-xl border-b-4 border-slate-900 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">0</button>
                        <button @click="backspace()"
                            class="bg-red-500/20 hover:bg-red-500/30 text-red-500 text-xl font-bold rounded-xl border-b-4 border-red-900/50 active:border-b-0 active:translate-y-1 transition-all h-20 shadow-lg">
                            <i class="bi bi-backspace"></i>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button
                            class="flex-1 bg-slate-700 hover:bg-slate-600 text-white rounded-xl h-16 font-bold transition-colors"
                            @click="showPaymentModal = false">
                            إلغاء
                        </button>
                        <button
                            class="flex-[2] bg-green-500 hover:bg-green-600 text-white rounded-xl h-16 font-bold text-xl shadow-lg shadow-green-500/30 transition-colors flex items-center justify-center gap-2"
                            @click="submitSale()">
                            <i class="bi bi-printer"></i>
                            <span>إتمام وطباعة</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Modal -->
        <div x-show="showAnalyticsModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-dark-card w-full max-w-4xl rounded-3xl shadow-2xl border border-slate-700 p-8 transform transition-all"
                @click.away="showAnalyticsModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-90 translate-y-4">

                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-secondary/20 text-secondary flex items-center justify-center">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <span>ملخص الوردية الحالية</span>
                    </h3>
                    <button @click="fetchAnalytics()" class="text-slate-400 hover:text-white transition-colors">
                        <i class="bi bi-arrow-clockwise text-xl"></i>
                    </button>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Total Sales -->
                    <div
                        class="bg-slate-800 p-5 rounded-2xl border border-slate-700 relative overflow-hidden group hover:border-primary/50 transition-colors">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="bi bi-currency-dollar text-6xl text-primary"></i>
                        </div>
                        <div class="text-slate-400 text-sm mb-1">إجمالي المبيعات</div>
                        <div class="text-2xl font-bold text-white font-mono"
                            x-text="formatMoney(analyticsData.total_sales || 0)"></div>
                    </div>

                    <!-- Invoices Count -->
                    <div
                        class="bg-slate-800 p-5 rounded-2xl border border-slate-700 relative overflow-hidden group hover:border-blue-500/50 transition-colors">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="bi bi-receipt text-6xl text-blue-500"></i>
                        </div>
                        <div class="text-slate-400 text-sm mb-1">عدد الفواتير</div>
                        <div class="text-2xl font-bold text-white font-mono" x-text="analyticsData.invoices_count || 0">
                        </div>
                    </div>

                    <!-- Cash -->
                    <div
                        class="bg-slate-800 p-5 rounded-2xl border border-slate-700 relative overflow-hidden group hover:border-green-500/50 transition-colors">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="bi bi-cash-stack text-6xl text-green-500"></i>
                        </div>
                        <div class="text-slate-400 text-sm mb-1">مبيعات نقدية</div>
                        <div class="text-2xl font-bold text-green-400 font-mono"
                            x-text="formatMoney(analyticsData.cash_total || 0)"></div>
                    </div>

                    <!-- Card -->
                    <div
                        class="bg-slate-800 p-5 rounded-2xl border border-slate-700 relative overflow-hidden group hover:border-purple-500/50 transition-colors">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="bi bi-credit-card text-6xl text-purple-500"></i>
                        </div>
                        <div class="text-slate-400 text-sm mb-1">مبيعات بطاقة</div>
                        <div class="text-2xl font-bold text-purple-400 font-mono"
                            x-text="formatMoney(analyticsData.card_total || 0)"></div>
                    </div>
                </div>

                <!-- Recent Transactions Placeholder (Can be added later) -->
                <div class="bg-slate-800/50 rounded-2xl p-6 border border-slate-700">
                    <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                        <i class="bi bi-clock-history text-slate-400"></i>
                        <span>آخر العمليات</span>
                    </h4>
                    <div class="space-y-2">
                        <!-- We can iterate over recent transactions if passed from backend, for now static empty state or simple list -->
                        <div class="text-center text-slate-500 py-4 text-sm">
                            يتم تحديث البيانات لحظياً مع كل عملية بيع
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Initialization Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', () => ({
                currentCategory: 'all',
                searchQuery: '',
                cart: [],
                customers: @json($customers),
                products: {!! json_encode($categories->flatMap->products) !!},
                selectedCustomer: '',
                selectedWarehouse: '{{ $warehouses->first()->id ?? "" }}',
                selectedPaymentAccount: '{{ $paymentAccounts->first()->id ?? "" }}',
                discount: 0,

                // Payment Modal State
                showPaymentModal: false,
                paymentMethod: 'cash', // cash, card, credit, split
                paidAmount: 0,

                // Success Modal State
                showSuccessModal: false,
                lastInvoiceNumber: '',

                // Hold/Recall State
                heldOrders: [],
                showHeldOrdersModal: false,
                showShortcutsModal: false,

                // Offline State
                isOffline: !navigator.onLine,
                pendingSync: [],

                // Return Mode
                isReturnMode: false,

                // Analytics
                showAnalyticsModal: false,
                analyticsData: {},

                init() {
                    // Load held orders & pending sync
                    const savedHeld = localStorage.getItem('pos_held_orders');
                    if (savedHeld) this.heldOrders = JSON.parse(savedHeld);

                    const savedPending = localStorage.getItem('pos_pending_sales');
                    if (savedPending) this.pendingSync = JSON.parse(savedPending);

                    // Network Listeners
                    window.addEventListener('online', () => {
                        this.isOffline = false;
                        this.syncSales();
                    });
                    window.addEventListener('offline', () => {
                        this.isOffline = true;
                    });

                    // Attempt sync if online
                    if (!this.isOffline) this.syncSales();

                    // Start clock
                    setInterval(() => { this.time = new Date() }, 60000);

                    // Global Keyboard Listeners
                    document.addEventListener('keydown', (e) => {
                        // F-Keys
                        if (e.key === 'F1') { e.preventDefault(); this.showShortcutsModal = true; }
                        if (e.key === 'F2') { e.preventDefault(); document.querySelector('input[type="text"]')?.focus(); }
                        if (e.key === 'F8') { e.preventDefault(); this.holdOrder(); }
                        if (e.key === 'F9') { e.preventDefault(); this.showHeldOrdersModal = true; }
                        if (e.key === 'F10') { e.preventDefault(); this.fetchAnalytics(); }
                        if (e.key === 'F12') { e.preventDefault(); this.openPaymentModal(); }
                        if (e.key === 'Escape') {
                            this.showPaymentModal = false;
                            this.showSuccessModal = false;
                            this.showHeldOrdersModal = false;
                            this.showShortcutsModal = false;
                            this.showAnalyticsModal = false;
                        }

                        // Ignore input fields for scanner
                        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                        const char = e.key;
                        const currentTime = Date.now();

                        // Scanner acts like fast typing
                        if (currentTime - lastKeyTime > 50) barcodeBuffer = '';
                        lastKeyTime = currentTime;

                        if (char === 'Enter') {
                            if (barcodeBuffer.length > 2) {
                                this.handleBarcode(barcodeBuffer);
                                barcodeBuffer = '';
                            }
                        } else if (char.length === 1) {
                            barcodeBuffer += char;
                        }
                    });

                    // Scanner vars (re-declared here for closure)
                    let barcodeBuffer = '';
                    let lastKeyTime = Date.now();
                },

                get filteredProducts() {
                    let items = this.products;
                    if (this.currentCategory !== 'all') items = items.filter(p => p.category_id === this.currentCategory);
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        items = items.filter(p => p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)) || (p.barcode && p.barcode.includes(q)));
                    }
                    return items;
                },

                handleBarcode(code) {
                    const product = this.products.find(p => p.barcode === code || p.sku === code);
                    if (product) this.addToCart(product);
                    else alert('Product not found: ' + code);
                },

                addToCart(product) {
                    const qtyToAdd = this.isReturnMode ? -1 : 1;
                    const existing = this.cart.find(item => item.id === product.id);

                    if (existing) {
                        existing.qty += qtyToAdd;
                        if (existing.qty === 0) {
                            this.removeFromCart(this.cart.indexOf(existing));
                            return;
                        }
                    } else {
                        this.cart.push({ ...product, qty: qtyToAdd, price: parseFloat(product.selling_price) });
                    }
                    this.scrollToBottom();
                },

                toggleReturnMode() {
                    this.isReturnMode = !this.isReturnMode;
                    if (this.isReturnMode) {
                        // Optional: Clear cart if switching? No, allow mixed.
                        const toast = document.createElement('div');
                        toast.className = 'fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-red-600 text-white px-8 py-4 rounded-2xl font-bold shadow-2xl z-50 text-xl animate-bounce';
                        toast.innerText = 'وضع المرتجع مفعل 🔄';
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 1500);
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                updateQty(index, change) {
                    this.cart[index].qty += change;
                    if (this.cart[index].qty === 0) this.cart[index].qty = this.isReturnMode ? -1 : 1; // Prevent 0
                },

                scrollToBottom() {
                    const container = document.getElementById('cart-items-container');
                    if (container) setTimeout(() => container.scrollTop = container.scrollHeight, 50);
                },

                get subtotal() { return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0); },
                get tax() { return this.subtotal * 0.14; }, // 14% VAT
                get total() {
                    const t = this.subtotal + this.tax - this.discount;
                    // Allow negative total for returns
                    return t;
                },
                get change() { return Math.max(0, this.paidAmount - this.total); },

                formatMoney(amount) {
                    return new Intl.NumberFormat('en-EG', { style: 'currency', currency: 'EGP' }).format(amount);
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return alert('السلة فارغة');
                    this.showPaymentModal = true;
                    this.paidAmount = 0; // Reset
                    this.paymentMethod = 'cash';

                    // Focus numpad logic or input? 
                    // We'll use the virtual numpad mainly.
                },

                // Numpad Functions
                addNumber(num) {
                    // Prevent multiple dots
                    if (num === '.' && this.paidAmount.toString().includes('.')) return;

                    // If 0, replace
                    if (this.paidAmount === 0 && num !== '.') this.paidAmount = num;
                    else this.paidAmount = this.paidAmount.toString() + num;
                },

                backspace() {
                    let str = this.paidAmount.toString();
                    if (str.length > 0) {
                        this.paidAmount = str.slice(0, -1);
                        if (this.paidAmount === '') this.paidAmount = 0;
                    }
                },

                setExact() {
                    this.paidAmount = this.total;
                },

                setMethod(method) {
                    this.paymentMethod = method;
                },

                async fetchAnalytics() {
                    this.showAnalyticsModal = true;
                    try {
                        const response = await fetch('/pos/shift-report-quick');
                        this.analyticsData = await response.json();
                    } catch (e) {
                        console.error('Analytics Error', e);
                    }
                },

                async submitSale() {
                    if (parseFloat(this.paidAmount) < this.total && this.paymentMethod !== 'credit') {
                        return alert('المبلغ المدفوع أقل من الإجمالي!');
                    }

                    const payload = {
                        customer_id: this.selectedCustomer || null,
                        warehouse_id: this.selectedWarehouse,
                        payment_account_id: this.selectedPaymentAccount,
                        items: this.cart.map(item => ({
                            product_id: item.id,
                            quantity: item.qty,
                            price: item.price,
                            discount: 0
                        })),
                        amount_paid: parseFloat(this.paidAmount),
                        payment_method: this.paymentMethod,
                        discount: this.discount,
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    };

                    try {
                        const response = await fetch('/pos/checkout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': payload._token
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (response.ok) {
                            this.lastInvoiceNumber = result.invoice_number;
                            this.showPaymentModal = false;

                            // Success Alert
                            Swal.fire({
                                icon: 'success',
                                title: 'تمت العملية بنجاح',
                                text: 'تم إنشاء الفاتورة: ' + result.invoice_number,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // this.showSuccessModal = true; // Use SwAlert instead
                            this.resetPOS(true); // reset but keep items? No, clear all

                            // Auto Print?
                            setTimeout(() => {
                                // window.open('/sales/invoices/' + result.invoice_id + '/print', '_blank');
                            }, 1000);

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: result.message || 'حدث خطأ غير متوقع'
                            });
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في النظام',
                            text: 'تعذر الاتصال بالخادم'
                        });
                    }
                },

                printReceipt() {
                    if (!this.lastInvoiceNumber) return;
                    // window.open('/pos/receipt/' + this.lastInvoiceNumber, '_blank');
                    Swal.fire('جارِ الطباعة...', this.lastInvoiceNumber, 'info');
                },

                resetPOS(success = false) {
                    this.cart = [];
                    this.showPaymentModal = false;
                    this.showSuccessModal = false;
                    this.paidAmount = 0;
                    this.discount = 0;
                    this.selectedCustomer = '';
                    if (!success) {
                        Swal.fire({
                            icon: 'info',
                            title: 'تم إعادة تعيين الكاشير',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                }
            }));
        });
    </script>
</body>

</html>