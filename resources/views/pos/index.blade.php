@extends('layouts.app')

@section('title', 'نقطة البيع (POS) | Midnight Pro')

@section('content')
    <div x-data="posSystem()" x-init="init()" class="pos-wrapper" @keydown.window="handleGlobalKeys($event)">
        <!-- Audio Effects -->
        <audio id="beep-sound" src="{{ asset('assets/sounds/beep.mp3') }}"></audio>
        <audio id="success-sound" src="{{ asset('assets/sounds/success.mp3') }}"></audio>

        <div class="row g-0 flex-grow-1 h-100 overflow-hidden">

            <!-- ====================
                                                                                                                                         LEFT PANEL: CART 
                                                                                                                                         ==================== -->
            <div class="col-md-5 col-lg-4 d-flex flex-column border-end border-secondary bg-dark h-100 position-relative">

                <!-- Customer & Shift Info -->
                <div class="p-3 border-bottom border-secondary bg-darker">
                    <div class="d-flex justify-content-between mb-2 gap-2">
                        <span
                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 d-flex align-items-center"
                            @click="checkShift" style="cursor: pointer">
                            <i class="bi bi-clock-history me-1"></i>
                            <span x-text="shiftId ? 'وردية #' + shiftId : 'الوردية مغلقة'"></span>
                        </span>

                        <!-- Warehouse Selector -->
                        <select class="form-select form-select-sm bg-dark text-white border-secondary w-auto"
                            x-model="warehouseId" title="المخزن">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{Str::limit($w->name, 15)}}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-sm btn-outline-warning py-0" @click="toggleFullscreen">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </div>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i
                                class="bi bi-person"></i></span>
                        <select class="form-select bg-dark text-white border-secondary" x-model="customerId">
                            <option value="">عابر (Walk-in Customer)</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>

                        <!-- Customer Type Badge -->
                        <span x-show="customerId" class="input-group-text border-secondary fw-bold"
                            :class="getTypeBadgeClass(getSelectedCustomer()?.type)"
                            x-text="getTypeLabel(getSelectedCustomer()?.type)">
                        </span>

                        <button class="btn btn-outline-secondary" type="button" @click="showAddCustomerModal"><i
                                class="bi bi-plus-lg"></i></button>
                    </div>

                    <!-- Delivery Toggle -->
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <div class="form-check form-switch flex-grow-1">
                            <input class="form-check-input" type="checkbox" id="deliverySwitch" x-model="isDelivery">
                            <label class="form-check-label text-white small" for="deliverySwitch">
                                <i class="bi bi-truck me-1"></i> توصيل (Delivery)
                            </label>
                        </div>
                        <template x-if="isDelivery">
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm bg-dark text-white border-secondary w-auto"
                                    x-model="driverId">
                                    <option value="">اختار المندوب</option>
                                    @foreach($drivers as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number"
                                    class="form-control form-control-sm bg-dark text-white border-secondary"
                                    style="width: 70px;" placeholder="رسوم" x-model.number="deliveryFee"
                                    @input="calculateTotals()">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Cart Items (Scrollable) -->
                <div class="flex-grow-1 overflow-auto p-2 scrollbar-custom" id="cart-items">
                    <template x-if="cart.length === 0">
                        <div
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-secondary opacity-50">
                            <i class="bi bi-cart4 fs-1 mb-3"></i>
                            <p class="fs-5">السلة فارغة</p>
                            <small>ابدأ بمسح الباركود أو البحث</small>
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="index">
                        <div class="card mb-2 border-secondary bg-dark shadow-sm cart-item-anim">
                            <div class="card-body p-2 d-flex align-items-center gap-2">
                                <!-- Qty Control -->
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <button @click="updateQty(index, 1)"
                                        class="btn btn-sm btn-outline-success p-0 rounded-circle"
                                        style="width: 24px; height: 24px;">+</button>
                                    <span class="fw-bold font-monospace text-white" x-text="item.quantity"></span>
                                    <button @click="updateQty(index, -1)"
                                        class="btn btn-sm btn-outline-danger p-0 rounded-circle"
                                        style="width: 24px; height: 24px;">-</button>
                                </div>

                                <!-- Name & Price -->
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-white mb-1 text-truncate" x-text="item.name"></div>
                                    <div class="d-flex justify-content-between align-items-center small text-secondary">
                                        <span class="font-monospace" x-text="formatMoney(item.price)"></span>

                                        <!-- Discount Edit -->
                                        <div class="input-group input-group-sm w-25">
                                            <input type="number"
                                                class="form-control bg-dark border-secondary text-white p-0 text-center"
                                                x-model="item.discount" @change="calculateTotals()" placeholder="خصم">
                                        </div>

                                        <span class="font-monospace text-white fw-bold"
                                            x-text="formatMoney((item.price * item.quantity) - (item.discount || 0))"></span>
                                    </div>
                                </div>

                                <!-- Remove -->
                                <button @click="removeFromCart(index)" class="btn btn-link text-danger p-0 ms-1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Totals & Actions -->
                <div class="p-3 border-top border-secondary bg-darker mt-auto shadow-lg z-2">
                    <div class="d-flex justify-content-between mb-1 text-secondary small">
                        <span>المجموع الفرعي:</span>
                        <span class="font-monospace" x-text="formatMoney(subtotal)"></span>
                    </div>
                    <!-- Tax & Discount Inputs -->
                    <div class="d-flex justify-content-between mb-2 small text-secondary align-items-center">
                        <span>الضريبة:</span>
                        <span class="font-monospace text-danger" x-text="formatMoney(tax)"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-secondary align-items-center">
                        <span class="d-flex align-items-center">
                            خصم إضافي:
                            <button class="btn btn-xs btn-outline-secondary ms-2 py-0 px-1" @click="showDiscountModal">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </span>
                        <span class="font-monospace text-success" x-text="'-' + formatMoney(globalDiscount)"></span>
                    </div>

                    <div class="d-flex justify-content-between mb-3 border-top border-secondary pt-2">
                        <span class="fs-4 fw-bold text-white">الإجمالي:</span>
                        <div class="text-end">
                            <span class="fs-3 fw-bold text-primary font-monospace" x-text="formatMoney(total)"></span>
                            <div x-show="isDelivery && deliveryFee > 0" class="small text-secondary">
                                (شامل رسوم التوصيل: <span x-text="formatMoney(deliveryFee)"></span>)
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-danger py-3" @click="clearCart" :disabled="cart.length === 0"
                            title="إلغاء">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button class="btn btn-warning flex-grow-1 py-3 fw-bold" @click="holdSale"
                            :disabled="cart.length === 0 || isProcessing">
                            <span x-show="!isProcessing"><i class="bi bi-pause-circle me-1"></i> تعليق</span>
                            <span x-show="isProcessing"><span class="spinner-border spinner-border-sm"></span></span>
                        </button>
                        <button class="btn btn-success flex-grow-1 py-3 fw-bold bg-gradient-success shadow"
                            @click="showPaymentModal" :disabled="cart.length === 0">
                            <i class="bi bi-cash-stack me-1"></i> دفع (F9)
                        </button>
                    </div>
                </div>
            </div>

            <!-- ====================
                                                                                                                                 RIGHT PANEL: PRODUCTS 
                                                                                                                                 ==================== -->
            <div class="col-md-7 col-lg-8 d-flex flex-column h-100 bg-body-tertiary">

                <!-- Search & Filter -->
                <div class="p-3 bg-dark border-bottom border-secondary shadow-sm z-2">
                    <div class="input-group input-group-lg mb-3 shadow-sm">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i
                                class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white"
                            placeholder="بحث... (F2 للتركيز)" x-model="searchQuery" @keydown.enter="searchProducts"
                            @input.debounce.500ms="searchProducts" id="searchInput" autofocus autocomplete="off">
                        <button class="btn btn-primary" type="button" @click="searchProducts">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    <!-- Categories -->
                    <div class="d-flex gap-2 overflow-auto pb-2 scrollbar-none">
                        <button class="btn btn-sm rounded-pill text-nowrap px-3 transition-all"
                            :class="selectedCategory === null ? 'btn-primary shadow' : 'btn-outline-secondary'"
                            @click="filterCategory(null)">
                            <i class="bi bi-grid-fill me-1"></i> الكل
                        </button>
                        @foreach($categories as $cat)
                            <button class="btn btn-sm rounded-pill text-nowrap px-3 transition-all"
                                :class="selectedCategory === {{ $cat->id }} ? 'btn-primary shadow' : 'btn-outline-secondary'"
                                @click="filterCategory({{ $cat->id }})">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="flex-grow-1 overflow-auto p-3 scrollbar-custom bg-black bg-opacity-25">
                    <div class="row g-3">
                        <!-- Loading State -->
                        <div x-show="isLoading" class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-secondary animate-pulse">جاري البحث...</p>
                        </div>

                        <!-- Products Loop -->
                        <template x-for="product in products" :key="product.id">
                            <div class="col-6 col-md-4 col-lg-3 col-xl-3">
                                <div class="card h-100 border-secondary bg-dark card-hover cursor-pointer position-relative"
                                    :class="{'opacity-50 grayscale pointer-events-none': product.stock <= 0}"
                                    @click="product.stock > 0 && addToCart(product)">
                                    <!-- Image -->
                                    <div class="position-relative overflow-hidden rounded-top bg-dark-subtle d-flex align-items-center justify-content-center"
                                        style="height: 120px;">
                                        <!-- Placeholder (Always There) -->
                                        <div class="text-secondary opacity-25">
                                            <i class="bi bi-box-seam fs-1"></i>
                                        </div>

                                        <!-- Image - Fades in on Load -->
                                        <img :src="product.image || ''"
                                            class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 transition-opacity duration-300"
                                            style="opacity: 0" onload="this.style.opacity = 1"
                                            onerror="this.style.opacity = 0">

                                        <div class="position-absolute top-0 end-0 m-1">
                                            <span class="badge" :class="product.stock > 0 ? 'bg-success' : 'bg-danger'">
                                                <span x-text="product.stock > 0 ? product.stock : 'نفذ'"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="card-body p-2 d-flex flex-column">
                                        <h6 class="card-title text-white small mb-1 fw-bold text-truncate"
                                            x-text="product.name" :title="product.name"></h6>
                                        <small class="text-secondary font-monospace d-block mb-2"
                                            x-text="product.sku"></small>

                                        <div class="mt-auto d-flex justify-content-between align-items-end">
                                            <span
                                                class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 fs-6 font-monospace"
                                                x-text="formatMoney(product.price)"></span>
                                            <i x-show="product.stock > 0"
                                                class="bi bi-plus-circle-fill text-primary fs-5 opacity-50 add-btn"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="!isLoading && products.length === 0" class="col-12 text-center py-5 text-secondary">
                            <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                            <p>لا توجد منتجات</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================
                                                                                                                             MODALS
                                                                                                                             ==================== -->

        <!-- 1. Shift Management Modal -->
        <div class="modal fade" id="shiftModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title text-white">
                            <i class="bi bi-clock-history me-2"></i>
                            <span x-text="shiftId ? 'تفاصيل الوردية الحالية' : 'فتح وردية جديدة'"></span>
                        </h5>
                        <button x-show="shiftId" type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Open Shift Form -->
                        <div x-show="!shiftId">
                            <label class="form-label text-white">النقدية الافتتاحية في الدرج</label>
                            <div class="input-group input-group-lg mb-3">
                                <span class="input-group-text bg-secondary border-secondary text-white">ج.م</span>
                                <input type="number" class="form-control bg-dark border-secondary text-white"
                                    x-model="openingCash" placeholder="0.00">
                            </div>
                            <button class="btn btn-primary w-100 py-2 fw-bold" @click="openShift">
                                <i class="bi bi-check-circle me-1"></i> فتح الوردية
                            </button>
                        </div>

                        <!-- Close Shift View -->
                        <div x-show="shiftId">
                            <div class="card bg-dark border-secondary mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">عدد الفواتير:</span>
                                        <span class="fw-bold" x-text="shiftStats.total_sales">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">مبيعات نقدية (Cash):</span>
                                        <span class="fw-bold text-success"
                                            x-text="formatMoney(shiftStats.total_cash)">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">مبيعات بطاقة (Card):</span>
                                        <span class="fw-bold text-info" x-text="formatMoney(shiftStats.total_card)">0</span>
                                    </div>
                                    <hr class="border-secondary my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">النقدية المتوقعة في الدرج:</span>
                                        <span class="fw-bold fs-5 text-primary"
                                            x-text="formatMoney(shiftStats.expected_cash)">0</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-white">النقدية الفعلية في الدرج عند الإغلاق</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-secondary border-secondary text-white">ج.م</span>
                                    <input type="number" class="form-control bg-dark border-secondary text-white"
                                        x-model="closingCash" placeholder="0.00">
                                </div>
                                <small class="text-secondary mt-1 d-block text-center">سيتم حساب العجز أو الزيادة
                                    آلياً</small>
                            </div>

                            <button class="btn btn-danger w-100 py-3 fw-bold shadow" @click="closeShift">
                                <i class="bi bi-x-octagon me-1"></i> إغلاق الوردية وتصفية الحساب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discount Modal -->
        <div class="modal fade" id="discountModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content bg-dark border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title text-white">خصم إضافي</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label text-white mb-2">قيمة الخصم (مبلغ ثابت)</label>
                        <div class="input-group input-group-lg">
                            <input type="number" class="form-control bg-dark border-secondary text-white text-center"
                                x-model.number="globalDiscount" @change="calculateTotals()" placeholder="0.00">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">تطبيق</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title text-white"><i class="bi bi-credit-card me-2"></i>إتمام الدفع</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <small class="text-secondary text-uppercase ls-1">المبلغ المطلوب</small>
                            <h1 class="display-4 fw-bold text-primary font-monospace m-0" x-text="formatMoney(total)"></h1>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white text-start w-100">طريقة الدفع</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash"
                                        x-model="paymentMethod" checked>
                                    <label class="btn btn-outline-success w-100 py-3" for="pay_cash">
                                        <i class="bi bi-cash h3 d-block"></i> نقدي (Cash)
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="card"
                                        x-model="paymentMethod">
                                    <label class="btn btn-outline-info w-100 py-3" for="pay_card">
                                        <i class="bi bi-credit-card-2-front h3 d-block"></i> بطاقة (Card)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">المبلغ المدفوع</label>
                            <div class="input-group input-group-lg">
                                <input type="number"
                                    class="form-control bg-dark border-secondary text-white text-center fw-bold"
                                    x-model.number="amountPaid" id="amountPaidInput" @keyup.enter="processPayment">
                                <button class="btn btn-outline-secondary" @click="amountPaid = total">بالضبط</button>
                            </div>
                        </div>

                        <div class="alert alert-dark border-secondary d-flex justify-content-between align-items-center">
                            <span class="text-secondary">الباقي للعميل:</span>
                            <span class="fs-4 fw-bold" :class="change < 0 ? 'text-danger' : 'text-success'"
                                x-text="formatMoney(Math.max(0, change))"></span>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-primary px-5 fw-bold" @click="processPayment"
                            :disabled="isProcessing">
                            <span x-show="!isProcessing"><i class="bi bi-check-lg me-1"></i> تأكيد ودفع</span>
                            <span x-show="isProcessing"><span class="spinner-border spinner-border-sm me-1"></span>
                                جاري...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">إضافة عميل جديد</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-white">الاسم</label>
                        <input type="text" class="form-control bg-dark border-secondary text-white"
                            x-model="newCustomer.name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">رقم الهاتف</label>
                        <input type="text" class="form-control bg-dark border-secondary text-white"
                            x-model="newCustomer.phone">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" @click="saveCustomer">حفظ</button>
                </div>
            </div>
            <!-- 4. Shortcuts Help Modal -->
            <div class="modal fade" id="shortcutsModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark border-secondary">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title text-white">
                                <i class="bi bi-keyboard me-2"></i>
                                اختصارات لوحة المفاتيح
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <table class="table table-dark table-hover m-0">
                                <tbody>
                                    <tr>
                                        <td><kbd>F1</kbd> or <kbd>H</kbd></td>
                                        <td>إظهار هذه القائمة</td>
                                    </tr>
                                    <tr>
                                        <td><kbd>F2</kbd></td>
                                        <td>التركيز على محرك البحث</td>
                                    </tr>
                                    <tr>
                                        <td><kbd>F9</kbd></td>
                                        <td>إظهار نافذة الدفع</td>
                                    </tr>
                                    <tr>
                                        <td><kbd>F10</kbd></td>
                                        <td>تغيير العميل</td>
                                    </tr>
                                    <tr>
                                        <td><kbd>F11</kbd></td>
                                        <td>تفريغ السلة بالكامل</td>
                                    </tr>
                                    <tr>
                                        <td><kbd>Esc</kbd></td>
                                        <td>إغلاق النوافذ المفتوحة</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Sales Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-arrow-return-left me-2"></i>
                        مرتجع مبيعات (Sales Return)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Search Invoice -->
                    <div class="input-group mb-3">
                        <input type="text" class="form-control bg-dark text-white border-secondary"
                            placeholder="أدخل رقم الفاتورة (مثلاً POS-20240101-0001)" x-model="searchReturnQuery"
                            @keyup.enter="searchReturnInvoice">
                        <button class="btn btn-primary" @click="searchReturnInvoice">بحث</button>
                    </div>

                    <template x-if="returnInvoice">
                        <div>
                            <div class="alert alert-secondary border-secondary bg-dark text-white small mb-3">
                                <div class="row">
                                    <div class="col-md-6">فاتورة: <span x-text="returnInvoice.invoice_number"></span></div>
                                    <div class="col-md-6">التاريخ: <span x-text="returnInvoice.invoice_date"></span></div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-dark table-sm">
                                    <thead>
                                        <tr>
                                            <th>المنتج</th>
                                            <th>الكمية الأصلية</th>
                                            <th>الكمية المرتجعة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="item in returnItems" :key="item.line_id">
                                            <tr>
                                                <td x-text="item.name"></td>
                                                <td x-text="item.original_qty"></td>
                                                <td style="width: 120px;">
                                                    <input type="number"
                                                        class="form-control form-control-sm bg-dark text-white border-secondary"
                                                        x-model.number="item.quantity" :max="item.original_qty" min="0">
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white small">سبب المرتجع</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary"
                                    x-model="returnReason">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-danger small">الرقم السري للمرتجع (Refund PIN)</label>
                                <input type="password" class="form-control bg-dark text-white border-danger"
                                    x-model="refundPin" placeholder="****">
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-modal="modal"
                        data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" @click="processReturn"
                        :disabled="!returnInvoice || !refundPin">تأكيد المرتجع</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // Axios Global Configuration
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Content-Type'] = 'application/json';
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.timeout = 15000; // 15 seconds timeout to prevent infinite hanging

        const SOUNDS = {
            beep: new Audio('{{ asset("assets/sounds/beep.mp3") }}'),
            success: new Audio('{{ asset("assets/sounds/success.mp3") }}')
        };

        function posSystem() {
            return {
                isLoading: false,
                isProcessing: false,
                searchQuery: '',
                activeCategory: null, // Changed from selectedCategory
                customerId: '',
                shiftId: {{ $activeShift?->id ?? 'null' }},
                openingCash: '',
                closingCash: '',
                shiftStats: { total_sales: 0, total_cash: 0, total_card: 0, expected_cash: 0, total_amount: 0 },

                // Cart Data
                cart: [],
                products: [],
                globalDiscount: 0,
                warehouseId: '{{ $warehouses->first()->id ?? 1 }}', // Default to first warehouse

                // Payment Data
                paymentMethod: 'cash',
                amountPaid: 0,
                notes: '',

                // Delivery
                isDelivery: false,
                deliveryFee: 0,
                driverId: '',

                // Return Logic
                searchReturnQuery: '',
                returnInvoice: null,
                returnItems: [],
                returnReason: '',
                refundPin: '',

                // Customer Data
                newCustomer: { name: '', phone: '' },

                // Computed
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + ((item.price * item.quantity) - (item.discount || 0)), 0);
                },

                // Settings injected from Backend
                globalTaxRate: {{ \App\Models\Setting::getValue('default_tax_rate', 0) }},
                isTaxInclusive: {{ \App\Models\Setting::getValue('tax_inclusive', false) ? 'true' : 'false' }},

                get tax() {
                    return this.cart.reduce((acc, item) => {
                        // Use item tax if exists (and > 0), else global
                        // Ensure input is treated as number
                        let itemRate = parseFloat(item.tax_rate);
                        if (isNaN(itemRate) || itemRate === 0) itemRate = this.globalTaxRate;

                        // Tax Calculation Base: Always use Original Price (Quantity * Price)
                        // Ignore discount for tax calculation as per business rule
                        let lineVal = (item.price * item.quantity);

                        if (this.isTaxInclusive) {
                            // Back-calculate Tax: Gross * (Rate / (100 + Rate))
                            return acc + (lineVal * (itemRate / (100 + itemRate)));
                        } else {
                            // Regular Tax: Net * (Rate / 100)
                            return acc + (lineVal * (itemRate / 100));
                        }
                    }, 0);
                },

                get total() {
                    let baseTotal = 0;
                    if (this.isTaxInclusive) {
                        baseTotal = this.subtotal - this.globalDiscount;
                    } else {
                        baseTotal = this.subtotal + this.tax - this.globalDiscount;
                    }
                    return Math.max(0, baseTotal + (this.isDelivery ? this.deliveryFee : 0));
                },

                get change() {
                    return this.amountPaid - this.total;
                },

                // Data
                activeCustomers: @json($customers),

                // Init
                init() {
                    this.loadInitialProducts();

                    // Only auto-open if NO active shift
                    // Use a small delay to ensure DOM is ready
                    setTimeout(() => {
                        if (!this.shiftId) {
                            this.checkShift();
                        }
                    }, 500);

                    this.$watch('total', value => { this.amountPaid = value; });

                    // Filters Watchers
                    this.$watch('warehouseId', () => {
                        this.cart = [];
                        this.searchProducts();
                    });
                    this.$watch('activeCategory', () => {
                        this.searchProducts();
                    });

                    // Customer & Pricing Watcher
                    this.$watch('customerId', (id) => {
                        if (this.cart.length > 0) {
                            this.recalculateCartPrices();
                        }
                    });
                },

                // Pricing Helper
                getCustomerPrice(product) {
                    if (!this.customerId) return product.price;

                    const customer = this.activeCustomers.find(c => c.id == this.customerId);
                    if (!customer) return product.price;

                    // Map customer type to pricing field
                    // types: distributor, wholesale, half_wholesale, quarter_wholesale, technician, employee, vip
                    // fields: price_distributor, price_wholesale, ...

                    // Special case for 'company' -> maybe wholesale? checking rules.
                    // For now, assuming direct mapping to fields we created.
                    // If type is 'company', we might default to 'wholesale' or just 'price' if not mapped.
                    // In Product model we have: price_distributor, price_wholesale, price_half_wholesale, price_quarter_wholesale, price_special

                    let priceField = 'price';
                    switch (customer.type) {
                        case 'distributor': priceField = 'price_distributor'; break;
                        case 'wholesale': priceField = 'price_wholesale'; break;
                        case 'half_wholesale': priceField = 'price_half_wholesale'; break;
                        case 'quarter_wholesale': priceField = 'price_quarter_wholesale'; break;
                        case 'technician':
                        case 'employee':
                        case 'vip':
                            priceField = 'price_special'; break;
                    }

                    // Return tiered price if exists and > 0, else normal price
                    // Note: accessing product[priceField]
                    const tierPrice = parseFloat(product[priceField]);
                    return (tierPrice > 0) ? tierPrice : product.price;
                },

                recalculateCartPrices() {
                    this.cart.forEach(item => {
                        item.price = this.getCustomerPrice(item);
                    });
                },

                checkShift() {
                    var modalEl = document.getElementById('shiftModal');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }

                    if (this.shiftId) {
                        this.fetchShiftDetails();
                    }
                },

                openShift() {
                    if (!this.openingCash) return;
                    axios.post('{{ route("pos.shift.open") }}', { opening_cash: this.openingCash })
                        .then(res => {
                            this.shiftId = res.data.shift.id;
                            bootstrap.Modal.getInstance(document.getElementById('shiftModal')).hide();
                            alert('تم فتح الوردية بنجاح');
                        })
                        .catch(err => alert('خطأ في فتح الوردية'));
                },

                closeShift() {
                    if (!this.closingCash) {
                        alert('من فضلك أدخل المبلغ الفعلي في الدرج');
                        return;
                    }
                    if (!confirm('هل أنت متأكد من إغلاق الوردية وتصفية الحساب؟')) return;

                    axios.post('{{ route("pos.shift.close") }}', { closing_cash: this.closingCash })
                        .then(res => {
                            alert('تم إغلاق الوردية بنجاح. الفرق: ' + this.formatMoney(res.data.diff));
                            location.reload();
                        })
                        .catch(err => alert('خطأ في إغلاق الوردية: ' + (err.response?.data?.message || err.message)));
                },

                fetchShiftDetails() {
                    if (!this.shiftId) return;
                    axios.get('{{ route("pos.shift.stats") }}')
                        .then(res => {
                            if (res.data.success) {
                                this.shiftStats = res.data.shift;
                                // Default closing cash to expected to save effort
                                if (!this.closingCash) this.closingCash = this.shiftStats.expected_cash;
                            }
                        })
                        .catch(err => console.error('Error fetching shift details:', err));
                },

                // Return Logic Methods
                showReturnModal() {
                    this.searchReturnQuery = '';
                    this.returnInvoice = null;
                    this.returnItems = [];
                    this.returnReason = '';
                    this.refundPin = '';
                    new bootstrap.Modal(document.getElementById('returnModal')).show();
                },

                searchReturnInvoice() {
                    if (!this.searchReturnQuery) return;
                    axios.get('{{ route("pos.invoice.search") }}', { params: { q: this.searchReturnQuery } })
                        .then(res => {
                            if (res.data.success) {
                                this.returnInvoice = res.data.invoice;
                                this.returnItems = res.data.invoice.lines.map(line => ({
                                    line_id: line.id,
                                    name: line.product.name,
                                    original_qty: line.quantity,
                                    quantity: 0
                                }));
                            } else {
                                alert('الفاتورة غير موجودة أو لا يمكن إرجاعها.');
                                this.returnInvoice = null;
                                this.returnItems = [];
                            }
                        })
                        .catch(err => {
                            alert('خطأ: ' + (err.response?.data?.message || 'الفاتورة غير موجودة'));
                            this.returnInvoice = null;
                            this.returnItems = [];
                        });
                },

                processReturn() {
                    const itemsToReturn = this.returnItems.filter(i => i.quantity > 0);
                    if (itemsToReturn.length === 0) {
                        alert('من فضلك حدد الكميات المرتجعة');
                        return;
                    }

                    axios.post('{{ route("pos.return") }}', {
                        invoice_id: this.returnInvoice.id,
                        items: itemsToReturn,
                        reason: this.returnReason,
                        pin: this.refundPin
                    })
                        .then(res => {
                            alert('تم إتمام المرجوع بنجاح');
                            bootstrap.Modal.getInstance(document.getElementById('returnModal')).hide();
                            location.reload();
                        })
                        .catch(err => alert('خطأ: ' + (err.response?.data?.message || 'فشل إتمام المرجوع')));
                },

                loadInitialProducts() {
                    this.searchProducts();
                },

                getSelectedCustomer() {
                    return this.activeCustomers.find(c => c.id == this.customerId);
                },

                getTypeLabel(type) {
                    const labels = {
                        'individual': 'فرد',
                        'company': 'شركة',
                        'distributor': 'موزع',
                        'wholesale': 'جملة',
                        'half_wholesale': 'نص جملة',
                        'quarter_wholesale': 'ربع جملة',
                        'technician': 'فني',
                        'employee': 'موظف',
                        'vip': 'VIP'
                    };
                    return labels[type] || type;
                },

                getTypeBadgeClass(type) {
                    const classes = {
                        'company': 'bg-primary text-white',
                        'distributor': 'bg-purple-600 text-white',
                        'wholesale': 'bg-orange-600 text-white',
                        'vip': 'bg-amber-500 text-black',
                        'individual': 'bg-secondary text-white'
                    };
                    return classes[type] || 'bg-secondary text-white';
                },

                searchProducts() {
                    this.isLoading = true;
                    // Reset products to force UI update if needed, but keeping them avoid flicker is better
                    // this.products = []; 

                    axios.get('{{ route("pos.search") }}', {
                        params: {
                            q: this.searchQuery,
                            category_id: this.activeCategory,
                            warehouse_id: this.warehouseId
                        }
                    })
                        .then(res => {
                            this.products = res.data;
                            this.isLoading = false;
                        })
                        .catch(err => {
                            console.error('Search failed', err);
                            this.isLoading = false;
                        });
                },

                findByBarcode(code) {
                    axios.get('{{ route("pos.barcode") }}', { params: { barcode: code, warehouse_id: this.warehouseId } })
                        .then(res => {
                            this.addToCart(res.data);
                            this.searchQuery = '';
                        })
                        .catch(err => {
                            console.log('Barcode not found');
                            // Optional: play error sound
                        });
                },

                filterCategory(id) {
                    this.activeCategory = id; // Fix variable name
                    this.searchProducts();
                },

                addToCart(product) {
                    if (product.stock <= 0) {
                        return;
                    }

                    // Determine price based on current customer
                    const finalPrice = this.getCustomerPrice(product);

                    let existing = this.cart.find(item => item.id === product.id);
                    if (existing) {
                        if (existing.quantity + 1 > product.stock) {
                            alert('لا توجد كمية كافية في المخزون! المتاح: ' + product.stock);
                            return;
                        }
                        existing.quantity++;
                        // Update price in case it changed (though usually recalculate covers it)
                        existing.price = finalPrice;
                    } else {
                        // Push full product data so we have the pricing tiers for later recalculation
                        this.cart.push({ ...product, quantity: 1, discount: 0, price: finalPrice });
                    }
                    if (typeof SOUNDS !== 'undefined') SOUNDS.beep.play().catch(e => { });
                },

                updateQty(index, delta) {
                    const item = this.cart[index];
                    const newQty = item.quantity + delta;

                    if (newQty <= 0) return;

                    // Stock check for increment
                    if (delta > 0 && newQty > item.stock) {
                        alert('لا توجد كمية كافية في المخزون! المتاح: ' + item.stock);
                        return;
                    }

                    item.quantity = newQty;
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    if (confirm('هل أنت متأكد من تفريغ السلة؟')) this.cart = [];
                },

                formatMoney(amount) {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'EGP' }).format(amount);
                },

                // Discount Logic
                showDiscountModal() {
                    new bootstrap.Modal(document.getElementById('discountModal')).show();
                },

                applyDiscount(type) {
                    // Logic to apply discount (percentage or fixed) to globalDiscount
                    // For simplicity, user inputs amount directly in modal
                    // This is just a UI trigger
                },

                // Actions
                showAddCustomerModal() {
                    new bootstrap.Modal(document.getElementById('addCustomerModal')).show();
                },

                saveCustomer() {
                    axios.post('{{ route("customers.store") }}', {
                        name: this.newCustomer.name,
                        phone: this.newCustomer.phone,
                        type: 'individual'
                    }).then(res => {
                        alert('تم إضافة العميل بنجاح');
                        this.customerId = res.data.id;
                        bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
                        location.reload();
                    }).catch(err => alert('خطأ في إضافة العميل'));
                },

                holdSale() {
                    this.isProcessing = true;
                    axios.post('{{ route("pos.hold") }}', {
                        items: this.cart.map(i => ({ product_id: i.id, quantity: i.quantity, price: i.price })),
                        customer_id: this.customerId
                    }).then(res => {
                        alert('تم تعليق الفاتورة');
                        this.cart = [];
                    }).catch(err => {
                        alert('خطأ في تعليق الفاتورة: ' + (err.response?.data?.message || err.message));
                    }).finally(() => {
                        this.isProcessing = false;
                    });
                },

                showPaymentModal() {
                    if (this.cart.length === 0) return;
                    this.amountPaid = this.total;
                    var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    modal.show();
                    setTimeout(() => document.getElementById('amountPaidInput').focus(), 500);
                },

                processPayment() {
                    this.isProcessing = true;
                    const payload = {
                        items: this.cart.map(i => ({
                            product_id: i.id,
                            quantity: i.quantity,
                            price: i.price,
                            discount: i.discount
                        })),
                        customer_id: this.customerId,
                        warehouse_id: this.warehouseId,
                        payment_method: this.paymentMethod,
                        amount_paid: this.amountPaid,
                        is_delivery: this.isDelivery,
                        driver_id: this.driverId,
                        delivery_fee: this.deliveryFee,
                        discount: this.globalDiscount,
                        notes: this.notes
                    };

                    axios.post('{{ route("pos.checkout") }}', payload)
                        .then(res => {
                            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                            if (typeof SOUNDS !== 'undefined') SOUNDS.success.play().catch(e => { });
                            window.open('{{ route("pos.receipt", ":id") }}'.replace(':id', res.data.invoice.id), '_blank', 'width=400,height=600');

                            // Clear cart and reset UI
                            this.cart = [];
                            this.globalDiscount = 0;
                            this.notes = '';

                            // IMPORTANT: Refresh products to update stock levels on UI
                            this.searchProducts();
                        })
                        .catch(err => {
                            alert('خطأ: ' + (err.response?.data?.message || err.message));
                        })
                        .finally(() => {
                            this.isProcessing = false;
                        });
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        if (document.exitFullscreen) document.exitFullscreen();
                    }
                },

                handleGlobalKeys(e) {
                    // Help (F1 or H)
                    if (e.key === 'F1' || (e.key === 'h' && document.activeElement.tagName !== 'INPUT')) {
                        e.preventDefault();
                        new bootstrap.Modal(document.getElementById('shortcutsModal')).show();
                    }

                    // Return (Alt+R)
                    if (e.altKey && e.key === 'r') {
                        e.preventDefault();
                        this.showReturnModal();
                    }

                    // Search (F2)
                    if (e.key === 'F2') {
                        e.preventDefault();
                        document.getElementById('searchInput').focus();
                    }

                    // Pay (F9)
                    if (e.key === 'F9') {
                        e.preventDefault();
                        if (this.cart.length > 0) this.showPaymentModal();
                    }

                    // Customer Selection (F10)
                    if (e.key === 'F10') {
                        e.preventDefault();
                        // Logic to focus customer select or open search
                    }

                    // Clear Cart (F11)
                    if (e.key === 'F11') {
                        e.preventDefault();
                        this.clearCart();
                    }
                }
            }
        }
    </script>

    <style>
        /* Styling */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            transition: transform 0.1s;
        }

        .bg-gradient-success:active {
            transform: scale(0.98);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            border-color: var(--bs-primary) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .add-btn {
            transition: all 0.2s;
        }

        .card-hover:hover .add-btn {
            opacity: 1 !important;
            transform: scale(1.1);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .transition-transform {
            transition: transform 0.3s ease;
        }

        /* Scrollbars */
        .scrollbar-custom::-webkit-scrollbar {
            width: 6px;
        }

        .scrollbar-custom::-webkit-scrollbar-track {
            background: #111827;
        }

        <style> :root {
            --pos-bg: #0a0f1e;
            --pos-panel: rgba(16, 23, 42, 0.8);
            --pos-border: rgba(255, 255, 255, 0.1);
            --pos-accent: #3b82f6;
            --pos-success: #10b981;
            --pos-danger: #ef4444;
            --pos-text: #f8fafc;
            --pos-text-muted: #94a3b8;
            --glass: blur(12px) saturate(180%);
        }

        body {
            background-color: var(--pos-bg) !important;
            color: var(--pos-text);
            font-family: 'Cairo', sans-serif;
            overflow: hidden;
        }

        .bg-dark {
            background-color: var(--pos-panel) !important;
        }

        .bg-darker {
            background-color: rgba(0, 0, 0, 0.4) !important;
        }

        .h-100.d-flex.flex-column {
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.05), transparent);
        }

        /* Glassmorphism Containers */
        .border-secondary {
            border-color: var(--pos-border) !important;
        }

        .card.bg-dark {
            background: rgba(30, 41, 59, 0.5) !important;
            backdrop-filter: var(--glass);
            -webkit-backdrop-filter: var(--glass);
            border: 1px solid var(--pos-border);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px) scale(1.02);
            border-color: var(--pos-accent) !important;
            background: rgba(30, 41, 59, 0.8) !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5),
                0 0 15px rgba(59, 130, 246, 0.3);
        }

        /* Readability Optimization */
        .text-white {
            color: #ffffff !important;
            opacity: 1 !important;
        }

        .text-secondary {
            color: var(--pos-text-muted) !important;
        }

        .badge {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Inputs */
        .form-control,
        .form-select {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--pos-border) !important;
            color: #ffffff !important;
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pos-accent) !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        }

        /* Cart Items */
        .cart-item-anim {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Scrollbars */
        .scrollbar-custom::-webkit-scrollbar {
            width: 5px;
        }

        .scrollbar-custom::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-custom::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .scrollbar-custom::-webkit-scrollbar-thumb:hover {
            background: var(--pos-accent);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
        }

        .display-4 {
            text-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection