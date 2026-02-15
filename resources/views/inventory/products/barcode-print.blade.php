<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - طباعة ملصق</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Oswald:wght@500;700&display=swap');
        
        /* :root override removed for theme compatibility */

        body {
            font-family: 'Cairo', sans-serif;
            background: #020617;
            background-image: 
                radial-gradient(at 0% 0%, rgba(168, 85, 247, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.15) 0px, transparent 50%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: white;
        }

        /* --- DASHBOARD UI (THE COOL PART) --- */
        .controls-bar {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .product-info h1 {
            font-size: 1.1rem;
            margin: 0;
            color: var(--text-primary);
            text-shadow: 0 0 15px rgba(255,255,255,0.1);
        }
        .product-info .badge {
            background: rgba(168, 85, 247, 0.2);
            color: #d8b4fe;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            border: 1px solid rgba(168, 85, 247, 0.3);
            font-family: monospace;
        }

        .actions-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .glass-input {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            padding: 0.4rem 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: 0.3s;
        }
        .glass-input:focus-within {
            border-color: var(--neon-purple);
            box-shadow: var(--neon-glow);
        }

        .glass-input i { color: #94a3b8; }
        .glass-input label { font-size: 0.8rem; color: #94a3b8; font-weight: 600; white-space: nowrap; }
        
        .glass-input select, .glass-input input {
            background: transparent;
            border: none;
            color: white;
            font-family: inherit;
            font-weight: bold;
            outline: none;
            text-align: center;
            width: auto;
        }
        .glass-input select option { background: #1e293b; color: white; }

        .btn-neon {
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
            border: none;
            padding: 0.6rem 2rem;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.4);
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-neon:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.6);
        }

        .btn-close-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }
        .btn-close-glass:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
            color: #ef4444;
        }

        /* --- PREVIEW AREA --- */
        .workspace {
            flex: 1;
            display: flex;
            justify-content: center;
            padding: 40px;
            overflow: auto;
        }

        .sheet-container {
            background: white;
            box-shadow: 0 0 100px rgba(0,0,0,0.5);
            padding: 2mm;
            /* Dimensions set by JS */
            display: flex;
            align-content: flex-start;
            flex-wrap: wrap;
            gap: 2mm;
            transition: width 0.3s;
        }

        /* --- THE LABEL DESIGN (PREMIUM RETAIL) --- */
        .label-sticker {
            background: white;
            box-sizing: border-box;
            /* border: 1px dashed #e2e8f0; Optional guide border */
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 1.5mm;
            color: black;
            border-radius: 1mm; /* Subtle rounding */
        }

        /* Brand Header */
        .sticker-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 0.5mm;
            margin-bottom: 0.5mm;
            font-weight: 700;
        }

        /* Product Name */
        .sticker-name {
            font-size: 8pt;
            font-weight: 700;
            line-height: 1.1;
            text-align: center;
            flex-grow: 1; /* Push others down */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0 1mm;
        }

        /* Barcode Area */
        .sticker-barcode {
            margin: 1mm 0;
            display: flex;
            justify-content: center;
        }
        .sticker-barcode svg {
            width: 95%; 
            height: 28px;
            display: block;
        }

        /* Footer: Price & SKU */
        .sticker-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #ddd;
            padding-top: 1mm;
        }

        .sticker-sku {
            font-family: 'Oswald', monospace;
            font-size: 7pt;
            color: #333;
            font-weight: 500;
        }

        .sticker-price {
            font-family: 'Oswald', sans-serif;
            font-size: 11pt;
            font-weight: 700;
            line-height: 0.9;
        }
        .sticker-currency {
            font-size: 6pt;
            vertical-align: super;
            font-weight: 500;
        }

        /* --- PRINT STYLES --- */
        @media print {
            @page {
                margin: 0;
                padding: 0;
            }
            
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: white !important;
                overflow: visible !important;
            }

            .controls-bar { display: none !important; }
            .workspace { 
                padding: 0 !important; 
                margin: 0 !important;
                display: block !important;
                overflow: visible !important;
                background: white !important;
            }

            .sheet-container { 
                box-shadow: none !important; 
                margin: 0 !important; 
                padding: 0 !important;
                width: 100% !important; 
                display: block !important; 
            }

            /* SHEET MODE (A4) */
            body[data-mode="sheet"] .label-sticker {
                float: right; /* RTL Flow */
                page-break-inside: avoid;
                margin: 1mm; /* Gutter */
                border: 1px dashed #ddd; /* Helper guide */
            }

            /* ROLL MODE (THERMAL) */
            body[data-mode="roll"] .sheet-container {
                width: auto !important;
            }

            body[data-mode="roll"] .label-sticker {
                float: none !important;
                margin: 0 auto !important; /* Horizontally Center */
                page-break-after: always !important; /* Force cut */
                break-after: page !important;
                border: none !important;
                /* Vertical Center if needed, but usually top-aligned is safer for roll */
                position: relative;
                left: 0;
                top: 0;
            }
        }
    </style>
</head>
<body data-mode="sheet">

    <!-- THE UI -->
    <div class="controls-bar">
        <div class="product-info">
            <h1 class="d-flex align-items-center gap-2">
                <i class="bi bi-tag-fill text-purple-400"></i>
                {{ Str::limit($product->name, 25) }}
            </h1>
            <div class="mt-1">
                <span class="badge">{{ $product->sku }}</span>
            </div>
        </div>

        <div class="actions-group">
            <!-- Paper Type -->
            <div class="glass-input">
                <i class="bi bi-file-earmark-text"></i>
                <select id="paperMode" onchange="setMode(this.value)">
                    <option value="sheet">A4 Sheet</option>
                    <option value="roll">Thermal Roll</option>
                </select>
            </div>

            <!-- Size -->
            <div class="glass-input">
                <i class="bi bi-aspect-ratio"></i>
                <select id="labelSize" onchange="setSize(this.value)">
                    <option value="38x25">38mm x 25mm</option>
                    <option value="50x25">50mm x 25mm</option>
                    <option value="40x30">40mm x 30mm</option>
                </select>
            </div>

            <!-- Quantity -->
            <div class="glass-input">
                <i class="bi bi-123"></i>
                <input type="number" id="copies" value="{{ $copies }}" min="1" max="100" onchange="render()">
            </div>

            <button onclick="window.print()" class="btn-neon">
                <i class="bi bi-printer-fill"></i> طباعة الآن
            </button>
            
            <button onclick="window.close()" class="btn-close-glass">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- PREVIEW -->
    <div class="workspace">
        <div id="sheet" class="sheet-container"></div>
    </div>

    <!-- LABEL TEMPLATE -->
    <template id="tpl">
        <div class="label-sticker">
            <div class="sticker-brand">{{ $product->brand ? $product->brand->name : 'TWINX ERP' }}</div>
            <div class="sticker-name">{{ Str::limit($product->name, 20) }}</div>
            <div class="sticker-barcode">{!! $barcode_svg !!}</div>
            <div class="sticker-footer">
                <div class="sticker-sku">{{ $product->barcode ?: $product->sku }}</div>
                <div class="sticker-price">{{ number_format($product->selling_price) }}<span class="sticker-currency">EGP</span></div>
            </div>
        </div>
    </template>

    <!-- Dynamic Page Style -->
    <style id="page-style">
        @page { size: auto; margin: 0; }
    </style>

    <script>
        let config = { w: 38, h: 25, mode: 'sheet' };

        function init() { render(); }
        function setMode(m) { config.mode = m; document.body.setAttribute('data-mode', m); render(); }
        function setSize(s) { 
            const [w, h] = s.split('x').map(Number);
            config.w = w; config.h = h; render(); 
        }

        function render() {
            const count = document.getElementById('copies').value;
            const sheet = document.getElementById('sheet');
            const tpl = document.getElementById('tpl').content;
            const style = document.getElementById('page-style');
            
            sheet.innerHTML = '';
            
            if(config.mode === 'roll') {
                // FORCE @page SIZE
                style.innerHTML = `@page { size: ${config.w}mm ${config.h}mm; margin: 0; }`;
                
                sheet.style.width = (config.w + 1) + 'mm'; // +1mm buffer
                sheet.style.minHeight = 'auto';
            } else {
                // A4 Default
                style.innerHTML = `@page { size: A4; margin: 0; }`;
                
                sheet.style.width = '210mm';
                sheet.style.minHeight = '297mm';
            }

            for(let i=0; i<count; i++) {
                const node = document.importNode(tpl, true);
                const sticker = node.querySelector('.label-sticker');
                
                sticker.style.width = config.w + 'mm';
                sticker.style.height = config.h + 'mm';

                // Adjust layout for small height
                if(config.h < 30) {
                    sticker.querySelector('.sticker-name').style.fontSize = '8pt';
                }

                sheet.appendChild(node);
            }
        }
        window.onload = init;
    </script>
</body>
</html>