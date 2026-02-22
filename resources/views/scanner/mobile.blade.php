<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Twinx ERP - ماسح الباركود</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="Twinx Scanner">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Twinx Scanner">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('scanner-manifest.json') }}">

    <!-- html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Cairo', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .app-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .app-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .app-title span {
            color: #818cf8;
        }

        .user-badge {
            font-size: 12px;
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.05);
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Scanner Area */
        .scanner-container {
            padding: 16px;
        }

        .scanner-wrapper {
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid rgba(129, 140, 248, 0.3);
            box-shadow: 0 0 30px rgba(129, 140, 248, 0.1);
            position: relative;
            margin-bottom: 16px;
        }

        #reader {
            width: 100%;
        }

        #reader video {
            border-radius: 14px;
        }

        .scanner-status {
            text-align: center;
            padding: 12px;
            font-size: 14px;
            color: #94a3b8;
        }

        .scanner-status.scanning {
            color: #818cf8;
            animation: pulse 2s infinite;
        }

        .scanner-status.success {
            color: #34d399;
        }

        .scanner-status.error {
            color: #f87171;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Manual Input */
        .manual-input {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .manual-input input {
            flex: 1;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 16px;
            color: #fff;
            font-size: 16px;
            outline: none;
            transition: border-color 0.2s;
        }

        .manual-input input:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
        }

        .manual-input input::placeholder {
            color: #475569;
        }

        .btn-search {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
            border: none;
            color: #fff;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-search:active {
            transform: scale(0.95);
        }

        /* Product Card */
        .product-card {
            background: linear-gradient(135deg, #1e293b 0%, #1a2332 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            display: none;
            animation: slideUp 0.3s ease;
        }

        .product-card.show {
            display: block;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-name {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .product-sku {
            font-size: 13px;
            color: #64748b;
            font-family: monospace;
            margin-bottom: 16px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .product-field {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 12px;
        }

        .product-field.full-width {
            grid-column: 1 / -1;
        }

        .field-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .field-value {
            font-size: 16px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .price-main {
            font-size: 28px;
            font-weight: 800;
            color: #34d399;
        }

        .price-currency {
            font-size: 14px;
            color: #34d399;
            opacity: 0.7;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .stock-badge.in-stock {
            background: rgba(52, 211, 153, 0.15);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .stock-badge.low-stock {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .stock-badge.out-of-stock {
            background: rgba(248, 113, 113, 0.15);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.3);
        }

        .stock-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .stock-badge.in-stock .stock-dot {
            background: #34d399;
        }

        .stock-badge.low-stock .stock-dot {
            background: #fbbf24;
        }

        .stock-badge.out-of-stock .stock-dot {
            background: #f87171;
        }

        /* Not Found */
        .not-found {
            text-align: center;
            padding: 32px;
            display: none;
        }

        .not-found.show {
            display: block;
            animation: slideUp 0.3s ease;
        }

        .not-found-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .not-found-text {
            color: #f87171;
            font-size: 16px;
            font-weight: 600;
        }

        .not-found-code {
            color: #64748b;
            font-family: monospace;
            font-size: 14px;
            margin-top: 4px;
        }

        /* Loading */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 24px;
        }

        .loading-spinner.show {
            display: block;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(129, 140, 248, 0.2);
            border-top-color: #818cf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 12px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }


        /* Scanner controls - override html5-qrcode styles */
        #reader__scan_region {
            min-height: 250px !important;
        }

        #reader__dashboard_section_csr button {
            background: #818cf8 !important;
            border: none !important;
            color: #fff !important;
            padding: 10px 20px !important;
            border-radius: 10px !important;
            font-size: 14px !important;
        }

        #reader__dashboard_section_csr select {
            background: #1e293b !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 8px !important;
            padding: 8px !important;
        }

        #reader__dashboard_section {
            padding: 10px !important;
        }

        #reader__header_message {
            color: #94a3b8 !important;
            font-size: 13px !important;
        }

        img[alt="Info icon"] {
            display: none !important;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="app-header">
        <div class="app-title">Twinx <span>Scanner</span></div>
        <div class="user-badge">{{ auth()->user()->name ?? 'User' }}</div>
    </div>

    <div class="scanner-container">
        <!-- Scanner -->
        <div class="scanner-wrapper">
            <div id="reader"></div>
        </div>

        <div class="scanner-status scanning" id="statusText">
            📷 وجّه الكاميرا على الباركود...
        </div>

        <!-- Manual Input -->
        <div class="manual-input">
            <input type="text" id="manualCode" placeholder="أدخل الباركود أو SKU يدوياً..." inputmode="text"
                autocomplete="off">
            <button class="btn-search" onclick="lookupCode(document.getElementById('manualCode').value)">بحث</button>
        </div>

        <!-- Loading -->
        <div class="loading-spinner" id="loading">
            <div class="spinner"></div>
            <div style="color: #94a3b8; font-size: 14px;">جاري البحث...</div>
        </div>

        <!-- Not Found -->
        <div class="not-found" id="notFound">
            <div class="not-found-icon">🔍</div>
            <div class="not-found-text">لم يتم العثور على منتج</div>
            <div class="not-found-code" id="notFoundCode"></div>
        </div>

        <!-- Product Card -->
        <div class="product-card" id="productCard">
            <div class="product-name" id="pName"></div>
            <div class="product-sku" id="pSku"></div>

            <div class="product-grid">
                <!-- Selling Price -->
                <div class="product-field full-width">
                    <div class="field-label">سعر البيع</div>
                    <div>
                        <span class="price-main" id="pPrice"></span>
                        <span class="price-currency">EGP</span>
                    </div>
                </div>

                <!-- Stock -->
                <div class="product-field">
                    <div class="field-label">المخزون</div>
                    <div id="pStockBadge"></div>
                </div>

                <!-- Category -->
                <div class="product-field">
                    <div class="field-label">التصنيف</div>
                    <div class="field-value" id="pCategory"></div>
                </div>

                <!-- Brand -->
                <div class="product-field">
                    <div class="field-label">الماركة</div>
                    <div class="field-value" id="pBrand"></div>
                </div>

                <!-- Barcode -->
                <div class="product-field">
                    <div class="field-label">الباركود</div>
                    <div class="field-value" style="font-family: monospace; font-size: 14px;" id="pBarcode"></div>
                </div>


                <!-- Unit -->
                <div class="product-field">
                    <div class="field-label">الوحدة</div>
                    <div class="field-value" id="pUnit"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- Success Sound -->
    <audio id="beepSound" preload="auto">
        <source src="data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQAAAAA="
            type="audio/wav">
    </audio>

    <script>
        const LOOKUP_URL = "{{ route('scanner.lookup') }}";
        let scanner = null;
        let isProcessing = false;
        let lastScannedCode = '';
        let lastScanTime = 0;

        // Initialize scanner
        document.addEventListener('DOMContentLoaded', function () {
            scanner = new Html5Qrcode("reader");

            const config = {
                fps: 10,
                qrbox: { width: 280, height: 120 },
                aspectRatio: 1.5,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.CODE_93,
                    Html5QrcodeSupportedFormats.ITF,
                    Html5QrcodeSupportedFormats.QR_CODE,
                ]
            };

            scanner.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                setStatus('⚠️ لا يمكن الوصول للكاميرا - استخدم الإدخال اليدوي', 'error');
                console.error('Camera error:', err);
            });

            // Manual input enter key
            document.getElementById('manualCode').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    lookupCode(this.value);
                }
            });
        });

        function onScanSuccess(decodedText) {
            const now = Date.now();
            // Prevent duplicate scans within 3 seconds
            if (decodedText === lastScannedCode && (now - lastScanTime) < 3000) return;
            if (isProcessing) return;

            lastScannedCode = decodedText;
            lastScanTime = now;

            // Play beep
            try { document.getElementById('beepSound').play(); } catch (e) { }

            // Vibrate
            if (navigator.vibrate) navigator.vibrate(100);

            lookupCode(decodedText);
        }

        function onScanFailure(error) {
            // Ignore - this fires continuously when no code is in view
        }

        function lookupCode(code) {
            code = (code || '').trim();
            if (!code || isProcessing) return;

            isProcessing = true;
            hideAll();
            show('loading');
            setStatus('🔍 جاري البحث عن: ' + code, 'scanning');

            fetch(LOOKUP_URL + '?code=' + encodeURIComponent(code), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    hide('loading');
                    isProcessing = false;

                    if (data.found) {
                        displayProduct(data.product);
                        setStatus('✅ تم العثور على المنتج', 'success');
                    } else {
                        document.getElementById('notFoundCode').textContent = 'الكود: ' + code;
                        show('notFound');
                        setStatus('❌ لم يتم العثور على منتج بهذا الكود', 'error');
                    }
                })
                .catch(err => {
                    hide('loading');
                    isProcessing = false;
                    setStatus('⚠️ خطأ في الاتصال - تأكد من الشبكة', 'error');
                    console.error('Lookup error:', err);
                });
        }

        function displayProduct(p) {
            document.getElementById('pName').textContent = p.name;
            document.getElementById('pSku').textContent = 'SKU: ' + p.sku + (p.barcode ? ' | Barcode: ' + p.barcode : '');
            document.getElementById('pPrice').textContent = formatNumber(p.selling_price);
            document.getElementById('pCategory').textContent = p.category || '-';
            document.getElementById('pBrand').textContent = p.brand || '-';
            document.getElementById('pBarcode').textContent = p.barcode || '-';
            document.getElementById('pUnit').textContent = p.unit || '-';

            // Stock badge
            let stockHtml = '';
            if (p.stock <= 0) {
                stockHtml = '<span class="stock-badge out-of-stock"><span class="stock-dot"></span>نفد (0)</span>';
            } else if (p.is_low_stock) {
                stockHtml = '<span class="stock-badge low-stock"><span class="stock-dot"></span>' + formatNumber(p.stock) + ' ' + (p.unit || '') + '</span>';
            } else {
                stockHtml = '<span class="stock-badge in-stock"><span class="stock-dot"></span>' + formatNumber(p.stock) + ' ' + (p.unit || '') + '</span>';
            }
            document.getElementById('pStockBadge').innerHTML = stockHtml;

            show('productCard');
        }

        function formatNumber(n) {
            return parseFloat(n || 0).toFixed(2);
        }

        function setStatus(text, cls) {
            const el = document.getElementById('statusText');
            el.textContent = text;
            el.className = 'scanner-status ' + (cls || '');
        }

        function show(id) { document.getElementById(id).classList.add('show'); }
        function hide(id) { document.getElementById(id).classList.remove('show'); }
        function hideAll() {
            hide('productCard');
            hide('notFound');
            hide('loading');
        }
    </script>
</body>

</html>