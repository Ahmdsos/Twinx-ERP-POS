<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نقطة البيع - Twinx POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f1f5f9;
            --sidebar-width: 350px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: var(--dark);
            color: #fff;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .pos-container {
            display: flex;
            height: 100vh;
        }

        /* Products Panel */
        .products-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #0f172a;
            padding: 1rem;
        }

        .search-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .search-bar input {
            flex: 1;
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 12px;
            padding: 12px 20px;
            color: #fff;
            font-size: 1.1rem;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
        }

        .search-bar .btn-scan {
            background: var(--secondary);
            border: none;
            border-radius: 12px;
            padding: 0 20px;
            font-size: 1.5rem;
        }

        /* Categories */
        .categories-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .category-btn {
            background: #1e293b;
            border: 2px solid #334155;
            color: #94a3b8;
            padding: 10px 20px;
            border-radius: 10px;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* Products Grid */
        .products-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .product-card {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
            border: 2px solid #334155;
            border-radius: 16px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .product-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
        }

        .product-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            background: #334155;
        }

        .product-card .name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-card .price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--success);
        }

        .product-card .stock {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Cart Panel */
        .cart-panel {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border-right: 2px solid #334155;
            display: flex;
            flex-direction: column;
        }

        .cart-header {
            padding: 1rem;
            border-bottom: 2px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h2 {
            margin: 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cart-header .badge {
            background: var(--primary);
            border-radius: 20px;
            padding: 4px 12px;
        }

        /* Customer Selection */
        .customer-select {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #334155;
        }

        .customer-select select {
            width: 100%;
            background: #0f172a;
            border: 2px solid #334155;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
        }

        /* Cart Items */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .cart-item {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .cart-item .info {
            flex: 1;
        }

        .cart-item .name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #e2e8f0;
        }

        .cart-item .price {
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .cart-item .qty-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            background: #334155;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: var(--primary);
        }

        .qty-btn.remove {
            background: var(--danger);
        }

        .qty-value {
            width: 40px;
            text-align: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .item-total {
            font-weight: 700;
            color: var(--success);
            min-width: 70px;
            text-align: left;
        }

        /* Cart Summary */
        .cart-summary {
            padding: 1rem;
            border-top: 2px solid #334155;
            background: #0f172a;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1rem;
        }

        .summary-row.total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success);
            border-top: 2px solid #334155;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }

        /* Action Buttons */
        .cart-actions {
            padding: 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-pay {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: #fff;
            grid-column: span 2;
            font-size: 1.3rem;
            padding: 1.25rem;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-hold {
            background: var(--warning);
            color: #000;
        }

        .btn-clear {
            background: var(--danger);
            color: #fff;
        }

        .btn-held {
            background: var(--secondary);
            color: #fff;
        }

        .btn-exit {
            background: #475569;
            color: #fff;
        }

        /* Payment Modal */
        .modal-content {
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 20px;
            color: #fff;
        }

        .modal-header {
            border-bottom: 1px solid #334155;
        }

        .modal-footer {
            border-top: 1px solid #334155;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .payment-method {
            background: #0f172a;
            border: 2px solid #334155;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .payment-method:hover,
        .payment-method.active {
            border-color: var(--primary);
            background: rgba(79, 70, 229, 0.1);
        }

        .payment-method i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            max-width: 300px;
            margin: 0 auto;
        }

        .numpad button {
            padding: 1.25rem;
            font-size: 1.5rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            background: #334155;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .numpad button:hover {
            background: var(--primary);
        }

        .numpad .num-clear {
            background: var(--danger);
        }

        .numpad .num-enter {
            background: var(--success);
        }

        .amount-display {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            padding: 1rem;
            background: #0f172a;
            border-radius: 16px;
            margin-bottom: 1rem;
            color: var(--success);
        }

        /* Empty cart */
        .empty-cart {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Loading */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: #64748b;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #334155;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Success animation */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .success-content {
            text-align: center;
            animation: scaleIn 0.3s ease;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Shift Status Bar */
        .shift-status-bar {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary);
        }

        .shift-status-bar .shift-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .shift-status-bar .shift-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .shift-badge.open {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .shift-badge.closed {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .shift-status-bar .clock {
            font-size: 1.2rem;
            font-weight: 600;
            font-family: monospace;
            color: var(--secondary);
        }

        .shift-status-bar .cashier-info {
            color: #94a3b8;
        }

        .shift-status-bar .cashier-info strong {
            color: #fff;
        }

        /* Quick Amount Buttons */
        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .quick-amounts button {
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border: 2px solid #334155;
            border-radius: 10px;
            background: #1e293b;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-amounts button:hover {
            background: var(--primary);
            border-color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="pos-container">
        <!-- Cart Panel -->
        <div class="cart-panel">
            <!-- Shift Status Bar -->
            <div class="shift-status-bar">
                <div class="shift-info">
                    <div class="shift-badge" id="shiftBadge">
                        <i class="bi bi-door-closed"></i>
                        <span id="shiftStatusText">وردية مغلقة</span>
                    </div>
                    <div class="clock" id="liveClock">00:00:00</div>
                </div>
                <div class="cashier-info">
                    <i class="bi bi-person-circle me-1"></i>
                    <span>الكاشير:</span>
                    <strong>{{ auth()->user()?->name ?? 'غير معروف' }}</strong>
                </div>
            </div>
            <div class="cart-header">
                <h2><i class="bi bi-cart3"></i> السلة <span class="badge" id="cartCount">0</span></h2>
                <button class="btn btn-sm btn-outline-light" onclick="toggleFullscreen()">
                    <i class="bi bi-fullscreen"></i>
                </button>
                {{-- Customer,Warehouse & Account Selection --}}
                <div class="customer-select">
                    <label>العميل</label>
                    <select id="customer_id">
                        <option value="">عميل عادي (Walk-in)</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="customer-select">
                    <label>المخزن</label>
                    <select id="warehouse_id">
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="customer-select">
                    <label>حساب الدفع</label>
                    <select id="payment_account_id">
                        @foreach ($paymentAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->name }} ({{ $account->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Warehouse Selection -->
                <div class="customer-select" style="margin-top: 0.5rem;">
                    <select id="warehouseSelect">
                        <option value="">المخزن الرئيسي</option>
                        @isset($warehouses)
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div class="cart-items" id="cartItems">
                    <div class="empty-cart">
                        <i class="bi bi-cart-x"></i>
                        <p>السلة فارغة</p>
                        <small>اضغط على منتج لإضافته</small>
                    </div>
                </div>

                <!-- Invoice Discount Section -->
                <div class="invoice-discount"
                    style="padding: 0.75rem; background: #0f172a; border-top: 1px solid #334155;">
                    <div class="d-flex gap-2 align-items-center">
                        <label class="text-muted small mb-0">خصم الفاتورة:</label>
                        <div class="input-group input-group-sm" style="max-width: 150px;">
                            <input type="number" class="form-control bg-dark text-white border-secondary"
                                id="discountAmount" value="0" min="0" step="0.01" onchange="updateDiscount()">
                            <select class="form-select bg-dark text-white border-secondary" id="discountType"
                                onchange="updateDiscount()" style="max-width: 60px;">
                                <option value="percent">%</option>
                                <option value="fixed">ج.م</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>المجموع الفرعي</span>
                        <span id="subtotal">0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>الضريبة (14%)</span>
                        <span id="taxAmount">0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>الخصم</span>
                        <span id="discount">0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>الإجمالي</span>
                        <span id="total">0.00</span>
                    </div>
                </div>

                <div class="cart-actions">
                    <button class="btn-action btn-hold" onclick="holdSale()">
                        <i class="bi bi-pause-circle"></i> تعليق
                    </button>
                    <button class="btn-action btn-clear" onclick="clearCart()">
                        <i class="bi bi-trash"></i> مسح
                    </button>
                    <button class="btn-action btn-held" onclick="showHeldSales()">
                        <i class="bi bi-clock-history"></i> المعلقة
                    </button>
                    <button class="btn-action btn-exit" onclick="exitPOS()">
                        <i class="bi bi-box-arrow-right"></i> خروج
                    </button>
                    <button class="btn-action btn-pay" onclick="showPaymentModal()" id="btnPay" disabled>
                        <i class="bi bi-credit-card"></i> دفع
                    </button>
                </div>
            </div>

            <!-- Products Panel -->
            <div class="products-panel">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="ابحث بالاسم أو الباركود أو SKU..." autofocus>
                    <button class="btn-scan" onclick="focusBarcode()">
                        <i class="bi bi-upc-scan"></i>
                    </button>
                </div>

                <div class="categories-bar">
                    <button class="category-btn active" data-category=""
                        onclick="filterCategory(this, '')">الكل</button>
                    @foreach($categories as $category)
                        <button class="category-btn" data-category="{{ $category->id }}"
                            onclick="filterCategory(this, {{ $category->id }})">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="products-grid" id="productsGrid">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إتمام الدفع</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">طريقة الدفع</h6>
                                <div class="payment-methods">
                                    <div class="payment-method active" onclick="selectPayment('cash')">
                                        <i class="bi bi-cash-stack text-success"></i>
                                        <span>نقدي</span>
                                    </div>
                                    <div class="payment-method" onclick="selectPayment('card')">
                                        <i class="bi bi-credit-card text-primary"></i>
                                        <span>بطاقة</span>
                                    </div>
                                    <div class="payment-method" onclick="selectPayment('credit')">
                                        <i class="bi bi-person-badge text-warning"></i>
                                        <span>آجل</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">المبلغ المستحق</label>
                                    <div class="amount-display" id="modalTotal">0.00</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">المبلغ المدفوع</label>
                                    <input type="number" class="form-control form-control-lg" id="amountPaid"
                                        step="0.01" min="0">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">الباقي</label>
                                    <div class="amount-display" id="changeAmount" style="color: var(--warning);">0.00
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">لوحة الأرقام</h6>
                                <div class="numpad">
                                    <button onclick="numpadInput('7')">7</button>
                                    <button onclick="numpadInput('8')">8</button>
                                    <button onclick="numpadInput('9')">9</button>
                                    <button onclick="numpadInput('4')">4</button>
                                    <button onclick="numpadInput('5')">5</button>
                                    <button onclick="numpadInput('6')">6</button>
                                    <button onclick="numpadInput('1')">1</button>
                                    <button onclick="numpadInput('2')">2</button>
                                    <button onclick="numpadInput('3')">3</button>
                                    <button onclick="numpadInput('0')">0</button>
                                    <button onclick="numpadInput('.')">.</button>
                                    <button class="num-clear" onclick="numpadClear()">C</button>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-outline-light" onclick="setExactAmount()">المبلغ
                                        بالضبط</button>
                                </div>

                                <h6 class="mt-3 mb-2">مبالغ سريعة</h6>
                                <div class="quick-amounts">
                                    <button onclick="setQuickAmount(50)">50</button>
                                    <button onclick="setQuickAmount(100)">100</button>
                                    <button onclick="setQuickAmount(200)">200</button>
                                    <button onclick="setQuickAmount(500)">500</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-success btn-lg" onclick="processPayment()">
                            <i class="bi bi-check-circle"></i> إتمام الدفع
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Split Payment Modal -->
        <div class="modal fade" id="splitPaymentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pie-chart me-2"></i>تقسيم الدفع</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <div class="d-flex justify-content-between">
                                <span>إجمالي الفاتورة:</span>
                                <strong id="splitTotal">0.00 ج.م</strong>
                            </div>
                        </div>

                        <div id="splitPayments">
                            <div class="split-payment-row mb-3 p-3 rounded" style="background: #1e293b;">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small text-muted">طريقة الدفع</label>
                                        <select class="form-select bg-dark text-white border-secondary split-method">
                                            <option value="cash">نقدي</option>
                                            <option value="card">بطاقة</option>
                                            <option value="bank">تحويل بنكي</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small text-muted">المبلغ</label>
                                        <input type="number"
                                            class="form-control bg-dark text-white border-secondary split-amount"
                                            step="0.01" min="0" onchange="updateSplitRemaining()">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button class="btn btn-danger w-100" onclick="removeSplitRow(this)" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-outline-primary w-100 mb-3" onclick="addSplitRow()">
                            <i class="bi bi-plus-circle me-2"></i>إضافة طريقة دفع أخرى
                        </button>

                        <div class="alert" id="splitRemainingAlert" style="background: #334155;">
                            <div class="d-flex justify-content-between">
                                <span>المتبقي:</span>
                                <strong id="splitRemaining">0.00 ج.م</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-success" onclick="processSplitPayment()"
                            id="btnConfirmSplit">
                            <i class="bi bi-check-circle me-2"></i>تأكيد الدفع
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Modal -->
        <div class="modal fade" id="receiptPreviewModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>معاينة الإيصال</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="receiptContent"
                            style="background:#fff; color:#000; padding:20px; font-family:monospace; font-size:12px;">
                            <!-- Receipt will be generated here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="button" class="btn btn-primary" onclick="printReceipt()">
                            <i class="bi bi-printer me-2"></i>طباعة
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Panel (Floating) -->
        <div class="recent-transactions-panel" id="recentTransactionsPanel"
            style="display:none; position:fixed; top:80px; left:20px; width:350px; max-height:400px; background:#1e293b; border:2px solid #334155; border-radius:16px; z-index:1000; overflow:hidden;">
            <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>آخر المعاملات</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleRecentTransactions()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="p-2" id="recentTransactionsList" style="max-height:320px; overflow-y:auto;">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-hourglass"></i> جاري التحميل...
                </div>
            </div>
        </div>

        <!-- Success Overlay (hidden) -->
        <div class="success-overlay" id="successOverlay" style="display: none;">
            <div class="success-content">
                <div class="success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h2>تم إتمام العملية بنجاح!</h2>
                <p id="successInvoice"></p>
                <p id="successChange" style="font-size: 1.5rem; color: var(--warning);"></p>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // State
            let cart = [];
            let products = [];
            let selectedCategory = '';
            let paymentMethod = 'cash';
            let paymentModal;

            // Initialize
            document.addEventListener('DOMContentLoaded', () => {
                paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                loadProducts();
                setupBarcodeScanner();
                startLiveClock();
                updateShiftStatus();
                setupKeyboardShortcuts();

                document.getElementById('searchInput').addEventListener('input', debounce(searchProducts, 300));
                document.getElementById('amountPaid').addEventListener('input', calculateChange);
            });

            // ============ Keyboard Shortcuts ============
            function setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Skip if typing in an input field
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                        if (e.key === 'Escape') {
                            e.target.blur();
                            return;
                        }
                        return;
                    }

                    switch (e.key) {
                        case 'F1':
                            e.preventDefault();
                            showShortcutsHelp();
                            break;
                        case 'F2':
                            e.preventDefault();
                            document.getElementById('searchInput').focus();
                            break;
                        case 'F3':
                            e.preventDefault();
                            document.getElementById('customerSelect').focus();
                            break;
                        case 'F5':
                            e.preventDefault();
                            clearCart();
                            break;
                        case 'F8':
                            e.preventDefault();
                            holdSale();
                            break;
                        case 'F10':
                            e.preventDefault();
                            if (cart.length > 0) setExactAmount();
                            break;
                        case 'F12':
                            e.preventDefault();
                            if (cart.length > 0) showPaymentModal();
                            break;
                        case 'Escape':
                            e.preventDefault();
                            // Close any open modals
                            document.querySelectorAll('.modal.show').forEach(modal => {
                                bootstrap.Modal.getInstance(modal)?.hide();
                            });
                            break;
                    }
                });
            }

            // Show keyboard shortcuts help modal
            function showShortcutsHelp() {
                const shortcuts = `
                <div class="table-responsive">
                    <table class="table table-sm table-dark">
                        <thead><tr><th>اختصار</th><th>الوظيفة</th></tr></thead>
                        <tbody>
                            <tr><td><kbd>F1</kbd></td><td>عرض الاختصارات</td></tr>
                            <tr><td><kbd>F2</kbd></td><td>البحث السريع</td></tr>
                            <tr><td><kbd>F3</kbd></td><td>اختيار العميل</td></tr>
                            <tr><td><kbd>F5</kbd></td><td>مسح السلة</td></tr>
                            <tr><td><kbd>F8</kbd></td><td>تعليق البيع</td></tr>
                            <tr><td><kbd>F10</kbd></td><td>المبلغ بالضبط</td></tr>
                            <tr><td><kbd>F12</kbd></td><td>فتح الدفع</td></tr>
                            <tr><td><kbd>ESC</kbd></td><td>إغلاق/إلغاء</td></tr>
                        </tbody>
                    </table>
                </div>
            `;

                // Create and show modal
                const modalHtml = `
                <div class="modal fade" id="shortcutsModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-keyboard me-2"></i>اختصارات لوحة المفاتيح</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">${shortcuts}</div>
                        </div>
                    </div>
                </div>
            `;

                // Remove existing modal if any
                document.getElementById('shortcutsModal')?.remove();
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                new bootstrap.Modal(document.getElementById('shortcutsModal')).show();
            }

            // Live Clock
            function startLiveClock() {
                function updateClock() {
                    const now = new Date();
                    const hours = now.getHours().toString().padStart(2, '0');
                    const mins = now.getMinutes().toString().padStart(2, '0');
                    const secs = now.getSeconds().toString().padStart(2, '0');
                    document.getElementById('liveClock').textContent = `${hours}:${mins}:${secs}`;
                }
                updateClock();
                setInterval(updateClock, 1000);
            }

            // Update Shift Status from API
            async function updateShiftStatus() {
                try {
                    const response = await fetch('/pos/shift/status');
                    const data = await response.json();
                    const badge = document.getElementById('shiftBadge');
                    const text = document.getElementById('shiftStatusText');

                    if (data.is_open) {
                        badge.className = 'shift-badge open';
                        badge.querySelector('i').className = 'bi bi-door-open';
                        text.textContent = 'وردية مفتوحة';
                    } else {
                        badge.className = 'shift-badge closed';
                        badge.querySelector('i').className = 'bi bi-door-closed';
                        text.textContent = 'وردية مغلقة';
                    }
                } catch (e) {
                    console.log('Shift status check failed');
                }
            }

            // Quick Amount Buttons
            function setQuickAmount(amount) {
                document.getElementById('amountPaid').value = amount;
                calculateChange();
            }

            // ============ Receipt Preview Functions ============
            let receiptPreviewModal;

            function showReceiptPreview(invoiceData = null) {
                if (!receiptPreviewModal) {
                    receiptPreviewModal = new bootstrap.Modal(document.getElementById('receiptPreviewModal'));
                }

                const content = document.getElementById('receiptContent');
                const now = new Date();
                const dateStr = now.toLocaleDateString('ar-EG');
                const timeStr = now.toLocaleTimeString('ar-EG');

                // Use current cart if no invoice data provided
                const items = invoiceData?.items || cart;
                const total = invoiceData?.total || parseFloat(document.getElementById('total').textContent);
                const invoiceNo = invoiceData?.invoice_number || 'معاينة';

                let itemsHtml = items.map(item => `
                <tr>
                    <td style="text-align:right;">${item.name}</td>
                    <td style="text-align:center;">${item.quantity}</td>
                    <td style="text-align:left;">${((item.price * item.quantity) - (item.discount || 0)).toFixed(2)}</td>
                </tr>
            `).join('');

                content.innerHTML = `
                <div style="text-align:center; border-bottom:1px dashed #000; padding-bottom:10px; margin-bottom:10px;">
                    <h4 style="margin:0;">Twinx POS</h4>
                    <small>فاتورة رقم: ${invoiceNo}</small><br>
                    <small>${dateStr} - ${timeStr}</small>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px dashed #000;">
                            <th style="text-align:right;">الصنف</th>
                            <th style="text-align:center;">الكمية</th>
                            <th style="text-align:left;">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
                <div style="border-top:1px dashed #000; margin-top:10px; padding-top:10px; text-align:left;">
                    <strong style="font-size:16px;">الإجمالي: ${total.toFixed(2)} ج.م</strong>
                </div>
                <div style="text-align:center; margin-top:15px; font-size:10px; color:#666;">
                    شكراً لتعاملكم معنا
                </div>
            `;

                receiptPreviewModal.show();
            }

            function printReceipt() {
                const content = document.getElementById('receiptContent').innerHTML;
                const printWindow = window.open('', '_blank', 'width=300,height=500');
                printWindow.document.write(`
                <html dir="rtl">
                <head><title>إيصال</title></head>
                <body style="font-family:monospace; font-size:12px; padding:10px;">
                    ${content}
                </body>
                </html>
            `);
                printWindow.document.close();
                printWindow.print();
                printWindow.close();
            }

            // ============ Recent Transactions Functions ============
            let recentTransactionsVisible = false;

            function toggleRecentTransactions() {
                const panel = document.getElementById('recentTransactionsPanel');
                recentTransactionsVisible = !recentTransactionsVisible;
                panel.style.display = recentTransactionsVisible ? 'block' : 'none';

                if (recentTransactionsVisible) {
                    loadRecentTransactions();
                }
            }

            async function loadRecentTransactions() {
                const list = document.getElementById('recentTransactionsList');
                list.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div></div>';

                try {
                    const response = await fetch('/pos/recent-transactions');
                    const data = await response.json();

                    if (data.transactions && data.transactions.length > 0) {
                        list.innerHTML = data.transactions.map(t => `
                        <div class="p-2 mb-2 rounded" style="background:#0f172a; cursor:pointer;" onclick="viewTransaction(${t.id})">
                            <div class="d-flex justify-content-between">
                                <span class="text-info">#${t.invoice_number}</span>
                                <span class="text-success">${parseFloat(t.total).toFixed(2)} ج.م</span>
                            </div>
                            <small class="text-muted">${t.created_at}</small>
                        </div>
                    `).join('');
                    } else {
                        list.innerHTML = '<div class="text-center text-muted py-4">لا توجد معاملات حديثة</div>';
                    }
                } catch (e) {
                    list.innerHTML = '<div class="text-center text-danger py-4">خطأ في تحميل البيانات</div>';
                }
            }

            function viewTransaction(id) {
                window.open('/sales-invoices/' + id, '_blank');
            }

            // ============ Shift Report Quick View ============
            async function showShiftReport() {
                try {
                    const response = await fetch('/pos/shift/report');
                    const data = await response.json();

                    const reportHtml = `
                    <div class="modal fade" id="shiftReportModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-bar-chart me-2"></i>تقرير الوردية</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="p-3 rounded text-center" style="background:#0f172a;">
                                                <div class="text-muted small">عدد الفواتير</div>
                                                <div class="h4 text-info mb-0">${data.invoices_count || 0}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 rounded text-center" style="background:#0f172a;">
                                                <div class="text-muted small">إجمالي المبيعات</div>
                                                <div class="h4 text-success mb-0">${(data.total_sales || 0).toFixed(2)}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 rounded text-center" style="background:#0f172a;">
                                                <div class="text-muted small">نقدي</div>
                                                <div class="h5 text-warning mb-0">${(data.cash_total || 0).toFixed(2)}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 rounded text-center" style="background:#0f172a;">
                                                <div class="text-muted small">بطاقات</div>
                                                <div class="h5 text-primary mb-0">${(data.card_total || 0).toFixed(2)}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                    document.getElementById('shiftReportModal')?.remove();
                    document.body.insertAdjacentHTML('beforeend', reportHtml);
                    new bootstrap.Modal(document.getElementById('shiftReportModal')).show();
                } catch (e) {
                    alert('خطأ في تحميل تقرير الوردية');
                }
            }

            // Debounce helper
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Load products
            async function loadProducts(query = '', categoryId = '') {
                const grid = document.getElementById('productsGrid');
                grid.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

                try {
                    const params = new URLSearchParams();
                    if (query) params.append('q', query);
                    if (categoryId) params.append('category_id', categoryId);

                    const response = await fetch(`/pos/search?${params}`);
                    products = await response.json();

                    renderProducts();
                } catch (error) {
                    grid.innerHTML = '<div class="loading">حدث خطأ في تحميل المنتجات</div>';
                }
            }

            // Render products
            function renderProducts() {
                const grid = document.getElementById('productsGrid');

                if (products.length === 0) {
                    grid.innerHTML = '<div class="loading">لا توجد منتجات</div>';
                    return;
                }

                grid.innerHTML = products.map(p => `
                <div class="product-card" onclick="addToCart(${p.id})">
                    <img src="${p.image || '/images/no-image.png'}" alt="${p.name}" onerror="this.src='/images/no-image.png'">
                    <div class="name">${p.name}</div>
                    <div class="price">${p.price.toFixed(2)}</div>
                    <div class="stock">المخزون: ${p.stock}</div>
                </div>
            `).join('');
            }

            // Search products
            function searchProducts() {
                const query = document.getElementById('searchInput').value;
                loadProducts(query, selectedCategory);
            }

            // Filter by category
            function filterCategory(btn, categoryId) {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedCategory = categoryId;
                loadProducts(document.getElementById('searchInput').value, categoryId);
            }

            // Add to cart
            function addToCart(productId) {
                const product = products.find(p => p.id === productId);
                if (!product) return;

                const existingItem = cart.find(item => item.product_id === productId);

                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    cart.push({
                        product_id: productId,
                        name: product.name,
                        price: product.price,
                        quantity: 1,
                        discount: 0,
                        notes: '',
                        image: product.image || null
                    });
                }

                playBeep();
                renderCart();
            }

            // Remove from cart
            function removeFromCart(index) {
                cart.splice(index, 1);
                renderCart();
            }

            // Update quantity
            function updateQuantity(index, delta) {
                cart[index].quantity += delta;
                if (cart[index].quantity <= 0) {
                    removeFromCart(index);
                } else {
                    renderCart();
                }
            }

            // Render cart
            function renderCart() {
                const container = document.getElementById('cartItems');
                const countBadge = document.getElementById('cartCount');
                const btnPay = document.getElementById('btnPay');

                countBadge.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
                btnPay.disabled = cart.length === 0;

                if (cart.length === 0) {
                    container.innerHTML = `
                    <div class="empty-cart">
                        <i class="bi bi-cart-x"></i>
                        <p>السلة فارغة</p>
                        <small>اضغط على منتج لإضافته</small>
                    </div>
                `;
                    updateTotals();
                    return;
                }

                container.innerHTML = cart.map((item, index) => `
                <div class="cart-item">
                    ${item.image ? `<img src="${item.image}" class="cart-item-img" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">` : ''}
                    <div class="info" style="flex:1;">
                        <div class="name">${item.name}</div>
                        <div class="price">
                            <span class="editable-price" onclick="showPriceOverride(${index})" style="cursor:pointer;" title="اضغط لتعديل السعر">
                                ${item.price.toFixed(2)}
                            </span> × ${item.quantity}
                            ${item.notes ? `<i class="bi bi-chat-dots text-info ms-1" title="${item.notes}"></i>` : ''}
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1" style="min-width:32px;">
                        <button class="btn btn-sm btn-outline-secondary p-0" onclick="showLineNotes(${index})" title="ملاحظات">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning p-0" onclick="showLineDiscount(${index})" title="خصم">
                            <i class="bi bi-percent"></i>
                        </button>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQuantity(${index}, -1)">−</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                    <div class="item-total">${((item.price * item.quantity) - (item.discount || 0)).toFixed(2)}</div>
                    <button class="qty-btn remove" onclick="removeFromCart(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `).join('');

                updateTotals();
            }

            // Update totals
            function updateTotals() {
                const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity - item.discount), 0);
                const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);

                // Get invoice discount
                const discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
                const discountType = document.getElementById('discountType').value;

                let invoiceDiscount = 0;
                if (discountType === 'percent') {
                    invoiceDiscount = (subtotal * discountAmount) / 100;
                } else {
                    invoiceDiscount = discountAmount;
                }

                // Calculate tax (14%)
                const taxableAmount = subtotal - invoiceDiscount;
                const taxAmount = taxableAmount * 0.14;

                const total = taxableAmount + taxAmount;

                document.getElementById('subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('taxAmount').textContent = taxAmount.toFixed(2);
                document.getElementById('discount').textContent = (lineDiscounts + invoiceDiscount).toFixed(2);
                document.getElementById('total').textContent = total.toFixed(2);
            }

            // Update discount (called when discount input changes)
            function updateDiscount() {
                updateTotals();
            }

            // ============ Line Item Edit Functions ============

            // Show line notes prompt
            function showLineNotes(index) {
                const item = cart[index];
                const notes = prompt('ملاحظات على "' + item.name + '":', item.notes || '');
                if (notes !== null) {
                    cart[index].notes = notes;
                    renderCart();
                }
            }

            // Show price override prompt
            function showPriceOverride(index) {
                const item = cart[index];
                const newPrice = prompt('السعر الجديد لـ "' + item.name + '":', item.price.toFixed(2));
                if (newPrice !== null) {
                    const price = parseFloat(newPrice);
                    if (!isNaN(price) && price >= 0) {
                        cart[index].price = price;
                        renderCart();
                    } else {
                        alert('سعر غير صالح');
                    }
                }
            }

            // Show line discount prompt
            function showLineDiscount(index) {
                const item = cart[index];
                const discountStr = prompt('خصم "' + item.name + '" (ج.م):', (item.discount || 0).toFixed(2));
                if (discountStr !== null) {
                    const discount = parseFloat(discountStr);
                    if (!isNaN(discount) && discount >= 0) {
                        cart[index].discount = discount;
                        renderCart();
                    } else {
                        alert('خصم غير صالح');
                    }
                }
            }

            // ============ Split Payment Functions ============
            let splitPaymentModal;

            function initSplitPaymentModal() {
                if (!splitPaymentModal) {
                    splitPaymentModal = new bootstrap.Modal(document.getElementById('splitPaymentModal'));
                }
            }

            function showSplitPaymentModal() {
                if (cart.length === 0) return;
                initSplitPaymentModal();

                const total = parseFloat(document.getElementById('total').textContent) || 0;
                document.getElementById('splitTotal').textContent = total.toFixed(2) + ' ج.م';

                // Reset split payments to single row
                document.getElementById('splitPayments').innerHTML = `
                <div class="split-payment-row mb-3 p-3 rounded" style="background: #1e293b;">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label small text-muted">طريقة الدفع</label>
                            <select class="form-select bg-dark text-white border-secondary split-method">
                                <option value="cash">نقدي</option>
                                <option value="card">بطاقة</option>
                                <option value="bank">تحويل بنكي</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small text-muted">المبلغ</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary split-amount" 
                                step="0.01" min="0" value="${total.toFixed(2)}" onchange="updateSplitRemaining()">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-danger w-100" onclick="removeSplitRow(this)" disabled>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

                updateSplitRemaining();
                splitPaymentModal.show();
            }

            function addSplitRow() {
                const container = document.getElementById('splitPayments');
                const rows = container.querySelectorAll('.split-payment-row');

                // Enable delete on existing rows
                rows.forEach(row => {
                    row.querySelector('button').disabled = false;
                });

                const newRow = document.createElement('div');
                newRow.className = 'split-payment-row mb-3 p-3 rounded';
                newRow.style.background = '#1e293b';
                newRow.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label small text-muted">طريقة الدفع</label>
                        <select class="form-select bg-dark text-white border-secondary split-method">
                            <option value="cash">نقدي</option>
                            <option value="card">بطاقة</option>
                            <option value="bank">تحويل بنكي</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small text-muted">المبلغ</label>
                        <input type="number" class="form-control bg-dark text-white border-secondary split-amount" 
                            step="0.01" min="0" value="0" onchange="updateSplitRemaining()">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-danger w-100" onclick="removeSplitRow(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
                container.appendChild(newRow);
                updateSplitRemaining();
            }

            function removeSplitRow(btn) {
                const row = btn.closest('.split-payment-row');
                row.remove();

                const rows = document.querySelectorAll('.split-payment-row');
                if (rows.length === 1) {
                    rows[0].querySelector('button').disabled = true;
                }
                updateSplitRemaining();
            }

            function updateSplitRemaining() {
                const total = parseFloat(document.getElementById('total').textContent) || 0;
                const amounts = document.querySelectorAll('.split-amount');
                let totalPaid = 0;
                amounts.forEach(input => {
                    totalPaid += parseFloat(input.value) || 0;
                });

                const remaining = total - totalPaid;
                const remainingEl = document.getElementById('splitRemaining');
                const alertEl = document.getElementById('splitRemainingAlert');

                remainingEl.textContent = remaining.toFixed(2) + ' ج.م';

                if (Math.abs(remaining) < 0.01) {
                    alertEl.className = 'alert alert-success';
                    document.getElementById('btnConfirmSplit').disabled = false;
                } else if (remaining > 0) {
                    alertEl.className = 'alert alert-warning';
                    document.getElementById('btnConfirmSplit').disabled = true;
                } else {
                    alertEl.className = 'alert alert-danger';
                    document.getElementById('btnConfirmSplit').disabled = true;
                }
            }

            async function processSplitPayment() {
                const total = parseFloat(document.getElementById('total').textContent) || 0;
                const payments = [];

                document.querySelectorAll('.split-payment-row').forEach(row => {
                    const method = row.querySelector('.split-method').value;
                    const amount = parseFloat(row.querySelector('.split-amount').value) || 0;
                    if (amount > 0) {
                        payments.push({ method, amount });
                    }
                });

                const data = {
                    items: cart.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        price: item.price,
                        discount: item.discount
                    })),
                    customer_id: document.getElementById('customerSelect').value || null,
                    warehouse_id: document.getElementById('warehouseSelect').value || null,
                    payment_method: 'split',
                    payments: payments,
                    amount_paid: total,
                    discount: parseFloat(document.getElementById('discountAmount').value) || 0,
                    discount_type: document.getElementById('discountType').value,
                    notes: ''
                };

                try {
                    const response = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        splitPaymentModal.hide();
                        showSuccessOverlay(result.invoice_number, 0);
                        cart = [];
                        renderCart();
                        document.getElementById('discountAmount').value = 0;
                        updateDiscount();
                    } else {
                        alert(result.message || 'حدث خطأ');
                    }
                } catch (e) {
                    console.error(e);
                    alert('حدث خطأ في الاتصال');
                }
            }

            // Clear cart
            function clearCart() {
                if (cart.length === 0) return;
                if (confirm('هل أنت متأكد من مسح السلة؟')) {
                    cart = [];
                    renderCart();
                }
            }

            // Payment modal
            function showPaymentModal() {
                if (cart.length === 0) return;

                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                document.getElementById('modalTotal').textContent = total.toFixed(2);
                document.getElementById('amountPaid').value = total.toFixed(2);
                document.getElementById('changeAmount').textContent = '0.00';

                paymentModal.show();
            }

            // Select payment method
            function selectPayment(method) {
                paymentMethod = method;
                document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
                event.target.closest('.payment-method').classList.add('active');
            }

            // Numpad input
            function numpadInput(value) {
                const input = document.getElementById('amountPaid');
                input.value = (input.value || '') + value;
                calculateChange();
            }

            function numpadClear() {
                document.getElementById('amountPaid').value = '';
                calculateChange();
            }

            function setExactAmount() {
                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                document.getElementById('amountPaid').value = total.toFixed(2);
                calculateChange();
            }

            function calculateChange() {
                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
                const change = paid - total;

                const changeEl = document.getElementById('changeAmount');
                changeEl.textContent = Math.max(0, change).toFixed(2);
                changeEl.style.color = change >= 0 ? 'var(--success)' : 'var(--danger)';
            }

            // Process payment
            async function processPayment() {
                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

                if (paymentMethod !== 'credit' && amountPaid < total) {
                    alert('المبلغ المدفوع أقل من الإجمالي');
                    return;
                }

                const data = {
                    items: cart.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        price: item.price,
                        discount: item.discount
                    })),
                    customer_id: document.getElementById('customerSelect').value || null,
                    payment_method: paymentMethod,
                    amount_paid: amountPaid,
                    discount: 0,
                    notes: ''
                };

                try {
                    const response = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        paymentModal.hide();
                        showSuccess(result.invoice);
                        cart = [];
                        renderCart();
                    } else {
                        alert(result.message || 'حدث خطأ');
                    }
                } catch (error) {
                    alert('حدث خطأ في الاتصال');
                }
            }

            // Show success
            function showSuccess(invoice) {
                const overlay = document.getElementById('successOverlay');
                document.getElementById('successInvoice').textContent = `رقم الفاتورة: ${invoice.number}`;
                document.getElementById('successChange').textContent = invoice.change > 0 ? `الباقي: ${invoice.change.toFixed(2)}` : '';

                overlay.style.display = 'flex';
                playSuccess();

                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 3000);
            }

            // Hold sale
            async function holdSale() {
                if (cart.length === 0) return;

                try {
                    const response = await fetch('/pos/hold', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            items: cart,
                            customer_id: document.getElementById('customerSelect').value || null,
                            notes: ''
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('تم تعليق الفاتورة');
                        cart = [];
                        renderCart();
                    }
                } catch (error) {
                    alert('حدث خطأ');
                }
            }

            // Show held sales
            async function showHeldSales() {
                try {
                    const response = await fetch('/pos/held');
                    const heldSales = await response.json();

                    if (heldSales.length === 0) {
                        alert('لا توجد فواتير معلقة');
                        return;
                    }

                    const choice = prompt(`الفواتير المعلقة:\n${heldSales.map((s, i) => `${i + 1}. ${s.created_at} (${s.items.length} منتج)`).join('\n')}\n\nاختر رقم الفاتورة:`);

                    if (choice && heldSales[parseInt(choice) - 1]) {
                        const sale = heldSales[parseInt(choice) - 1];
                        await resumeHeldSale(sale.id);
                    }
                } catch (error) {
                    alert('حدث خطأ');
                }
            }

            async function resumeHeldSale(holdId) {
                try {
                    const response = await fetch('/pos/resume', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ hold_id: holdId })
                    });

                    const sale = await response.json();

                    if (!sale.error) {
                        cart = sale.items;
                        if (sale.customer_id) {
                            document.getElementById('customerSelect').value = sale.customer_id;
                        }
                        renderCart();
                    }
                } catch (error) {
                    alert('حدث خطأ');
                }
            }

            // Barcode scanner
            function setupBarcodeScanner() {
                let barcodeBuffer = '';
                let lastKeyTime = 0;

                document.addEventListener('keydown', async (e) => {
                    const currentTime = new Date().getTime();

                    // If focused on search input, let it handle normally
                    if (document.activeElement.id === 'searchInput') return;

                    // Fast consecutive keypresses indicate barcode scanner
                    if (currentTime - lastKeyTime < 50) {
                        barcodeBuffer += e.key;
                    } else {
                        barcodeBuffer = e.key;
                    }

                    lastKeyTime = currentTime;

                    if (e.key === 'Enter' && barcodeBuffer.length > 3) {
                        const barcode = barcodeBuffer.replace('Enter', '');
                        await scanBarcode(barcode);
                        barcodeBuffer = '';
                    }
                });
            }

            async function scanBarcode(barcode) {
                try {
                    const response = await fetch(`/pos/barcode?barcode=${encodeURIComponent(barcode)}`);

                    if (response.ok) {
                        const product = await response.json();

                        // Add to products array if not exists
                        if (!products.find(p => p.id === product.id)) {
                            products.push(product);
                        }

                        addToCart(product.id);
                    } else {
                        playError();
                    }
                } catch (error) {
                    playError();
                }
            }

            function focusBarcode() {
                document.getElementById('searchInput').focus();
            }

            // Sound effects
            function playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    osc.frequency.value = 800;
                    osc.connect(ctx.destination);
                    osc.start();
                    setTimeout(() => osc.stop(), 100);
                } catch (e) { }
            }

            function playSuccess() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    osc.frequency.value = 1000;
                    osc.connect(ctx.destination);
                    osc.start();
                    setTimeout(() => { osc.frequency.value = 1200; }, 100);
                    setTimeout(() => osc.stop(), 200);
                } catch (e) { }
            }

            function playError() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    osc.frequency.value = 300;
                    osc.connect(ctx.destination);
                    osc.start();
                    setTimeout(() => osc.stop(), 300);
                } catch (e) { }
            }

            // Fullscreen
            function toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }

            // Exit POS
            function exitPOS() {
                if (cart.length > 0 && !confirm('هناك منتجات في السلة. هل تريد الخروج؟')) {
                    return;
                }
                window.location.href = '/dashboard';
            }
        </script>
</body>

</html>