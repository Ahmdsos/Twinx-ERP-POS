@extends('layouts.app')
@section('title', __('Barcode Management'))
@section('content')
    <style>
        .bm {
            padding: 1rem 1.25rem
        }

        .bm * {
            box-sizing: border-box
        }

        /* === 2-col layout: settings+preview LEFT | products RIGHT === */
        .bm-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1rem;
            min-height: calc(100vh - 130px)
        }

        @media(max-width:992px) {
            .bm-grid {
                grid-template-columns: 1fr
            }
        }

        .bm-col {
            display: flex;
            flex-direction: column;
            gap: .75rem
        }

        /* Cards */
        .bm-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 1rem;
            box-shadow: var(--card-shadow)
        }

        .bm-title {
            font-size: .88rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: .75rem
        }

        .bm-title i {
            color: #3b82f6;
            font-size: 1rem
        }

        .bm-title .badge-count {
            margin-inline-start: auto;
            background: rgba(59, 130, 246, .15);
            color: #60a5fa;
            font-size: .7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px
        }

        /* === TABS === */
        .bm-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 1rem
        }

        .bm-tab {
            padding: 8px 16px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border: none;
            background: transparent;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            white-space: nowrap
        }

        .bm-tab:hover {
            color: var(--text-secondary)
        }

        .bm-tab.act {
            color: #3b82f6;
            border-bottom-color: #3b82f6
        }

        .bm-tab-panel {
            display: none
        }

        .bm-tab-panel.act {
            display: block
        }

        /* === Inputs === */
        .bm-inp {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            padding: 8px 12px;
            color: var(--input-color);
            font-size: .82rem;
            width: 100%;
            transition: border .2s
        }

        .bm-inp:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: var(--input-focus-shadow)
        }

        .bm-lbl {
            font-size: .72rem;
            font-weight: 600;
            color: var(--text-secondary);
            display: block;
            margin-bottom: 4px
        }

        .bm-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px
        }

        .bm-fg {
            flex: 1;
            min-width: 0
        }

        /* Checkboxes */
        .bm-chk {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .78rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background .15s;
            border: 1px solid transparent
        }

        .bm-chk:hover {
            background: rgba(59, 130, 246, .05)
        }

        .bm-chk input {
            accent-color: #3b82f6;
            width: 16px;
            height: 16px
        }

        .bm-chks {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 4px;
            margin-bottom: 8px
        }

        /* Templates */
        .bm-tpls {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: 8px
        }

        .bm-tpl {
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            border-radius: 10px;
            padding: 10px 8px;
            cursor: pointer;
            font-size: .76rem;
            color: var(--text-secondary);
            transition: all .2s;
            font-weight: 600;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px
        }

        .bm-tpl span {
            font-size: 1.2rem
        }

        .bm-tpl:hover {
            border-color: rgba(59, 130, 246, .4);
            background: rgba(59, 130, 246, .05)
        }

        .bm-tpl.act {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, .1);
            color: #60a5fa
        }

        /* Pill buttons - bigger */
        .bm-pills {
            display: flex;
            gap: 0;
            background: var(--input-bg);
            border-radius: 8px;
            padding: 3px;
            border: 1px solid var(--input-border)
        }

        .bm-pill {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: .74rem;
            cursor: pointer;
            color: var(--text-muted);
            transition: all .15s;
            white-space: nowrap;
            border: none;
            background: transparent;
            font-weight: 600;
            flex: 1;
            text-align: center
        }

        .bm-pill.act {
            background: #3b82f6;
            color: #fff
        }

        /* Number input with +/- */
        .bm-num {
            display: flex;
            align-items: center;
            gap: 0;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            overflow: hidden;
            background: var(--input-bg)
        }

        .bm-num button {
            width: 30px;
            height: 32px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: .9rem;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .bm-num button:hover {
            background: rgba(59, 130, 246, .1);
            color: #60a5fa
        }

        .bm-num input {
            width: 40px;
            border: none;
            text-align: center;
            background: transparent;
            color: var(--text-primary);
            font-weight: 700;
            font-size: .82rem
        }

        /* Size presets */
        .bm-szs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px
        }

        .bm-sz {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: .72rem;
            cursor: pointer;
            border: 2px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-muted);
            transition: all .15s;
            font-weight: 700
        }

        .bm-sz.act {
            background: rgba(59, 130, 246, .1);
            color: #60a5fa;
            border-color: #3b82f6
        }

        /* === Preview === */
        .bm-pvw {
            background: #ffffff;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            min-height: 140px;
            border: 2px dashed rgba(0, 0, 0, .08)
        }

        .bm-pvs {
            border: 1px solid #ccc;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 6px;
            background: #fff
        }

        .pv-el {
            line-height: 1.2;
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .pv-el.l2 {
            white-space: normal;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden
        }

        /* === Product list === */
        .bm-plist {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
            max-height: calc(100vh - 320px)
        }

        .bm-plist::-webkit-scrollbar {
            width: 4px
        }

        .bm-plist::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 4px
        }

        .bm-pi {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 10px;
            cursor: pointer;
            transition: all .15s;
            border-bottom: 1px solid var(--border-color);
            font-size: .78rem
        }

        .bm-pi:hover {
            background: rgba(59, 130, 246, .06)
        }

        .bm-pi.sel {
            background: rgba(59, 130, 246, .1);
            border-inline-start: 3px solid #3b82f6
        }

        .bm-pi input[type=checkbox] {
            accent-color: #3b82f6;
            width: 16px;
            height: 16px;
            flex-shrink: 0
        }

        .bm-pi-info {
            flex: 1;
            min-width: 0
        }

        .bm-pi-name {
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: .8rem
        }

        .bm-pi-meta {
            font-size: .68rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .bm-pi-price {
            font-weight: 700;
            color: var(--text-primary);
            font-size: .8rem;
            white-space: nowrap
        }

        /* SA row */
        .bm-sa {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: .78rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 6px
        }

        /* Selected table */
        .bm-sel-area {
            max-height: 200px;
            overflow-y: auto;
            scrollbar-width: thin
        }

        .bm-st {
            width: 100%;
            border-collapse: collapse;
            font-size: .76rem
        }

        .bm-st th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: .68rem;
            text-align: start;
            padding: 5px 6px;
            border-bottom: 1px solid var(--border-color)
        }

        .bm-st td {
            padding: 5px 6px;
            color: var(--text-primary);
            vertical-align: middle
        }

        .bm-st input {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 6px;
            padding: 4px 6px;
            width: 70px;
            text-align: center;
            color: var(--input-color);
            font-size: .74rem
        }

        .bm-cc {
            display: flex;
            align-items: center;
            gap: 0
        }

        .bm-cc button {
            width: 24px;
            height: 24px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-secondary);
            border-radius: 6px;
            cursor: pointer;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .bm-cc button:hover {
            background: rgba(59, 130, 246, .15);
            color: #60a5fa
        }

        .bm-cc input {
            width: 34px;
            border: none;
            text-align: center;
            background: transparent;
            color: var(--text-primary);
            font-weight: 700;
            font-size: .8rem
        }

        .bm-rb {
            background: transparent;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: .9rem;
            padding: 2px
        }

        .bm-rb:hover {
            transform: scale(1.2)
        }

        .bm-pvb {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: .8rem;
            padding: 2px
        }

        /* Empty */
        .bm-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-muted);
            font-size: .78rem;
            gap: 6px
        }

        .bm-empty i {
            font-size: 1.5rem;
            opacity: .5
        }

        /* Buttons */
        .bm-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            color: #fff;
            font-weight: 700;
            font-size: .84rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all .2s;
            width: 100%
        }

        .bm-btn:hover:not(:disabled) {
            filter: brightness(1.08);
            transform: translateY(-1px)
        }

        .bm-btn:disabled {
            opacity: .5;
            cursor: not-allowed
        }

        .bm-btn-s {
            background: transparent;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            padding: 4px 10px;
            color: var(--text-secondary);
            font-size: .72rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all .15s
        }

        .bm-btn-s:hover {
            background: rgba(59, 130, 246, .1);
            color: #60a5fa
        }

        .bm-btn-ghost {
            background: transparent;
            border: 1px solid rgba(59, 130, 246, .3);
            border-radius: 8px;
            padding: 6px 12px;
            color: #60a5fa;
            font-size: .76rem;
            cursor: pointer;
            font-weight: 600;
            transition: all .15s
        }

        .bm-btn-ghost:hover {
            background: rgba(59, 130, 246, .08)
        }
    </style>

    <div class="bm">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0" style="color:var(--text-heading);font-size:1.1rem"><i class="bi bi-upc-scan me-2"
                    style="color:#3b82f6"></i>{{ __('Barcode Management') }}</h5>
            <button class="bm-btn" style="width:auto;padding:8px 20px" id="pb1" disabled onclick="submitPrint()"><i
                    class="bi bi-printer-fill"></i> {{ __('Print Barcodes') }} (<span id="c1">0</span>)</button>
        </div>

        <div class="bm-grid">
            {{-- ====== LEFT: Settings + Preview ====== --}}
            <div class="bm-col">
                {{-- PREVIEW --}}
                <div class="bm-card">
                    <div class="bm-title"><i class="bi bi-eye-fill"></i> {{ __('Preview') }}
                        <span style="font-weight:400;color:var(--text-muted);font-size:.72rem" id="pvN"></span>
                    </div>
                    <div class="bm-pvw">
                        <div class="bm-pvs" id="pvS" style="width:189px;height:113px;align-items:center">
                            <div id="pvA"></div>
                            <div id="pvBar" style="text-align:center">
                                <svg viewBox="0 0 200 50" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="200" height="50" fill="#f8f8f8" /><text x="100" y="30" text-anchor="middle"
                                        font-size="11" fill="#bbb">{{ __('Barcode') }}</text>
                                </svg>
                            </div>
                            <div id="pvB"></div>
                        </div>
                    </div>
                </div>

                {{-- TABS --}}
                <div class="bm-card" style="flex:1">
                    <div class="bm-tabs">
                        <button class="bm-tab act" onclick="openTab(this,'tab1')"><i
                                class="bi bi-grid-3x3-gap me-1"></i>{{ __('Templates') }}</button>
                        <button class="bm-tab" onclick="openTab(this,'tab2')"><i
                                class="bi bi-sliders me-1"></i>{{ __('Elements') }}</button>
                        <button class="bm-tab" onclick="openTab(this,'tab3')"><i
                                class="bi bi-arrows-move me-1"></i>{{ __('Position & Layout') }}</button>
                    </div>

                    {{-- TAB 1: Templates --}}
                    <div class="bm-tab-panel act" id="tab1">
                        <div class="bm-tpls">
                            <button class="bm-tpl act" onclick="apT('standard')"
                                data-t="standard"><span>📦</span>{{ __('Standard') }}</button>
                            <button class="bm-tpl" onclick="apT('price_tag')"
                                data-t="price_tag"><span>🏷️</span>{{ __('Price Tag') }}</button>
                            <button class="bm-tpl" onclick="apT('minimal')"
                                data-t="minimal"><span>✨</span>{{ __('Minimal') }}</button>
                            <button class="bm-tpl" onclick="apT('full')"
                                data-t="full"><span>📋</span>{{ __('Full Details') }}</button>
                            <button class="bm-tpl" onclick="apT('shelf')"
                                data-t="shelf"><span>🏪</span>{{ __('Shelf Label') }}</button>
                            <button class="bm-tpl" onclick="apT('jewelry')"
                                data-t="jewelry"><span>💎</span>{{ __('Jewelry') }}</button>
                        </div>

                        <div class="bm-lbl" style="margin-top:8px;margin-bottom:6px">{{ __('Sticker Size') }} (mm)</div>
                        <div class="bm-szs">
                            <button class="bm-sz act" data-d="50x30" onclick="sD(50,30)">50×30</button>
                            <button class="bm-sz" data-d="38x25" onclick="sD(38,25)">38×25</button>
                            <button class="bm-sz" data-d="60x40" onclick="sD(60,40)">60×40</button>
                            <button class="bm-sz" data-d="58x40" onclick="sD(58,40)">58×40</button>
                            <button class="bm-sz" data-d="20x10" onclick="sD(20,10)">20×10</button>
                            <button class="bm-sz" data-d="70x50" onclick="sD(70,50)">70×50</button>
                        </div>
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Width') }}</span><input type="number"
                                    class="bm-inp" id="sW" value="50" min="10" max="200" onchange="sDC()"></div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Height') }}</span><input type="number"
                                    class="bm-inp" id="sH" value="30" min="10" max="200" onchange="sDC()"></div>
                        </div>
                    </div>

                    {{-- TAB 2: Elements & Sizes --}}
                    <div class="bm-tab-panel" id="tab2">
                        <div class="bm-lbl" style="margin-bottom:6px">{{ __('Show/Hide Elements') }}</div>
                        <div class="bm-chks">
                            <label class="bm-chk"><input type="checkbox" id="shN" checked onchange="upd()">
                                {{ __('Name') }}</label>
                            <label class="bm-chk"><input type="checkbox" id="shP" checked onchange="upd()">
                                {{ __('Price') }}</label>
                            <label class="bm-chk"><input type="checkbox" id="shS" onchange="upd()"> {{ __('SKU') }}</label>
                            <label class="bm-chk"><input type="checkbox" id="shBN" checked onchange="upd()">
                                {{ __('Barcode') }} #</label>
                            <label class="bm-chk"><input type="checkbox" id="shCat" onchange="upd()">
                                {{ __('Category') }}</label>
                            <label class="bm-chk"><input type="checkbox" id="shBr" onchange="upd()">
                                {{ __('Brand') }}</label>
                        </div>
                        <div class="bm-row">
                            <label class="bm-chk" style="flex:1"><input type="checkbox" id="nB" checked onchange="upd()">
                                {{ __('Bold Name') }}</label>
                            <label class="bm-chk" style="flex:1"><input type="checkbox" id="pB" checked onchange="upd()">
                                {{ __('Bold Price') }}</label>
                        </div>

                        <div class="bm-lbl" style="margin-top:8px;margin-bottom:6px">{{ __('Font Sizes') }}</div>
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Name') }}</span>
                                <div class="bm-num"><button onclick="adjFS('nFS',-1)">−</button><input id="nFS" value="10"
                                        onchange="upd()"><button onclick="adjFS('nFS',1)">+</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Price') }}</span>
                                <div class="bm-num"><button onclick="adjFS('pFS',-1)">−</button><input id="pFS" value="12"
                                        onchange="upd()"><button onclick="adjFS('pFS',1)">+</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Barcode') }}</span>
                                <div class="bm-num"><button onclick="adjFS('bnFS',-1)">−</button><input id="bnFS" value="10"
                                        onchange="upd()"><button onclick="adjFS('bnFS',1)">+</button></div>
                            </div>
                        </div>
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Header') }}</span>
                                <div class="bm-num"><button onclick="adjFS('hFS',-1)">−</button><input id="hFS" value="8"
                                        onchange="upd()"><button onclick="adjFS('hFS',1)">+</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('SKU') }}</span>
                                <div class="bm-num"><button onclick="adjFS('skFS',-1)">−</button><input id="skFS" value="8"
                                        onchange="upd()"><button onclick="adjFS('skFS',1)">+</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Category') }}</span>
                                <div class="bm-num"><button onclick="adjFS('catFS',-1)">−</button><input id="catFS"
                                        value="7" onchange="upd()"><button onclick="adjFS('catFS',1)">+</button></div>
                            </div>
                        </div>
                        <div class="bm-row" style="margin-bottom:0">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Brand') }}</span>
                                <div class="bm-num"><button onclick="adjFS('brFS',-1)">−</button><input id="brFS" value="7"
                                        onchange="upd()"><button onclick="adjFS('brFS',1)">+</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Bar Width') }}</span>
                                <div class="bm-num"><button onclick="adjFS('bW',-1)">−</button><input id="bW" value="2"
                                        onchange="upd()"><button onclick="adjFS('bW',1)">+</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Bar Height') }}</span>
                                <div class="bm-num"><button onclick="adjFS('bH',-5)">−</button><input id="bH" value="60"
                                        onchange="upd()"><button onclick="adjFS('bH',5)">+</button></div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: Position & Layout --}}
                    <div class="bm-tab-panel" id="tab3">
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Name Position') }}</span>
                                <div class="bm-pills"><button class="bm-pill act" data-g="nP"
                                        onclick="sO('nP','above',this)">{{ __('Above') }}</button><button class="bm-pill"
                                        data-g="nP" onclick="sO('nP','below',this)">{{ __('Below') }}</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Price Position') }}</span>
                                <div class="bm-pills"><button class="bm-pill" data-g="pP"
                                        onclick="sO('pP','above',this)">{{ __('Above') }}</button><button
                                        class="bm-pill act" data-g="pP"
                                        onclick="sO('pP','below',this)">{{ __('Below') }}</button></div>
                            </div>
                        </div>
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Name Align') }}</span>
                                <div class="bm-pills"><button class="bm-pill" data-g="nA"
                                        onclick="sO('nA','left',this)">⇐</button><button class="bm-pill act" data-g="nA"
                                        onclick="sO('nA','center',this)">☰</button><button class="bm-pill" data-g="nA"
                                        onclick="sO('nA','right',this)">⇒</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Price Align') }}</span>
                                <div class="bm-pills"><button class="bm-pill" data-g="pA"
                                        onclick="sO('pA','left',this)">⇐</button><button class="bm-pill act" data-g="pA"
                                        onclick="sO('pA','center',this)">☰</button><button class="bm-pill" data-g="pA"
                                        onclick="sO('pA','right',this)">⇒</button></div>
                            </div>
                        </div>
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Name Lines') }}</span>
                                <div class="bm-pills"><button class="bm-pill act" data-g="nL"
                                        onclick="sO('nL','1',this)">1</button><button class="bm-pill" data-g="nL"
                                        onclick="sO('nL','2',this)">2</button></div>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Price Decimals') }}</span>
                                <div class="bm-pills"><button class="bm-pill" data-g="pDc"
                                        onclick="sO('pDc','0',this)">0</button><button class="bm-pill act" data-g="pDc"
                                        onclick="sO('pDc','2',this)">2</button><button class="bm-pill" data-g="pDc"
                                        onclick="sO('pDc','3',this)">3</button></div>
                            </div>
                        </div>

                        <hr style="border-color:var(--border-color);margin:10px 0">

                        <div class="bm-row">
                            <div class="bm-fg" style="flex:2"><span class="bm-lbl">{{ __('Header Text') }}</span><input
                                    type="text" class="bm-inp" id="hT" placeholder="{{ __('e.g. Store Name') }}"
                                    oninput="upd()"></div>
                        </div>
                        <div class="bm-row">
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Font') }}</span>
                                <select class="bm-inp" id="fF" onchange="upd()">
                                    <option value="Arial">Arial</option>
                                    <option value="Cairo">Cairo</option>
                                    <option value="Tajawal">Tajawal</option>
                                    <option value="Inter">Inter</option>
                                    <option value="Courier New">Courier</option>
                                </select>
                            </div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Currency') }}</span><input type="text"
                                    class="bm-inp" id="cur" value="{{ __('EGP') }}"></div>
                            <div class="bm-fg"><span class="bm-lbl">{{ __('Currency Position') }}</span>
                                <div class="bm-pills"><button class="bm-pill" data-g="cP"
                                        onclick="sO('cP','before',this)">{{ __('Before') }}</button><button
                                        class="bm-pill act" data-g="cP"
                                        onclick="sO('cP','after',this)">{{ __('After') }}</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ====== RIGHT: Products ====== --}}
            <div class="bm-col">
                {{-- Find Products --}}
                <div class="bm-card" style="flex:1;display:flex;flex-direction:column;overflow:hidden">
                    <div class="bm-title"><i class="bi bi-search"></i> {{ __('Find Products') }}
                        <span class="badge-count" id="fC">0</span>
                    </div>
                    <input type="text" class="bm-inp" id="sI" placeholder="{{ __('Search name, SKU, barcode...') }}"
                        oninput="doFilter()" style="margin-bottom:8px">
                    <div class="bm-row" style="margin-bottom:8px">
                        <div class="bm-fg"><span class="bm-lbl">{{ __('From Date') }}</span><input type="date"
                                class="bm-inp" id="df" onchange="doFilter()"></div>
                        <div class="bm-fg"><span class="bm-lbl">{{ __('To Date') }}</span><input type="date" class="bm-inp"
                                id="dt" onchange="doFilter()"></div>
                    </div>
                    <div class="bm-sa"><input type="checkbox" id="sA" onclick="togAll()"
                            style="accent-color:#3b82f6;width:16px;height:16px"><label for="sA"
                            style="cursor:pointer">{{ __('Select All') }}</label></div>
                    <div class="bm-plist" id="pLB">
                        <div class="bm-empty"><i class="bi bi-hourglass-split"></i>{{ __('Loading...') }}</div>
                    </div>
                </div>

                {{-- Selected --}}
                <div class="bm-card">
                    <div class="bm-title"><i class="bi bi-check2-square"></i> {{ __('Selected') }}
                        <span class="badge-count" id="sC">0</span>
                        <button class="bm-btn-s" style="margin-inline-start:auto" onclick="clrAll()"><i
                                class="bi bi-x-lg"></i> {{ __('Clear') }}</button>
                    </div>
                    <div class="bm-sel-area" id="sB">
                        <div class="bm-empty"><i class="bi bi-arrow-up-circle"></i>{{ __('Select products from the list') }}
                        </div>
                    </div>
                </div>
                <button class="bm-btn" id="pb2" disabled onclick="submitPrint()"><i class="bi bi-printer-fill"></i>
                    {{ __('Print Barcodes') }} (<span id="c2">0</span>)</button>
            </div>
        </div>
    </div>

    <script>
        let all = [], sel = {}, pvId = null;
        let o = { lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', nL: '1', pP: 'below', pA: 'center', pDc: '2', cP: 'after', skP: 'above', catP: 'above', brP: 'above', sW: 50, sH: 30 };
        const T = {
            standard: { shN: 1, shP: 1, shS: 0, shBN: 1, shCat: 0, shBr: 0, nFS: 10, pFS: 12, bnFS: 10, hFS: 8, skFS: 8, catFS: 7, brFS: 7, bW: 2, bH: 60, w: 50, h: 30, nB: 1, pB: 1, lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', pP: 'below', pA: 'center', pDc: '2', cP: 'after', nL: '1', font: 'Arial' },
            price_tag: { shN: 1, shP: 1, shS: 0, shBN: 1, shCat: 0, shBr: 0, nFS: 9, pFS: 22, bnFS: 9, hFS: 8, skFS: 8, catFS: 7, brFS: 7, bW: 2, bH: 45, w: 50, h: 30, nB: 1, pB: 1, lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', pP: 'below', pA: 'center', pDc: '2', cP: 'after', nL: '1', font: 'Arial' },
            minimal: { shN: 0, shP: 0, shS: 0, shBN: 1, shCat: 0, shBr: 0, nFS: 10, pFS: 12, bnFS: 9, hFS: 8, skFS: 8, catFS: 7, brFS: 7, bW: 2, bH: 55, w: 38, h: 25, nB: 1, pB: 1, lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', pP: 'below', pA: 'center', pDc: '2', cP: 'after', nL: '1', font: 'Arial' },
            full: { shN: 1, shP: 1, shS: 1, shBN: 1, shCat: 1, shBr: 1, nFS: 8, pFS: 10, bnFS: 7, hFS: 6, skFS: 7, catFS: 6, brFS: 6, bW: 1, bH: 40, w: 60, h: 40, nB: 1, pB: 1, lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', pP: 'below', pA: 'center', pDc: '2', cP: 'after', nL: '1', font: 'Arial' },
            shelf: { shN: 1, shP: 1, shS: 1, shBN: 1, shCat: 1, shBr: 0, nFS: 10, pFS: 14, bnFS: 8, hFS: 8, skFS: 7, catFS: 7, brFS: 7, bW: 2, bH: 48, w: 58, h: 40, nB: 1, pB: 1, lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', pP: 'below', pA: 'center', pDc: '2', cP: 'after', nL: '1', font: 'Arial' },
            jewelry: { shN: 1, shP: 1, shS: 0, shBN: 0, shCat: 0, shBr: 0, nFS: 5, pFS: 6, bnFS: 5, hFS: 5, skFS: 5, catFS: 5, brFS: 5, bW: 1, bH: 25, w: 20, h: 10, nB: 0, pB: 1, lay: 'vertical', hA: 'center', nP: 'above', nA: 'center', pP: 'below', pA: 'center', pDc: '0', cP: 'after', nL: '1', font: 'Arial' }
        };

        document.addEventListener('DOMContentLoaded', function () {
            fetch("{{ route('barcode.searchProducts') }}", { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(function (d) { all = d.products || []; doFilter(); upd(); })
                .catch(function (e) { console.error(e); document.getElementById('pLB').innerHTML = '<div class="bm-empty"><i class="bi bi-exclamation-triangle"></i>{{ __("Error loading") }}</div>'; });
        });

        function el(id) { return document.getElementById(id); }
        function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function gV(id) { return parseInt(el(id).value) || 8; }

        function sv(e) { var v = el(e.id + 'V'); if (v) v.textContent = e.value; }
        function sl(id, v) { var e = el(id); if (e) { e.value = v; } }
        function adjFS(id, d) { var e = el(id); if (!e) return; e.value = Math.max(1, Math.min(200, parseInt(e.value || 0) + d)); upd(); }

        function openTab(btn, panelId) {
            document.querySelectorAll('.bm-tab').forEach(function (t) { t.classList.remove('act'); });
            document.querySelectorAll('.bm-tab-panel').forEach(function (p) { p.classList.remove('act'); });
            btn.classList.add('act'); el(panelId).classList.add('act');
        }

        function doFilter() {
            var q = el('sI').value.toLowerCase().trim(), df = el('df').value, dt = el('dt').value;
            var f = all.filter(function (p) {
                if (q && !((p.name || '').toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q) || (p.barcode || '').toLowerCase().includes(q))) return false;
                if (df && p.created_at && p.created_at < df) return false;
                if (dt && p.created_at && p.created_at > dt) return false;
                return true;
            });
            el('fC').textContent = f.length;
            var h = '';
            if (!f.length) { h = '<div class="bm-empty"><i class="bi bi-inbox"></i>{{ __("No results") }}</div>'; }
            else f.forEach(function (p) {
                var s = !!sel[p.id];
                h += '<div class="bm-pi ' + (s ? 'sel' : '') + '" onclick="togP(' + p.id + ')"><input type="checkbox" ' + (s ? 'checked' : '') + ' onclick="event.stopPropagation();togP(' + p.id + ')"><div class="bm-pi-info"><div class="bm-pi-name">' + esc(p.name) + '</div><div class="bm-pi-meta">' + esc(p.sku || '') + (p.category_name ? ' · ' + esc(p.category_name) : '') + '</div></div><span class="bm-pi-price">' + Number(p.selling_price || 0).toFixed(2) + '</span></div>';
            });
            el('pLB').innerHTML = h;
        }

        function togP(id) {
            if (sel[id]) delete sel[id]; else { var p = all.find(function (x) { return x.id === id; }); if (p) sel[id] = { id: p.id, name: p.name, sku: p.sku, barcode: p.barcode, selling_price: p.selling_price, category_name: p.category_name, brand_name: p.brand_name, copies: 1, cbc: p.barcode || '' }; }
            if (!sel[pvId]) pvId = Object.keys(sel)[0] ? parseInt(Object.keys(sel)[0]) : null;
            doFilter(); renderSel(); updCnt(); upd();
        }
        function togAll() {
            var cb = el('sA'); var items = document.querySelectorAll('#pLB .bm-pi');
            items.forEach(function (e2) {
                var m = e2.getAttribute('onclick').match(/\d+/); if (!m) return; var id = parseInt(m[0]);
                if (cb.checked) { if (!sel[id]) { var p = all.find(function (x) { return x.id === id; }); if (p) sel[id] = { id: p.id, name: p.name, sku: p.sku, barcode: p.barcode, selling_price: p.selling_price, category_name: p.category_name, brand_name: p.brand_name, copies: 1, cbc: p.barcode || '' }; } }
                else delete sel[id];
            });
            if (!sel[pvId]) pvId = Object.keys(sel)[0] ? parseInt(Object.keys(sel)[0]) : null;
            doFilter(); renderSel(); updCnt();
        }
        function clrAll() { sel = {}; pvId = null; doFilter(); renderSel(); updCnt(); upd(); }
        function prevP(id) { pvId = id; el('pvN').textContent = sel[id] ? sel[id].name : ''; renderSel(); upd(); }

        function renderSel() {
            var sb = el('sB'), ids = Object.keys(sel);
            if (!ids.length) { sb.innerHTML = '<div class="bm-empty"><i class="bi bi-arrow-up-circle"></i>{{ __("Select products from the list") }}</div>'; return; }
            var h = '<table class="bm-st"><thead><tr><th></th><th>{{ __("Product") }}</th><th>{{ __("Barcode") }}</th><th>{{ __("Copies") }}</th><th></th></tr></thead><tbody>';
            ids.forEach(function (id) {
                var s = sel[id], isPv = pvId == id;
                h += '<tr style="' + (isPv ? 'background:rgba(59,130,246,.06);' : '') + '"><td><button class="bm-pvb" onclick="prevP(' + id + ')" title="{{ __("Preview") }}">👁</button></td><td><div style="font-weight:600;color:var(--text-primary);font-size:.74rem;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(s.name) + '</div></td><td><input value="' + esc(s.cbc) + '" onchange="sel[' + id + '].cbc=this.value" placeholder="Auto"></td><td><div class="bm-cc"><button onclick="chC(' + id + ',-1)">−</button><input value="' + s.copies + '" onchange="setC(' + id + ',this.value)"><button onclick="chC(' + id + ',1)">+</button></div></td><td><button class="bm-rb" onclick="togP(' + id + ')">✕</button></td></tr>';
            });
            h += '</tbody></table>'; sb.innerHTML = h;
        }

        function chC(id, d) { if (!sel[id]) return; sel[id].copies = Math.max(1, Math.min(100, sel[id].copies + d)); renderSel(); updCnt(); }
        function setC(id, v) { if (!sel[id]) return; sel[id].copies = Math.max(1, Math.min(100, parseInt(v) || 1)); renderSel(); updCnt(); }
        function updCnt() { var ids = Object.keys(sel), t = ids.reduce(function (s, id) { return s + sel[id].copies; }, 0); el('sC').textContent = ids.length; el('c1').textContent = t; el('c2').textContent = t; el('pb1').disabled = !ids.length; el('pb2').disabled = !ids.length; }

        function sD(w, h) { o.sW = w; o.sH = h; el('sW').value = w; el('sH').value = h; document.querySelectorAll('.bm-sz').forEach(function (e) { e.classList.toggle('act', e.dataset.d === w + 'x' + h); }); upd(); }
        function sDC() { var w = parseInt(el('sW').value) || 50, h = parseInt(el('sH').value) || 30; o.sW = w; o.sH = h; document.querySelectorAll('.bm-sz').forEach(function (e) { e.classList.toggle('act', e.dataset.d === w + 'x' + h); }); upd(); }

        function sO(key, val, btn) { o[key] = val; btn.closest('.bm-pills').querySelectorAll('.bm-pill').forEach(function (b) { b.classList.remove('act'); }); btn.classList.add('act'); upd(); }

        function apT(name) {
            var t = T[name]; if (!t) return;
            document.querySelectorAll('.bm-tpl').forEach(function (b) { b.classList.toggle('act', b.dataset.t === name); });
            el('shN').checked = !!t.shN; el('shP').checked = !!t.shP; el('shS').checked = !!t.shS; el('shBN').checked = !!t.shBN; el('shCat').checked = !!t.shCat; el('shBr').checked = !!t.shBr;
            el('nB').checked = !!t.nB; el('pB').checked = !!t.pB;
            sl('hFS', t.hFS); sl('nFS', t.nFS); sl('pFS', t.pFS); sl('bnFS', t.bnFS); sl('skFS', t.skFS); sl('catFS', t.catFS); sl('brFS', t.brFS);
            sl('bW', t.bW); sl('bH', t.bH); sD(t.w, t.h);
            if (t.font) el('fF').value = t.font;
            o.lay = t.lay; o.hA = t.hA; o.nP = t.nP; o.nA = t.nA; o.pP = t.pP; o.pA = t.pA; o.pDc = t.pDc; o.cP = t.cP; o.nL = t.nL; o.skP = t.skP || 'above'; o.catP = t.catP || 'above'; o.brP = t.brP || 'above';
            syncBtns(); upd();
        }
        function syncBtns() {
            document.querySelectorAll('.bm-pill').forEach(function (b) {
                var oc = b.getAttribute('onclick'); if (!oc) return;
                var m = oc.match(/'([^']+)','([^']+)'/); if (!m) return; b.classList.toggle('act', o[m[1]] === m[2]);
            });
        }

        function upd() {
            var s = el('pvS'); var p = pvId && sel[pvId] ? sel[pvId] : (Object.values(sel)[0] || null);
            var font = el('fF').value, curr = el('cur').value, dec = parseInt(o.pDc) || 0;
            var pw = (o.sW || 50) * 3.78, ph = (o.sH || 30) * 3.78;
            s.style.width = pw + 'px'; s.style.height = ph + 'px'; s.style.fontFamily = font + ',sans-serif';
            el('pvN').textContent = p ? p.name : '';
            var nm = p ? p.name : '{{ __("Product Name") }}', sk = p ? (p.sku || '') : 'SKU-001';
            var raw = p ? Number(p.selling_price || 0) : 0, priceNum = raw.toFixed(dec);
            var priceStr; if (o.cP === 'before' && curr) priceStr = curr + ' ' + priceNum; else if (o.cP === 'after' && curr) priceStr = priceNum + ' ' + curr; else priceStr = priceNum;
            var bcT = p ? (p.cbc || p.barcode || 'Auto') : '6281234567890';
            var catT = p && p.category_name ? p.category_name : '{{ __("Category") }}';
            var brT = p && p.brand_name ? p.brand_name : '{{ __("Brand") }}';
            var hdr = el('hT').value;
            var above = [], below = [];
            if (hdr) above.push({ t: hdr, fs: gV('hFS'), b: false, a: o.hA });
            if (el('shCat').checked) { (o.catP === 'above' ? above : below).push({ t: catT, fs: gV('catFS'), b: false, a: o.hA }); }
            if (el('shBr').checked) { (o.brP === 'above' ? above : below).push({ t: brT, fs: gV('brFS'), b: false, a: o.hA }); }
            if (el('shN').checked) { (o.nP === 'above' ? above : below).push({ t: nm, fs: gV('nFS'), b: el('nB').checked, a: o.nA, cls: o.nL === '2' ? 'l2' : '' }); }
            if (el('shS').checked) { (o.skP === 'above' ? above : below).push({ t: sk, fs: gV('skFS'), b: false, a: o.nA }); }
            if (el('shP').checked) { (o.pP === 'above' ? above : below).push({ t: priceStr, fs: gV('pFS'), b: el('pB').checked, a: o.pA }); }
            if (el('shBN').checked) below.push({ t: bcT, fs: gV('bnFS'), b: false, a: 'center' });

            function mkE(e) { return '<div class="pv-el ' + (e.cls || '') + '" style="font-size:' + e.fs + 'px;font-weight:' + (e.b ? 700 : 400) + ';text-align:' + e.a + ';">' + esc(e.t) + '</div>'; }

            // Generate realistic barcode SVG
            var barH = Math.min(parseInt(el('bH').value) || 60, 80);
            var barW = parseInt(el('bW').value) || 2;
            var svg = '<svg viewBox="0 0 200 ' + barH + '" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-height:' + barH + 'px"><rect width="200" height="' + barH + '" fill="#fff"/><g fill="#000">';
            var x = 5;
            while (x < 195) {
                var w = barW * (Math.random() > 0.5 ? 1 : 0.5);
                svg += '<rect x="' + x + '" y="0" width="' + w + '" height="' + barH + '"/>';
                x += w + (barW * (Math.random() > 0.3 ? 1 : 2));
            }
            svg += '</g></svg>';

            el('pvA').innerHTML = above.map(mkE).join('');
            el('pvBar').innerHTML = svg;
            el('pvB').innerHTML = below.map(mkE).join('');
        }

        function submitPrint() {
            var ids = Object.keys(sel); if (!ids.length) return;
            var f = document.createElement('form'); f.method = 'POST'; f.action = '{{ route("barcode.batch") }}'; f.target = '_blank';
            function aH(n, v) { var i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; f.appendChild(i); }
            aH('_token', '{{ csrf_token() }}');
            ids.forEach(function (id) { aH('products[]', id); aH('copies[' + id + ']', sel[id].copies); if (sel[id].cbc) aH('barcodes[' + id + ']', sel[id].cbc); });
            aH('show_name', el('shN').checked ? '1' : '0'); aH('show_price', el('shP').checked ? '1' : '0'); aH('show_sku', el('shS').checked ? '1' : '0');
            aH('show_barcode_num', el('shBN').checked ? '1' : '0'); aH('show_category', el('shCat').checked ? '1' : '0'); aH('show_brand', el('shBr').checked ? '1' : '0');
            aH('header_text', el('hT').value); aH('header_font_size', gV('hFS')); aH('name_font_size', gV('nFS')); aH('price_font_size', gV('pFS'));
            aH('barcode_font_size', gV('bnFS')); aH('sku_font_size', gV('skFS')); aH('cat_font_size', gV('catFS'));
            aH('bar_width', gV('bW')); aH('bar_height', gV('bH')); aH('sticker_w', o.sW); aH('sticker_h', o.sH); aH('layout', o.lay);
            aH('name_position', o.nP); aH('price_position', o.pP); aH('sku_position', o.skP); aH('cat_position', o.catP); aH('brand_position', o.brP);
            aH('name_align', o.nA); aH('price_align', o.pA); aH('header_align', o.hA); aH('price_decimals', o.pDc);
            aH('currency', el('cur').value); aH('currency_position', o.cP); aH('font_family', el('fF').value);
            aH('name_bold', el('nB').checked ? '1' : '0'); aH('price_bold', el('pB').checked ? '1' : '0'); aH('name_max_lines', o.nL);
            document.body.appendChild(f); f.submit(); document.body.removeChild(f);
        }
    </script>
@endsection