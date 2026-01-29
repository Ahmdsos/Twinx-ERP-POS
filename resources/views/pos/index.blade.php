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
    </style>
</head>

<body>
    <div class="pos-container">
        <!-- Cart Panel -->
        <div class="cart-panel">
            <div class="cart-header">
                <h2><i class="bi bi-cart3"></i> السلة <span class="badge" id="cartCount">0</span></h2>
                <button class="btn btn-sm btn-outline-light" onclick="toggleFullscreen()">
                    <i class="bi bi-fullscreen"></i>
                </button>
            </div>

            <div class="customer-select">
                <select id="customerSelect">
                    <option value="">عميل عام (نقدي)</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->code }} - {{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <p>السلة فارغة</p>
                    <small>اضغط على منتج لإضافته</small>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>المجموع الفرعي</span>
                    <span id="subtotal">0.00</span>
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
                <button class="category-btn active" data-category="" onclick="filterCategory(this, '')">الكل</button>
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
                                <input type="number" class="form-control form-control-lg" id="amountPaid" step="0.01"
                                    min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الباقي</label>
                                <div class="amount-display" id="changeAmount" style="color: var(--warning);">0.00</div>
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
                                <button class="btn btn-outline-light" onclick="setExactAmount()">المبلغ بالضبط</button>
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

            document.getElementById('searchInput').addEventListener('input', debounce(searchProducts, 300));
            document.getElementById('amountPaid').addEventListener('input', calculateChange);
        });

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
                    discount: 0
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
                    <div class="info">
                        <div class="name">${item.name}</div>
                        <div class="price">${item.price.toFixed(2)} × ${item.quantity}</div>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQuantity(${index}, -1)">−</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                    <div class="item-total">${(item.price * item.quantity).toFixed(2)}</div>
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
            const discount = cart.reduce((sum, item) => sum + item.discount, 0);
            const total = subtotal;

            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('discount').textContent = discount.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2);
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