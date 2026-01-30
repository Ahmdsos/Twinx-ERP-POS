<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نقطة البيع - Twinx POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --secondary: #22d3ee;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-dark: #0f0f23;
            --bg-card: #1a1a2e;
            --bg-input: #16213e;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --cart-width: 380px;
            --glow-primary: rgba(99, 102, 241, 0.4);
        }

        [data-theme="light"] {
            --bg-dark: #f1f5f9;
            --bg-card: #ffffff;
            --bg-input: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
        }

        /* Main Layout */
        .pos-app {
            display: grid;
            grid-template-columns: 1fr var(--cart-width);
            height: 100vh;
            gap: 0;
        }

        /* ===================== PRODUCTS SECTION ===================== */
        .products-section {
            display: flex;
            flex-direction: column;
            background: var(--bg-dark);
            padding: 1rem;
            overflow: hidden;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, var(--bg-card) 0%, rgba(99, 102, 241, 0.1) 100%);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-light);
        }

        .logo i {
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 50px;
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px var(--glow-primary);
        }

        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.25rem;
        }

        .top-bar-btn {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .top-bar-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Categories */
        .categories {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .categories::-webkit-scrollbar {
            display: none;
        }

        .cat-btn {
            padding: 10px 20px;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 25px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cat-btn:hover,
        .cat-btn.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--glow-primary);
        }

        /* Products Grid */
        .products-wrapper {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .products-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        .products-wrapper::-webkit-scrollbar-track {
            background: var(--bg-card);
            border-radius: 3px;
        }

        .products-wrapper::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 3px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            padding-bottom: 1rem;
        }

        .product-card {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.2);
        }

        .product-card:hover::before {
            opacity: 1;
        }

        .product-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 0.75rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
        }

        .product-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--success);
        }

        .product-stock {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .product-stock.low {
            color: var(--warning);
        }

        .product-stock.out {
            color: var(--danger);
        }

        /* ===================== CART SECTION ===================== */
        .cart-section {
            background: linear-gradient(180deg, var(--bg-card) 0%, #12122a 100%);
            border-right: 2px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Cart Header */
        .cart-header {
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .cart-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .cart-header-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cart-header-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Customer & Warehouse Select */
        .cart-selects {
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .cart-select {
            width: 100%;
            padding: 10px 15px;
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .cart-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Cart Items */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem;
        }

        .cart-empty {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
        }

        .cart-empty i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--bg-input);
            border-radius: 12px;
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            border-color: var(--primary);
        }

        .cart-item-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }

        .cart-item-info {
            flex: 1;
            min-width: 0;
        }

        .cart-item-name {
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-price {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .qty-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        .qty-value {
            width: 35px;
            text-align: center;
            font-weight: 600;
        }

        .cart-item-total {
            font-weight: 700;
            color: var(--success);
            min-width: 70px;
            text-align: left;
        }

        .cart-item-remove {
            width: 30px;
            height: 30px;
            background: rgba(239, 68, 68, 0.1);
            border: none;
            border-radius: 8px;
            color: var(--danger);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .cart-item-remove:hover {
            background: var(--danger);
            color: white;
        }

        /* Cart Summary */
        .cart-summary {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border-top: 1px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .summary-row.total {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--success);
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 2px dashed var(--border-color);
        }

        /* Cart Actions */
        .cart-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            padding: 0.75rem;
            background: var(--bg-dark);
        }

        .action-btn {
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .action-btn i {
            font-size: 1.25rem;
        }

        .action-btn.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .action-btn.danger:hover {
            background: var(--danger);
            color: white;
        }

        .action-btn.secondary {
            background: rgba(148, 163, 184, 0.1);
            color: var(--text-secondary);
        }

        .action-btn.secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-dark);
        }

        /* Pay Button */
        .pay-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            border: none;
            border-radius: 0;
            color: white;
            font-family: 'Cairo', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .pay-btn:disabled {
            background: var(--border-color);
            cursor: not-allowed;
        }

        .pay-btn:not(:disabled):hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        /* Loading State */
        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: var(--text-secondary);
        }

        .loading-spinner i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Modal Styles */
        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            border-radius: 20px 20px 0 0;
            border: none;
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .payment-method {
            padding: 1.5rem 1rem;
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover,
        .payment-method.active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        .payment-method i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .payment-method span {
            font-weight: 600;
        }

        /* Amount Display */
        .amount-display {
            background: var(--bg-dark);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .amount-display .label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .amount-display .value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--success);
        }

        .amount-display.change .value {
            color: var(--warning);
        }

        /* Numpad */
        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .numpad button {
            padding: 1.25rem;
            font-size: 1.5rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            background: var(--bg-input);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
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

        /* Quick Amounts */
        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .quick-amounts button {
            padding: 0.75rem;
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quick-amounts button:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .pos-app {
                grid-template-columns: 1fr 320px;
            }

            :root {
                --cart-width: 320px;
            }
        }

        @media (max-width: 768px) {
            .pos-app {
                grid-template-columns: 1fr;
            }

            .cart-section {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="pos-app">
        <!-- Products Section (Left) -->
        <div class="products-section">
            <!-- Shift Status Bar -->
            <div class="shift-status-bar d-flex justify-content-between align-items-center px-3 py-2"
                style="background: rgba(0,0,0,0.3); border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-3">
                    <div id="shiftBadge" class="badge bg-success py-2 px-3">
                        <i class="bi bi-door-open-fill me-1"></i> وردية مفتوحة
                    </div>
                    <div class="text-secondary small">
                        <i class="bi bi-clock me-1"></i> <span id="liveClock">00:00:00</span>
                    </div>
                </div>
                <div class="text-secondary small">
                    <i class="bi bi-person-badge me-1"></i> الكاشير: <strong>{{ auth()->user()->name }}</strong>
                </div>
            </div>

            <div class="top-bar">
                <div class="logo">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Twinx POS</span>
                </div>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="ابحث بالاسم أو الباركود (F1)" autofocus>
                </div>
                <div class="d-flex gap-2">
                    <button class="top-bar-btn" onclick="focusBarcode()" title="بحث (F1)">
                        <i class="bi bi-upc-scan"></i>
                    </button>
                    <button class="top-bar-btn" onclick="rePrintLast()" title="آخر فاتورة">
                        <i class="bi bi-printer"></i>
                    </button>
                    <button class="top-bar-btn" onclick="openDrawer()" title="فتح الدرج">
                        <i class="bi bi-safe2"></i>
                    </button>
                    <button class="top-bar-btn" onclick="showReturnsModal()" title="مرتجع">
                        <i class="bi bi-arrow-return-left"></i>
                    </button>
                    <button class="top-bar-btn" onclick="showDailyReport()" title="تقرير اليوم">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </button>
                    <button class="top-bar-btn" onclick="toggleFullscreen()" title="شاشة كاملة (F11)">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <button class="top-bar-btn" onclick="toggleTheme()" id="themeToggle" title="وضع الإضاءة">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                    <button class="top-bar-btn" onclick="exitPOS()" title="خروج">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                    <button class="top-bar-btn text-danger" onclick="closeShift()" title="إغلاق الوردية">
                        <i class="bi bi-power"></i>
                    </button>
                </div>
            </div>

            <div class="categories">
                <button class="cat-btn active" data-category="" onclick="filterCategory(this, '')">
                    <i class="bi bi-grid-fill"></i> الكل
                </button>
                @foreach($categories as $category)
                    <button class="cat-btn" data-category="{{ $category->id }}"
                        onclick="filterCategory(this, {{ $category->id }})">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <div class="products-wrapper">
                <div class="products-grid" id="productsGrid">
                    <div class="loading-spinner">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section (Right) -->
        <div class="cart-section">
            <div class="cart-header">
                <div class="cart-title">
                    <i class="bi bi-cart3"></i>
                    <span>السلة</span>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
                <button class="cart-header-btn" onclick="clearCart()" title="مسح السلة">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

            <div class="cart-selects">
                <select class="cart-select" id="customerSelect">
                    <option value="">👤 عميل عام (نقدي)</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->code }} - {{ $customer->name }}</option>
                    @endforeach
                </select>
                <select class="cart-select" id="warehouseSelect">
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty">
                    <i class="bi bi-cart-x"></i>
                    <p>السلة فارغة</p>
                    <small>اضغط على منتج لإضافته</small>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>المجموع الفرعي</span>
                    <span id="subtotal">0.00 ج.م</span>
                </div>
                <!-- Invoice Discount Section -->
                <div class="summary-row align-items-center">
                    <span>الخصم (الفاتورة)</span>
                    <div class="d-flex gap-1" style="max-width: 140px;">
                        <input type="number" id="invoiceDiscount" value="0"
                            class="form-control form-control-sm bg-dark text-white border-secondary text-center"
                            onchange="updateTotals()">
                        <select id="invoiceDiscountType"
                            class="form-select form-select-sm bg-dark text-white border-secondary"
                            onchange="updateTotals()">
                            <option value="fixed">ج.م</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>
                <div class="summary-row">
                    <span>إجمالي الخصم</span>
                    <span id="discount" class="text-danger">- 0.00 ج.م</span>
                </div>
                <div class="summary-row total">
                    <span>الإجمالي</span>
                    <span id="total">0.00 ج.م</span>
                </div>
                <!-- Notes Section -->
                <div class="mt-2">
                    <textarea id="invoiceNotes" class="form-control form-control-sm bg-dark text-white border-secondary"
                        placeholder="ملاحظات الفاتورة..." rows="2"></textarea>
                </div>
            </div>

            <div class="cart-actions">
                <button class="action-btn secondary" onclick="holdSale()">
                    <i class="bi bi-pause-circle"></i>
                    <span>تعليق</span>
                </button>
                <button class="action-btn secondary" onclick="showHeldSales()">
                    <i class="bi bi-clock-history"></i>
                    <span>المعلقة</span>
                </button>
                <button class="action-btn danger" onclick="clearCart()">
                    <i class="bi bi-x-circle"></i>
                    <span>مسح</span>
                </button>
            </div>

            <button class="pay-btn" id="btnPay" onclick="showPaymentModal()" disabled>
                <i class="bi bi-credit-card-2-front"></i>
                <span>إتمام الدفع</span>
            </button>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>إتمام الدفع</h5>
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
                                <div class="payment-method" onclick="selectPayment('split')">
                                    <i class="bi bi-layers-half text-info"></i>
                                    <span>مقسم</span>
                                </div>
                            </div>
                            <div id="splitPaymentRow" class="row g-2 mb-3 d-none">
                                <div class="col-6">
                                    <label class="small">نقدي</label>
                                    <input type="number" id="splitCash"
                                        class="form-control form-control-sm bg-dark text-white border-secondary"
                                        value="0">
                                </div>
                                <div class="col-6">
                                    <label class="small">بطاقة</label>
                                    <input type="number" id="splitCard"
                                        class="form-control form-control-sm bg-dark text-white border-secondary"
                                        value="0">
                                </div>
                            </div>

                            <div class="amount-display">
                                <div class="label">المبلغ المستحق</div>
                                <div class="value" id="modalTotal">0.00</div>
                            </div>

                            <div class="amount-display change">
                                <div class="label">الباقي</div>
                                <div class="value" id="changeAmount">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">المبلغ المدفوع</h6>
                            <input type="number" class="form-control form-control-lg mb-3" id="amountPaid" step="0.01"
                                min="0"
                                style="background: var(--bg-input); border-color: var(--border-color); color: white; font-size: 1.5rem; text-align: center;">

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

                            <div class="quick-amounts">
                                <button onclick="setQuickAmount(50)">50</button>
                                <button onclick="setQuickAmount(100)">100</button>
                                <button onclick="setQuickAmount(200)">200</button>
                                <button onclick="setQuickAmount(500)">500</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--bg-dark); border: none;">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-success btn-lg px-5" onclick="processPayment()">
                        <i class="bi bi-check-circle me-1"></i> إتمام الدفع
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content text-dark" style="background: white; border-radius: 0;">
                <div class="modal-body p-4" id="receiptContent">
                    <!-- Receipt content will be generated here -->
                </div>
                <div class="modal-footer border-0" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
                        <i class="bi bi-printer me-1"></i> طباعة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <audio id="beepSound" src="https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3"></audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // State
        let products = [];
        let cart = [];
        let selectedCategory = '';
        let selectedPaymentMethod = 'cash';

        // Load products on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadProducts();
            setupSearch();
            startClock();
            setupShortcuts();
        });

        // Live Clock
        function startClock() {
            setInterval(() => {
                const now = new Date();
                document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
            }, 1000);
        }

        // Keyboard Shortcuts
        function setupShortcuts() {
            document.addEventListener('keydown', function (e) {
                // F1: Focus Search
                if (e.key === 'F1') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
                // F2: Payment
                if (e.key === 'F2') {
                    e.preventDefault();
                    showPaymentModal();
                }
                // F4: Clear Cart
                if (e.key === 'F4') {
                    e.preventDefault();
                    clearCart();
                }
                // ESC: Close Modals
                if (e.key === 'Escape') {
                    const activeModal = document.querySelector('.modal.show');
                    if (activeModal) {
                        bootstrap.Modal.getInstance(activeModal).hide();
                    }
                }
            });
        }

        // Load products from API
        function loadProducts(search = '', category = '') {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = '<div class="loading-spinner"><i class="bi bi-arrow-repeat"></i></div>';

            fetch(`/pos/search?q=${encodeURIComponent(search)}&category=${category}`)
                .then(res => res.json())
                .then(data => {
                    products = data;
                    renderProducts();
                })
                .catch(err => {
                    console.error('Error loading products:', err);
                    grid.innerHTML = '<div class="loading-spinner">خطأ في تحميل المنتجات</div>';
                });
        }

        // Render products grid
        function renderProducts() {
            const grid = document.getElementById('productsGrid');

            if (products.length === 0) {
                grid.innerHTML = '<div class="loading-spinner">لا توجد منتجات</div>';
                return;
            }

            grid.innerHTML = products.map(p => {
                const stockClass = p.stock <= 0 ? 'out' : p.stock < 10 ? 'low' : '';
                return `
                    <div class="product-card" onclick="addToCart(${p.id})">
                        <div class="product-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="product-name" title="${p.name}">${p.name}</div>
                        <div class="product-price">${p.price.toFixed(2)} ج.م</div>
                        <div class="product-stock ${stockClass}">المخزون: ${p.stock}</div>
                    </div>
                `;
            }).join('');
        }

        // Setup search with debounce
        function setupSearch() {
            let timeout;
            document.getElementById('searchInput').addEventListener('input', function (e) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    loadProducts(e.target.value, selectedCategory);
                }, 300);
            });
        }

        // Filter by category
        function filterCategory(btn, categoryId) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedCategory = categoryId;
            loadProducts(document.getElementById('searchInput').value, categoryId);
        }

        // Add product to cart
        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return;

            if (product.stock <= 0) {
                alert('هذا المنتج غير متوفر في المخزون');
                return;
            }

            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                if (existingItem.qty >= product.stock) {
                    alert('الكمية المطلوبة أكبر من المتوفر في المخزون');
                    return;
                }
                existingItem.qty++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    qty: 1,
                    stock: product.stock,
                    discount: 0
                });
            }

            // Play beep sound
            const sound = document.getElementById('beepSound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(e => console.log('Sound blocked'));
            }

            renderCart();
        }

        // Render cart
        function renderCart() {
            const container = document.getElementById('cartItems');
            const countEl = document.getElementById('cartCount');
            const btnPay = document.getElementById('btnPay');

            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <i class="bi bi-cart-x"></i>
                        <p>السلة فارغة</p>
                        <small>اضغط على منتج لإضافته</small>
                    </div>
                `;
                btnPay.disabled = true;
                countEl.textContent = '0';
            } else {
                container.innerHTML = cart.map(item => `
                    <div class="cart-item">
                        <div class="cart-item-icon"><i class="bi bi-box"></i></div>
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="cart-item-price">${item.price.toFixed(2)}</span>
                                <input type="number" class="form-control form-control-sm bg-transparent text-white border-0 p-0 text-center" 
                                    style="width: 50px; border-bottom: 1px dashed #444 !important; font-size: 0.75rem"
                                    placeholder="خصم" value="${item.discount || 0}" onchange="updateLineDiscount(${item.id}, this.value)">
                            </div>
                        </div>
                        <div class="cart-item-qty">
                            <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                            <span class="qty-value">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                        <div class="cart-item-total">${((item.price * item.qty) - (item.discount || 0)).toFixed(2)}</div>
                        <button class="cart-item-remove" onclick="removeFromCart(${item.id})">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                `).join('');
                btnPay.disabled = false;
                countEl.textContent = cart.reduce((sum, item) => sum + item.qty, 0);
            }

            updateTotals();
        }

        // Update quantity
        function updateQty(productId, change) {
            const item = cart.find(i => i.id === productId);
            if (!item) return;

            item.qty += change;

            if (item.qty <= 0) {
                removeFromCart(productId);
            } else if (item.qty > item.stock) {
                item.qty = item.stock;
                alert('الكمية المطلوبة أكبر من المتوفر');
            }

            renderCart();
        }

        // Remove from cart
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            renderCart();
        }

        // Clear cart
        function clearCart() {
            if (cart.length === 0) return;
            if (confirm('هل تريد مسح السلة؟')) {
                cart = [];
                renderCart();
            }
        }

        // Update totals
        function updateTotals() {
            const lineSubtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const lineDiscount = cart.reduce((sum, item) => sum + (parseFloat(item.discount) || 0), 0);
            const subtotal = lineSubtotal - lineDiscount;

            // Calculate Invoice Discount
            let discountValue = parseFloat(document.getElementById('invoiceDiscount').value) || 0;
            const discountType = document.getElementById('invoiceDiscountType').value;
            let invoiceDiscountAmount = 0;

            if (discountType === 'percent') {
                invoiceDiscountAmount = (subtotal * discountValue) / 100;
            } else {
                invoiceDiscountAmount = discountValue;
            }

            const total = subtotal - invoiceDiscountAmount;
            const totalDiscount = lineDiscount + invoiceDiscountAmount;

            document.getElementById('subtotal').textContent = lineSubtotal.toFixed(2) + ' ج.م';
            document.getElementById('discount').textContent = '- ' + totalDiscount.toFixed(2) + ' ج.م';
            document.getElementById('total').textContent = total.toFixed(2) + ' ج.م';

            const modalTotal = document.getElementById('modalTotal');
            if (modalTotal) modalTotal.textContent = total.toFixed(2);
            calculateChange();
        }

        // Show payment modal
        function showPaymentModal() {
            if (cart.length === 0) return;

            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            document.getElementById('modalTotal').textContent = total.toFixed(2);
            document.getElementById('amountPaid').value = total.toFixed(2);
            document.getElementById('changeAmount').textContent = '0.00';

            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }

        // Select payment method
        function selectPayment(method) {
            selectedPaymentMethod = method;
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');

            const splitRow = document.getElementById('splitPaymentRow');
            if (method === 'split') {
                splitRow.classList.remove('d-none');
                const total = cart.reduce((sum, item) => sum + (item.price * item.qty) - (item.discount || 0), 0);
                document.getElementById('splitCash').value = (total / 2).toFixed(2);
                document.getElementById('splitCard').value = (total / 2).toFixed(2);
            } else {
                splitRow.classList.add('d-none');
            }
        }

        // Numpad input
        function numpadInput(value) {
            const input = document.getElementById('amountPaid');
            input.value = (input.value || '') + value;
            calculateChange();
        }

        function numpadClear() {
            document.getElementById('amountPaid').value = '';
            document.getElementById('changeAmount').textContent = '0.00';
        }

        function setQuickAmount(amount) {
            document.getElementById('amountPaid').value = amount;
            calculateChange();
        }

        function calculateChange() {
            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
            const change = Math.max(0, paid - total);
            document.getElementById('changeAmount').textContent = change.toFixed(2);
        }

        // Process payment
        function processPayment() {
            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const paid = parseFloat(document.getElementById('amountPaid').value) || 0;

            if (paid < total && selectedPaymentMethod !== 'credit') {
                alert('المبلغ المدفوع أقل من الإجمالي');
                return;
            }

            const data = {
                items: cart.map(item => ({
                    product_id: item.id,
                    quantity: item.qty,
                    price: item.price,
                    discount: parseFloat(item.discount) || 0
                })),
                customer_id: document.getElementById('customerSelect').value || null,
                warehouse_id: document.getElementById('warehouseSelect').value || null,
                payment_method: selectedPaymentMethod,
                amount_paid: paid,
                invoice_discount: parseFloat(document.getElementById('invoiceDiscount').value) || 0,
                invoice_discount_type: document.getElementById('invoiceDiscountType').value,
                notes: document.getElementById('invoiceNotes').value
            };

            fetch('/pos/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        localStorage.setItem('lastInvoice', result.invoice_number);
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                        showReceipt(result.invoice_number);

                        cart = [];
                        renderCart();
                        loadProducts();
                    } else {
                        alert('حدث خطأ: ' + (result.message || 'غير معروف'));
                    }
                })
                .catch(err => {
                    console.error('Checkout error:', err);
                    alert('حدث خطأ في الاتصال');
                });
        }

        // Hold sale
        function holdSale() {
            if (cart.length === 0) return;
            const heldSales = JSON.parse(localStorage.getItem('heldSales') || '[]');
            heldSales.push({
                id: Date.now(),
                cart: [...cart],
                date: new Date().toLocaleString('ar-EG')
            });
            localStorage.setItem('heldSales', JSON.stringify(heldSales));
            alert('تم تعليق الفاتورة');
            cart = [];
            renderCart();
        }

        // Show held sales
        function showHeldSales() {
            const heldSales = JSON.parse(localStorage.getItem('heldSales') || '[]');
            if (heldSales.length === 0) {
                alert('لا توجد فواتير معلقة');
                return;
            }
            // For now, just load the first one
            if (confirm('هل تريد استرجاع آخر فاتورة معلقة؟')) {
                cart = heldSales.pop().cart;
                localStorage.setItem('heldSales', JSON.stringify(heldSales));
                renderCart();
            }
        }

        // Show receipt in modal
        function showReceipt(invoiceNumber) {
            const now = new Date();
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const discount = parseFloat(document.getElementById('discount').textContent.replace('- ', '')) || 0;
            const total = subtotal - discount;

            let itemsHTML = cart.map(item => `
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.9rem;">
                    <span>${item.name} x ${item.qty}</span>
                    <span>${(item.price * item.qty).toFixed(2)}</span>
                </div>
            `).join('');

            document.getElementById('receiptContent').innerHTML = `
                <div class="text-center mb-3">
                    <h4 class="mb-1">Twinx ERP - POS</h4>
                    <p class="small text-muted mb-1">فاتورة مبيعات</p>
                    <p class="small mb-0">رقم: ${invoiceNumber}</p>
                    <p class="small mb-0">${now.toLocaleString('ar-EG')}</p>
                </div>
                <hr>
                <div class="mb-3">
                    ${itemsHTML}
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>المجموع:</span>
                    <span>${subtotal.toFixed(2)}</span>
                </div>
                <div class="d-flex justify-content-between text-danger">
                    <span>الخصم:</span>
                    <span>- ${discount.toFixed(2)}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold h5 mt-2">
                    <span>الإجمالي:</span>
                    <span>${total.toFixed(2)} ج.م</span>
                </div>
                <hr>
                <div class="text-center small">
                    شكراً لزيارتكم!
                </div>
            `;

            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        }

        function printReceipt() {
            const printContent = document.getElementById('receiptContent').innerHTML;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            location.reload(); // Refresh to restore states
        }

        function closeShift() {
            if (confirm('هل أنت متأكد من إغلاق الوردية الحالية؟')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/pos/shift/close';
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function showReturnsModal() {
            const invoice = prompt('أدخل رقم الفاتورة للمرتجع:');
            if (invoice) {
                window.location.href = `/pos/returns?invoice=${invoice}`;
            }
        }

        function showDailyReport() {
            window.location.href = '/pos/daily-report';
        }

        function rePrintLast() {
            const last = localStorage.getItem('lastInvoice');
            if (last) {
                showReceipt(last);
            } else {
                alert('لا توجد فاتورة سابقة مخزنة');
            }
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
            if (cart.length > 0) {
                if (!confirm('السلة تحتوي على منتجات. هل تريد الخروج؟')) {
                    return;
                }
            }
            window.location.href = '/dashboard';
        }

        // Focus barcode
        function focusBarcode() {
            document.getElementById('searchInput').focus();
        }

        function updateLineDiscount(productId, discount) {
            const item = cart.find(i => i.id === productId);
            if (item) {
                item.discount = parseFloat(discount) || 0;
                renderCart();
            }
        }

        function openDrawer() {
            alert('تم إرسال أمر فتح درج النقدية إلى الطابعة');
            // Normally: window.print() triggers some relay or custom protocol
        }

        function toggleTheme() {
            const body = document.body;
            const btn = document.getElementById('themeToggle');
            if (body.getAttribute('data-theme') === 'light') {
                body.removeAttribute('data-theme');
                btn.innerHTML = '<i class="bi bi-moon-stars"></i>';
                btn.title = 'وضع الإضاءة';
            } else {
                body.setAttribute('data-theme', 'light');
                btn.innerHTML = '<i class="bi bi-sun"></i>';
                btn.title = 'الوضع الليلي';
            }
        }
    </script>
</body>

</html>