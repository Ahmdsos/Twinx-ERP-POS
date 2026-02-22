@extends('layouts.app')

@section('title', __('Settings') . ' - ' . __('General Settings'))

@section('content')
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-heading fw-bold"><i class="bi bi-gear-wide-connected me-2"></i> {{ __('Settings') }}</h2>
            <p class="text-body-50">{{ __('Full control over application options, taxes, printing, and POS.') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Sidebar Navigation (Tabs) -->
            <div class="col-md-3">
                <div class="glass-card p-0 overflow-hidden sticky-top" style="top: 100px; z-index: 10;">
                    <div class="list-group list-group-flush bg-transparent">
                        <button type="button" class="list-group-item list-group-item-action active"
                            onclick="showTab('company')">
                            <i class="bi bi-building me-2"></i> {{ __('Company Info') }}
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('finance')">
                            <i class="bi bi-cash-stack me-2"></i> {{ __('Finance & Tax') }}
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('sales')">
                            <i class="bi bi-receipt me-2"></i> {{ __('Sales & Invoices') }}
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('pos')">
                            <i class="bi bi-shop me-2"></i> {{ __('POS') }}
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('printing')">
                            <i class="bi bi-printer me-2"></i> {{ __('Print Settings') }}
                        </button>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="showTab('accounting')">
                            <i class="bi bi-journal-check me-2"></i> {{ __('Accounting Integration') }}
                        </button>
                        <button type="button" class="list-group-item list-group-item-action text-danger"
                            onclick="showTab('system')">
                            <i class="bi bi-shield-slash me-2"></i> {{ __('Backup & Reset') }}
                        </button>

                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9">

                <!-- Tab: Company Info -->
                <div id="tab-company" class="settings-tab">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">{{ __('Company Info') }}
                        </h4>

                        <div class="mb-4 text-center">
                            <div class="mb-3 position-relative d-inline-block">
                                @if($settings['company']['company_logo'] ?? false)
                                    <img src="{{ Storage::url($settings['company']['company_logo']) }}" id="logo-preview"
                                        class="rounded-circle border border-2 border-secondary border-opacity-10"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle border border-2 border-secondary border-opacity-10 d-flex align-items-center justify-content-center bg-dark text-muted"
                                        style="width: 120px; height: 120px;">
                                        <i class="bi bi-image fs-1"></i>
                                    </div>
                                @endif
                                <label for="company_logo"
                                    class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle"
                                    style="width: 35px; height: 35px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-camera"></i>
                                </label>
                                <input type="file" name="company_logo" id="company_logo" class="d-none"
                                    onchange="previewImage(this)">
                            </div>
                            <p class="text-body-50 small">{{ __('Company logo (appears on invoices and reports)') }}</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Company Name') }}</label>
                                <input type="text" name="company_name"
                                    class="form-control form-control-lg bg-transparent text-body"
                                    value="{{ $settings['company']['company_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Tax Number') }}</label>
                                <input type="text" name="company_tax_number"
                                    class="form-control form-control-lg bg-transparent text-body"
                                    value="{{ $settings['company']['company_tax_number'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">{{ __('Address') }}</label>
                                <textarea name="company_address" class="form-control bg-transparent text-body"
                                    rows="2">{{ $settings['company']['company_address'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Phone') }}</label>
                                <input type="text" name="company_phone" class="form-control bg-transparent text-body"
                                    value="{{ $settings['company']['company_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Email') }}</label>
                                <input type="email" name="company_email" class="form-control bg-transparent text-body"
                                    value="{{ $settings['company']['company_email'] ?? '' }}">
                            </div>

                            <hr class="border-secondary my-4">
                            <h5 class="text-primary fw-bold mb-3"><i class="bi bi-globe me-2"></i> {{ __('Language') }}</h5>

                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Language') }}</label>
                                <select name="app_language" class="form-select bg-transparent text-body">
                                    <option value="ar" {{ ($settings['general']['app_language'] ?? 'ar') == 'ar' ? 'selected' : '' }}>🇪🇬 {{ __('Arabic') }}</option>
                                    <option value="en" {{ ($settings['general']['app_language'] ?? 'ar') == 'en' ? 'selected' : '' }}>🇬🇧 {{ __('English') }}</option>
                                </select>
                                <div class="form-text text-muted small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ __('Language') }}: تغيير اللغة سيتطلب إعادة تحميل الصفحة.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Finance & Tax -->
                <div id="tab-finance" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">{{ __('Finance & Tax') }}
                        </h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Currency') }}</label>
                                <select name="currency_code" class="form-select bg-transparent text-body">
                                    <option value="EGP" {{ ($settings['currency']['currency_code'] ?? '') == 'EGP' ? 'selected' : '' }}>EGP - {{ __('Egyptian Pound') }}</option>
                                    <option value="USD" {{ ($settings['currency']['currency_code'] ?? '') == 'USD' ? 'selected' : '' }}>USD - {{ __('US Dollar') }}</option>
                                    <option value="SAR" {{ ($settings['currency']['currency_code'] ?? '') == 'SAR' ? 'selected' : '' }}>SAR - {{ __('Saudi Riyal') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Currency Symbol') }}</label>
                                <input type="text" name="currency_symbol" class="form-control bg-transparent text-body"
                                    value="{{ $settings['currency']['currency_symbol'] ?? 'ج.م' }}">
                            </div>

                            <hr class="border-secondary my-4">

                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Default Tax Rate (%)') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="default_tax_rate"
                                        class="form-control bg-transparent text-body"
                                        value="{{ $settings['tax']['default_tax_rate'] ?? 14 }}">
                                    <span class="input-group-text bg-transparent text-muted">%</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div
                                    class="form-check form-switch p-0 d-flex gap-3 align-items-center custom-switch-wrapper">
                                    <label class="form-check-label text-muted m-0 order-1"
                                        for="tax_inclusive">{{ __('Prices include tax?') }}</label>
                                    <input class="form-check-input ms-0 me-2 order-2" type="checkbox" id="tax_inclusive"
                                        name="tax_inclusive" {{ ($settings['tax']['tax_inclusive'] ?? false) ? 'checked' : '' }}>
                                </div>
                                <div class="form-text text-muted mt-1 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ __('When enabled, tax will be calculated as part of the product price, not added on top.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Sales & Invoices -->
                <div id="tab-sales" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">{{ __('Invoice Settings') }}
                        </h4>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">{{ __('Invoice Prefix') }}</label>
                                <input type="text" name="invoice_prefix" class="form-control bg-transparent text-body"
                                    value="{{ $settings['invoice']['invoice_prefix'] ?? 'INV' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">{{ __('Next Sequential Number') }}</label>
                                <input type="number" name="invoice_next_number"
                                    class="form-control bg-transparent text-body"
                                    value="{{ $settings['invoice']['invoice_next_number'] ?? 1 }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label text-muted">{{ __('Invoice Footer Note') }}</label>
                                <textarea name="invoice_footer" class="form-control bg-transparent text-body"
                                    rows="3">{{ $settings['invoice']['invoice_footer'] ?? __('Thank you for your business!') }}</textarea>
                                <div class="form-text text-muted">
                                    {{ __('Appears at the bottom of every printed invoice.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: POS -->
                <div id="tab-pos" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">{{ __('POS') }}</h4>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pos_print_receipt"
                                        name="pos_print_receipt" {{ ($settings['pos']['pos_print_receipt'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body"
                                        for="pos_print_receipt">{{ __('Auto print receipt after sale') }}</label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pos_allow_negative_stock"
                                        name="pos_allow_negative_stock" {{ ($settings['pos']['pos_allow_negative_stock'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body"
                                        for="pos_allow_negative_stock">{{ __('Allow selling when stock is depleted (negative)') }}</label>
                                </div>

                                <hr class="border-secondary my-4">
                                <h5 class="text-primary fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>
                                    {{ __('Security Console') }}</h5>

                                <div class="col-md-6">
                                    <label class="form-label text-muted">{{ __('Sensitive Operations PIN') }}</label>
                                    <div class="input-group">
                                        <input type="password" name="pos_refund_pin" id="pos_refund_pin"
                                            class="form-control bg-transparent text-body"
                                            value="{{ $settings['pos']['pos_refund_pin'] ?? '1234' }}">
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePinVisibility()">
                                            <i class="bi bi-eye" id="pin-icon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted small">
                                        {{ __('Used to confirm operations (returns, item deletion, price changes).') }}
                                    </div>
                                </div>

                                <!-- Phase 3: Price Override Security Settings -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label text-muted">{{ __('Manager PIN (for pricing)') }}</label>
                                    <div class="input-group">
                                        <input type="password" name="pos_manager_pin" id="pos_manager_pin"
                                            class="form-control bg-transparent text-body"
                                            value="{{ $settings['pos']['pos_manager_pin'] ?? '' }}"
                                            placeholder="{{ __('If empty, uses refund PIN') }}">
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="toggleManagerPinVisibility()">
                                            <i class="bi bi-eye" id="manager-pin-icon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted small">
                                        {{ __('Dedicated PIN for manager to approve price changes.') }}
                                    </div>
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label class="form-label text-muted">{{ __('Maximum Discount (%)') }}</label>
                                    <div class="input-group">
                                        <input type="number" step="1" min="0" max="100" name="pos_max_discount_percent"
                                            class="form-control bg-transparent text-body"
                                            value="{{ $settings['pos']['pos_max_discount_percent'] ?? 50 }}">
                                        <span class="input-group-text bg-transparent text-muted">%</span>
                                    </div>
                                    <div class="form-text text-muted small">
                                        {{ __('Maximum discount percentage allowed in POS.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Printing -->
                <div id="tab-printing" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">{{ __('Print Settings') }}</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Default Printer Type') }}</label>
                                <select name="printer_type" class="form-select bg-transparent text-body">
                                    <option value="thermal" {{ ($settings['printer']['printer_type'] ?? '') == 'thermal' ? 'selected' : '' }}>{{ __('Thermal Printer') }}</option>
                                    <option value="a4" {{ ($settings['printer']['printer_type'] ?? '') == 'a4' ? 'selected' : '' }}>{{ __('Laser/Inkjet Printer (A4)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Paper Width (thermal)') }}</label>
                                <select name="printer_paper_width" class="form-select bg-transparent text-body">
                                    <option value="80" {{ ($settings['printer']['printer_paper_width'] ?? '') == 80 ? 'selected' : '' }}>80mm (Standard)</option>
                                    <option value="58" {{ ($settings['printer']['printer_paper_width'] ?? '') == 58 ? 'selected' : '' }}>58mm (Small)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="printer_show_logo"
                                        name="printer_show_logo" {{ ($settings['printer']['printer_show_logo'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="printer_show_logo">{{ __('Print logo on receipt') }}</label>
                                </div>
                            </div>

                            <hr class="border-secondary my-4">
                            <h5 class="text-primary fw-bold mb-3"><i class="bi bi-palette me-2"></i> {{ __('Receipt Layout Customization') }}</h5>

                            <div class="col-12">
                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" id="pos_receipt_qr_enabled"
                                        name="pos_receipt_qr_enabled" {{ ($settings['printer']['pos_receipt_qr_enabled'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-body" for="pos_receipt_qr_enabled">{{ __('Show QR code at end of receipt') }}</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Custom Header Text') }}</label>
                                <textarea name="pos_receipt_header_custom" class="form-control bg-transparent text-body"
                                    rows="3"
                                    placeholder="{{ __('e.g., VAT registration number ...') }}">{{ $settings['printer']['pos_receipt_header_custom'] ?? '' }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted">{{ __('Custom Footer Text') }}</label>
                                <textarea name="pos_receipt_footer_custom" class="form-control bg-transparent text-body"
                                    rows="3"
                                    placeholder="{{ __('e.g., Thank you, follow us on Facebook ...') }}">{{ $settings['printer']['pos_receipt_footer_custom'] ?? '' }}</textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label text-muted">{{ __('QR Code Link (leave empty for auto-invoice link)') }}</label>
                                <input type="text" name="pos_receipt_qr_link" class="form-control bg-transparent text-body"
                                    placeholder="https://example.com"
                                    value="{{ $settings['printer']['pos_receipt_qr_link'] ?? '' }}">
                                <div class="form-text text-muted">{{ __('If left empty, the QR will link to the customer\'s invoice page automatically.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Accounting Integration -->
                <div id="tab-accounting" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <div
                            class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-2">
                            <h4 class="text-heading fw-bold m-0">{{ __('Accounting Integration') }}</h4>
                            <span class="badge bg-primary px-3 py-2">{{ __('Advanced Configuration') }}</span>
                        </div>

                        <div class="alert alert-info border-0 bg-primary bg-opacity-10 text-primary mb-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            {{ __('Control how financial transactions are posted to the Chart of Accounts. Please be careful when changing these settings.') }}
                        </div>

                        <!-- Section: Sales -->
                        <div class="mb-5">
                            <h5 class="text-info fw-bold mb-3"><i class="bi bi-cart-check me-2"></i> {{ __('Sales & Revenue') }}</h5>
                            <div class="row g-3">
                                @php
                                    $salesKeys = [
                                        'acc_ar' => __('Accounts Receivable (Customers)'),
                                        'acc_sales_revenue' => __('Sales Revenue Account'),
                                        'acc_sales_return' => __('Sales Returns Account'),
                                        'acc_tax_payable' => __('Output Tax Account (Sales Tax)'),
                                        'acc_tax_receivable' => __('Input Tax Account (Expense/Purchase Tax)'),
                                        'acc_sales_discount' => __('Sales Discount Account'),
                                        'acc_shipping_revenue' => __('Shipping & Delivery Revenue Account'),
                                        'acc_delivery_liability' => __('Delivery Deposits Account (for external orders)'),
                                        'acc_pending_delivery' => __('Delivery Reconciliation Account (Pending)'),
                                    ];
                                @endphp
                                @foreach($salesKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-body border-secondary">
                                            <option value="">-- {{ __('Select Account') }} --</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ ($settings['accounting'][$key] ?? '') == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">{{ __('Delivery Cost Accounting Method') }}</label>
                                    <select name="pos_delivery_accounting_method"
                                        class="form-select bg-transparent text-body border-info shadow-sm">
                                        <option value="revenue" {{ ($settings['accounting']['pos_delivery_accounting_method'] ?? 'revenue') == 'revenue' ? 'selected' : '' }}>{{ __('Revenue for the Company') }}
                                        </option>
                                        <option value="liability" {{ ($settings['accounting']['pos_delivery_accounting_method'] ?? 'revenue') == 'liability' ? 'selected' : '' }}>{{ __('Deposits for Drivers (Liability)') }}
                                        </option>
                                    </select>
                                    <div class="form-text text-muted small mt-1">
                                        <i class="bi bi-info-circle me-1"></i>
                                        {{ __('Choose "Deposits" if delivery fees go as driver liability, or "Revenue" if it\'s income for you.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Purchasing -->
                        <div class="mb-5">
                            <h5 class="text-warning fw-bold mb-3"><i class="bi bi-bag-plus me-2"></i> {{ __('Purchases & Expenses') }}</h5>
                            <div class="row g-3">
                                @php
                                    $purchaseKeys = [
                                        'acc_ap' => __('Accounts Payable (Suppliers)'),
                                        'acc_tax_receivable' => __('Input Tax Account (Purchase Tax)'),
                                        'acc_purchase_discount' => __('Purchase Discount Account'),
                                    ];
                                @endphp
                                @foreach($purchaseKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-body border-secondary">
                                            <option value="">-- {{ __('Select Account') }} --</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ ($settings['accounting'][$key] ?? '') == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Section: Inventory -->
                        <div class="mb-5">
                            <h5 class="text-success fw-bold mb-3"><i class="bi bi-box-seam me-2"></i> {{ __('Inventory & COGS') }}</h5>
                            <div class="row g-3">
                                @php
                                    $inventoryKeys = [
                                        'acc_inventory' => __('Inventory Account'),
                                        'acc_cogs' => __('Cost of Goods Sold (COGS) Account'),
                                        'acc_inventory_adj' => __('Inventory Adjustment Account'),
                                        'acc_purchase_suspense' => __('Purchase Suspense Account'),
                                        'acc_inventory_other' => __('Other Inventory Differences Account'),
                                    ];
                                @endphp
                                @foreach($inventoryKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-body border-secondary">
                                            <option value="">-- {{ __('Select Account') }} --</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ ($settings['accounting'][$key] ?? '') == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Section: Payments -->
                        <div class="mb-5">
                            <h5 class="text-danger fw-bold mb-3"><i class="bi bi-cash-stack me-2"></i> {{ __('Payment Methods & Cash') }}</h5>
                            <div class="row g-3">
                                @php
                                    $paymentKeys = [
                                        'acc_cash' => __('Main Cash Account'),
                                        'acc_bank' => __('Main Bank/Visa Account'),
                                        'acc_pos_change' => __('Change Balance Account'),
                                    ];
                                @endphp
                                @foreach($paymentKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-body border-secondary">
                                            <option value="">-- {{ __('Select Account') }} --</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ ($settings['accounting'][$key] ?? '') == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Section: HR -->
                        <div class="mb-5">
                            <h5 class="text-light fw-bold mb-3"><i class="bi bi-people me-2"></i> {{ __('Salaries & HR') }}</h5>
                            <div class="row g-3">
                                @php
                                    $hrKeys = [
                                        'acc_salaries_exp' => __('Salary Expense Account'),
                                        'acc_salaries_payable' => __('Salaries & Wages Payable Account'),
                                        'acc_employee_advances' => __('Employee Advances'),
                                    ];
                                @endphp
                                @foreach($hrKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-body border-secondary">
                                            <option value="">-- {{ __('Select Account') }} --</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ ($settings['accounting'][$key] ?? '') == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Section: System -->
                        <div class="mb-0">
                            <h5 class="text-heading-50 fw-bold mb-3"><i class="bi bi-gear-fill me-2"></i> {{ __('Other System Settings') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">{{ __('Opening Balances Account') }}</label>
                                    <select name="acc_opening_balance"
                                        class="form-select bg-transparent text-body border-secondary">
                                        <option value="">-- {{ __('Select Account') }} --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->code }}" {{ ($settings['accounting']['acc_opening_balance'] ?? '') == $account->code ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: System Reset & Backup -->
                <div id="tab-system" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">{{ __('Backup & Reset') }}</h4>

                        <div class="row g-4">
                            <!-- Backup Section -->
                            <div class="col-12">
                                <h5 class="text-info fw-bold mb-3"><i class="bi bi-cloud-arrow-up me-2"></i> {{ __('Backup') }}</h5>
                                <div class="bg-info bg-opacity-10 p-3 rounded-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-info fw-bold">{{ __('Data Protection') }}</div>
                                            <div class="text-muted small">{{ __('It is recommended to take periodic backups to ensure data safety.') }}</div>
                                        </div>
                                        <a href="{{ route('settings.backup.index') }}" class="btn btn-info px-4">{{ __('Manage Backups') }}</a>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-secondary">

                            <!-- Danger Zone Section -->
                            <div class="col-12">
                                <h5 class="text-danger fw-bold mb-3"><i class="bi bi-exclamation-triangle me-2"></i> {{ __('Danger Zone') }}</h5>
                                <div class="border border-danger border-opacity-25 bg-danger bg-opacity-10 p-4 rounded-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-9">
                                            <h6 class="text-heading fw-bold">{{ __('System Safe Wipe') }}</h6>
                                            <p class="text-body-50 small mb-0">
                                                {{ __('This will delete all sales, purchases, inventory, running accounts, and journal entries.') }}
                                                <br>
                                                <strong>{{ __('Will be preserved:') }}</strong> {{ __('Chart of Accounts, Users, Permissions, and System Settings.') }}
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button type="button" class="btn btn-danger w-100 fw-bold shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#systemResetModal">
                                                {{ __('Reset Operations Now') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button Sticky Footer -->
                <div class="glass-card p-3 mt-4 text-end sticky-bottom" style="bottom: 20px; z-index: 10;">
                    <button type="submit" class="btn btn-primary btn-lg shadow-lg px-5">
                        <i class="bi bi-save me-2"></i> {{ __('Save Settings') }}
                    </button>
                </div>
            </div>
        </div>
    </form>


    <style>
        

        .list-group-item {
            border: none;
            color: rgba(255, 255, 255, 0.6);
            background: transparent;
            padding: 1rem 1.5rem;
            transition: all 0.2s;
            border-right: 4px solid transparent;
        }

        .list-group-item:hover {
            background: var(--btn-glass-bg);
            color: var(--text-primary);
        }

        .list-group-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
            border-right-color: #60a5fa;
            font-weight: bold;
        }

        /* Form Controls Overrides for Dark Glass Theme */
        .form-control,
        .form-select {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(17, 24, 39, 0.9);
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.25);
            color: var(--text-primary);
        }

        /* Fix for invisible select options in dark theme */
        select option {
            background-color: #111827 !important;
            color: var(--text-primary); !important;
        }
    </style>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.settings-tab').forEach(el => el.classList.add('d-none'));
            // Remove active class from buttons
            document.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));

            // Show selected tab
            document.getElementById('tab-' + tabName).classList.remove('d-none');
            // Set button to active (This requires finding the specific button, but for simplicity assuming order match or using event target in a more complex setup)
            // Simpler way:
            const buttons = document.querySelectorAll('.list-group-item');
            if (tabName === 'company') buttons[0].classList.add('active');
            if (tabName === 'finance') buttons[1].classList.add('active');
            if (tabName === 'sales') buttons[2].classList.add('active');
            if (tabName === 'pos') buttons[3].classList.add('active');
            if (tabName === 'printing') buttons[4].classList.add('active');
            if (tabName === 'accounting') buttons[5].classList.add('active');
            if (tabName === 'system') buttons[6].classList.add('active');
        }

        // Handle System Reset PIN Modal
        document.addEventListener('DOMContentLoaded', function () {
            // Check if there are validation errors for the PIN
            @if($errors->has('pin') || $errors->has('error'))
                showTab('system');
                var modal = new bootstrap.Modal(document.getElementById('systemResetModal'));
                modal.show();
            @endif
                                                                        });

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('logo-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function togglePinVisibility() {
            const input = document.getElementById('pos_refund_pin');
            const icon = document.getElementById('pin-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        function toggleManagerPinVisibility() {
            const input = document.getElementById('pos_manager_pin');
            const icon = document.getElementById('manager-pin-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>

    <!-- System Reset Modal -->
    <div class="modal fade" id="systemResetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-danger border-opacity-50">
                <div class="modal-header border-bottom border-secondary border-opacity-10 border-opacity-10">
                    <h5 class="modal-title text-danger fw-bold"><i class="bi bi-shield-lock me-2"></i> تأكيد تصفير النظام
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="text-danger display-4 mb-3">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                    <h5 class="text-heading mb-3">هل أنت متأكد من مسح كافة العمليات؟</h5>
                    <p class="text-body-50 small mb-4 text-center">
                        هذا الإجراء سيقوم بتصفير كافة الأرصدة والكميات وحذف جميع الفواتير والقيود.
                        <br>
                        <span class="text-warning">لا يمكن التراجع عن هذه الخطوة!</span>
                    </p>

                    <form action="{{ route('settings.reset') }}" method="POST">
                        @csrf
                        <div class="mb-4 text-start">
                            <label class="form-label text-muted small">أدخل رمز مرور المدير (Admin PIN) للمتابعة:</label>
                            <input type="password" name="pin"
                                class="form-control form-control-lg bg-transparent text-body border-danger text-center font-monospace"
                                placeholder="****" required autofocus>
                            @error('pin')
                                <div class="text-danger small mt-2"><i class="bi bi-x-circle me-1"></i> {{ $message }}</div>
                            @enderror
                            @error('error')
                                <div class="text-danger small mt-2"><i class="bi bi-x-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg fw-bold">تأكيد المسح النهائي</button>
                            <button type="button" class="btn btn-glass-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection