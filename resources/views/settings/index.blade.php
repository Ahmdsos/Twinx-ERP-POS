@extends('layouts.app')

@section('title', 'إعدادات النظام - System Settings')

@section('content')
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-white fw-bold"><i class="bi bi-gear-wide-connected me-2"></i> إعدادات النظام</h2>
            <p class="text-white-50">التحكم الكامل في خيارات البرنامج، الضرائب، الطباعة، ونقاط البيع.</p>
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
                            <i class="bi bi-building me-2"></i> بيانات الشركة
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('finance')">
                            <i class="bi bi-cash-stack me-2"></i> المالية والضرائب
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('sales')">
                            <i class="bi bi-receipt me-2"></i> المبيعات والفواتير
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('pos')">
                            <i class="bi bi-shop me-2"></i> نقاط البيع (POS)
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" onclick="showTab('printing')">
                            <i class="bi bi-printer me-2"></i> إعدادات الطباعة
                        </button>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="showTab('accounting')">
                            <i class="bi bi-journal-check me-2"></i> الربط المحاسبي (Integration)
                        </button>
                        <button type="button" class="list-group-item list-group-item-action text-danger"
                            onclick="showTab('system')">
                            <i class="bi bi-shield-slash me-2"></i> النسخ الاحتياطي والتصفير
                        </button>

                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9">

                <!-- Tab: Company Info -->
                <div id="tab-company" class="settings-tab">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2">بيانات المؤسسة</h4>

                        <div class="mb-4 text-center">
                            <div class="mb-3 position-relative d-inline-block">
                                @if($settings['company']['company_logo'] ?? false)
                                    <img src="{{ Storage::url($settings['company']['company_logo']) }}" id="logo-preview"
                                        class="rounded-circle border border-2 border-white"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle border border-2 border-white d-flex align-items-center justify-content-center bg-dark text-white-50"
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
                            <p class="text-white-50 small">شعار المؤسسة (يظهر في الفواتير والتقارير)</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">اسم المؤسسة</label>
                                <input type="text" name="company_name"
                                    class="form-control form-control-lg bg-transparent text-white"
                                    value="{{ $settings['company']['company_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">الرقم الضريبي</label>
                                <input type="text" name="company_tax_number"
                                    class="form-control form-control-lg bg-transparent text-white"
                                    value="{{ $settings['company']['company_tax_number'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50">العنوان</label>
                                <textarea name="company_address" class="form-control bg-transparent text-white"
                                    rows="2">{{ $settings['company']['company_address'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">رقم الهاتف</label>
                                <input type="text" name="company_phone" class="form-control bg-transparent text-white"
                                    value="{{ $settings['company']['company_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">البريد الإلكتروني</label>
                                <input type="email" name="company_email" class="form-control bg-transparent text-white"
                                    value="{{ $settings['company']['company_email'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Finance & Tax -->
                <div id="tab-finance" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2">المالية والضرائب</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">العملة الأساسية</label>
                                <select name="currency_code" class="form-select bg-transparent text-white">
                                    <option value="EGP" {{ ($settings['currency']['currency_code'] ?? '') == 'EGP' ? 'selected' : '' }}>EGP - جنيه مصري</option>
                                    <option value="USD" {{ ($settings['currency']['currency_code'] ?? '') == 'USD' ? 'selected' : '' }}>USD - دولار أمريكي</option>
                                    <option value="SAR" {{ ($settings['currency']['currency_code'] ?? '') == 'SAR' ? 'selected' : '' }}>SAR - ريال سعودي</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">رمز العملة</label>
                                <input type="text" name="currency_symbol" class="form-control bg-transparent text-white"
                                    value="{{ $settings['currency']['currency_symbol'] ?? 'ج.م' }}">
                            </div>

                            <hr class="border-secondary my-4">

                            <div class="col-md-6">
                                <label class="form-label text-white-50">نسبة الضريبة الافتراضية (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="default_tax_rate"
                                        class="form-control bg-transparent text-white"
                                        value="{{ $settings['tax']['default_tax_rate'] ?? 14 }}">
                                    <span class="input-group-text bg-transparent text-white-50">%</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div
                                    class="form-check form-switch p-0 d-flex gap-3 align-items-center custom-switch-wrapper">
                                    <label class="form-check-label text-white-50 m-0 order-1" for="tax_inclusive">الأسعار
                                        شاملة الضريبة؟</label>
                                    <input class="form-check-input ms-0 me-2 order-2" type="checkbox" id="tax_inclusive"
                                        name="tax_inclusive" {{ ($settings['tax']['tax_inclusive'] ?? false) ? 'checked' : '' }}>
                                </div>
                                <div class="form-text text-white-50 mt-1 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    عند التفعيل، سيقوم النظام باحتساب الضريبة كجزء من سعر المنتج، وليس إضافة عليه.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Sales & Invoices -->
                <div id="tab-sales" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2">إعدادات الفواتير</h4>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white-50">بادئة رقم الفاتورة (Prefix)</label>
                                <input type="text" name="invoice_prefix" class="form-control bg-transparent text-white"
                                    value="{{ $settings['invoice']['invoice_prefix'] ?? 'INV' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">الرقم التسلسلي التالي</label>
                                <input type="number" name="invoice_next_number"
                                    class="form-control bg-transparent text-white"
                                    value="{{ $settings['invoice']['invoice_next_number'] ?? 1 }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label text-white-50">ملاحظات تذييل الفاتورة (Footer Note)</label>
                                <textarea name="invoice_footer" class="form-control bg-transparent text-white"
                                    rows="3">{{ $settings['invoice']['invoice_footer'] ?? 'شكراً لتعاملكم معنا!' }}</textarea>
                                <div class="form-text text-white-50">تظهر أسفل كل فاتورة مطبوعة (مثل: البضاعة المباعة لا ترد
                                    ولا تستبدل بعد 14 يوم).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: POS -->
                <div id="tab-pos" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2">نقطة البيع (POS)</h4>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pos_print_receipt"
                                        name="pos_print_receipt" {{ ($settings['pos']['pos_print_receipt'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-white" for="pos_print_receipt">طباعة الإيصال
                                        تلقائياً بعد البيع</label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pos_allow_negative_stock"
                                        name="pos_allow_negative_stock" {{ ($settings['pos']['pos_allow_negative_stock'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label text-white" for="pos_allow_negative_stock">السماح بالبيع
                                        عند نفاد المخزون (بالسالب)</label>
                                </div>

                                <hr class="border-secondary my-4">
                                <h5 class="text-primary fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i> لوحة التحكم
                                    الأمنية (Security Console)</h5>

                                <div class="col-md-6">
                                    <label class="form-label text-white-50">كلمة مرور العمليات الحساسة (PIN)</label>
                                    <div class="input-group">
                                        <input type="password" name="pos_refund_pin" id="pos_refund_pin"
                                            class="form-control bg-transparent text-white"
                                            value="{{ $settings['pos']['pos_refund_pin'] ?? '1234' }}">
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePinVisibility()">
                                            <i class="bi bi-eye" id="pin-icon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-white-50 small">
                                        تستخدم لتأكيد عمليات (المرتجع، حذف الأصناف، تعديل الأسعار).
                                    </div>
                                </div>

                                <!-- Phase 3: Price Override Security Settings -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label text-white-50">كلمة مرور المدير (للتسعير)</label>
                                    <div class="input-group">
                                        <input type="password" name="pos_manager_pin" id="pos_manager_pin"
                                            class="form-control bg-transparent text-white"
                                            value="{{ $settings['pos']['pos_manager_pin'] ?? '' }}"
                                            placeholder="إذا فارغ يستخدم PIN المرتجعات">
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="toggleManagerPinVisibility()">
                                            <i class="bi bi-eye" id="manager-pin-icon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-white-50 small">
                                        PIN خاص للمدير لاعتماد تغيير الأسعار.
                                    </div>
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label class="form-label text-white-50">الحد الأقصى للخصم (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="1" min="0" max="100" name="pos_max_discount_percent"
                                            class="form-control bg-transparent text-white"
                                            value="{{ $settings['pos']['pos_max_discount_percent'] ?? 50 }}">
                                        <span class="input-group-text bg-transparent text-white-50">%</span>
                                    </div>
                                    <div class="form-text text-white-50 small">
                                        أقصى نسبة خصم مسموحة في نقاط البيع.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Printing -->
                <div id="tab-printing" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2">إعدادات الطباعة</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">نوع الطابعة الافتراضي</label>
                                <select name="printer_type" class="form-select bg-transparent text-white">
                                    <option value="thermal" {{ ($settings['printer']['printer_type'] ?? '') == 'thermal' ? 'selected' : '' }}>طابعة حرارية (ريسيت) - Thermal</option>
                                    <option value="a4" {{ ($settings['printer']['printer_type'] ?? '') == 'a4' ? 'selected' : '' }}>طابعة ليزر (A4) - Laser/Inkjet</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">عرض الورق (للطباعة الحرارية)</label>
                                <select name="printer_paper_width" class="form-select bg-transparent text-white">
                                    <option value="80" {{ ($settings['printer']['printer_paper_width'] ?? '') == 80 ? 'selected' : '' }}>80mm (Standard)</option>
                                    <option value="58" {{ ($settings['printer']['printer_paper_width'] ?? '') == 58 ? 'selected' : '' }}>58mm (Small)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="printer_show_logo"
                                        name="printer_show_logo" {{ ($settings['printer']['printer_show_logo'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-white" for="printer_show_logo">طباعة الشعار على
                                        الإيصال</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Accounting Integration -->
                <div id="tab-accounting" class="settings-tab d-none">
                    <div class="glass-card p-4 mb-4">
                        <div
                            class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-2">
                            <h4 class="text-white fw-bold m-0">الربط المحاسبي (Logic Mapping)</h4>
                            <span class="badge bg-primary px-3 py-2">Advanced Configuration</span>
                        </div>

                        <div class="alert alert-info border-0 bg-primary bg-opacity-10 text-primary mb-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            تحكم في كيفية ترحيل العمليات المالية لـ شجرة الحسابات. يرجى الحذر عند تغيير هذه الإعدادات.
                        </div>

                        <!-- Section: Sales -->
                        <div class="mb-5">
                            <h5 class="text-info fw-bold mb-3"><i class="bi bi-cart-check me-2"></i> المبيعات والإيرادات
                            </h5>
                            <div class="row g-3">
                                @php
                                    $salesKeys = [
                                        'acc_ar' => 'حساب المدينين (العملاء)',
                                        'acc_sales_revenue' => 'حساب إيرادات المبيعات',
                                        'acc_sales_return' => 'حساب مرتجعات المبيعات',
                                        'acc_tax_payable' => 'حساب ضريبة المخرجات (Sales Tax)',
                                        'acc_tax_receivable' => 'حساب ضريبة المدخلات (Expense/Purchase Tax)',
                                        'acc_sales_discount' => 'حساب مسموحات المبيعات (الخصم)',
                                        'acc_shipping_revenue' => 'حساب إيرادات الشحن والتوصيل',
                                        'acc_pending_delivery' => 'حساب تسوية الدليفري (Pending)',
                                    ];
                                @endphp
                                @foreach($salesKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-white border-secondary">
                                            <option value="">-- اختار الحساب --</option>
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

                        <!-- Section: Purchasing -->
                        <div class="mb-5">
                            <h5 class="text-warning fw-bold mb-3"><i class="bi bi-bag-plus me-2"></i> المشتريات والمصروفات
                            </h5>
                            <div class="row g-3">
                                @php
                                    $purchaseKeys = [
                                        'acc_ap' => 'حساب الدائنين (الموردين)',
                                        'acc_tax_receivable' => 'حساب ضريبة المدخلات (Purchase Tax)',
                                        'acc_purchase_discount' => 'حساب مسموحات المشتريات (الخصم)',
                                    ];
                                @endphp
                                @foreach($purchaseKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-white border-secondary">
                                            <option value="">-- اختار الحساب --</option>
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
                            <h5 class="text-success fw-bold mb-3"><i class="bi bi-box-seam me-2"></i> المخزون وتكلفة
                                المبيعات</h5>
                            <div class="row g-3">
                                @php
                                    $inventoryKeys = [
                                        'acc_inventory' => 'حساب مخزون البضاعة',
                                        'acc_cogs' => 'حساب تكلفة البضاعة المباعة (COGS)',
                                        'acc_inventory_adj' => 'حساب تسويات المخزون (Inventory Adj)',
                                        'acc_purchase_suspense' => 'حساب المشتريات المعلقة (Suspense)',
                                        'acc_inventory_other' => 'حساب فروقات المخزون الأخرى',
                                    ];
                                @endphp
                                @foreach($inventoryKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-white border-secondary">
                                            <option value="">-- اختار الحساب --</option>
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
                            <h5 class="text-danger fw-bold mb-3"><i class="bi bi-cash-stack me-2"></i> طرق السداد والنقدية
                            </h5>
                            <div class="row g-3">
                                @php
                                    $paymentKeys = [
                                        'acc_cash' => 'حساب الخزينة الرئيسي (Cash)',
                                        'acc_bank' => 'حساب البنك / الفيزا الرئيسي',
                                        'acc_pos_change' => 'حساب تسوية الفكة (Change Balance)',
                                    ];
                                @endphp
                                @foreach($paymentKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-white border-secondary">
                                            <option value="">-- اختار الحساب --</option>
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
                            <h5 class="text-light fw-bold mb-3"><i class="bi bi-people me-2"></i> الرواتب وشؤون الموظفين
                            </h5>
                            <div class="row g-3">
                                @php
                                    $hrKeys = [
                                        'acc_salaries_exp' => 'حساب مصروف الرواتب',
                                        'acc_salaries_payable' => 'حساب الرواتب والأجور المستحقة',
                                    ];
                                @endphp
                                @foreach($hrKeys as $key => $label)
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50">{{ $label }}</label>
                                        <select name="{{ $key }}"
                                            class="form-select bg-transparent text-white border-secondary">
                                            <option value="">-- اختار الحساب --</option>
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
                            <h5 class="text-white-50 fw-bold mb-3"><i class="bi bi-gear-fill me-2"></i> إعدادات النظام
                                الأخرى</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-white-50">حساب أرصدة أول المدة (Opening Balances)</label>
                                    <select name="acc_opening_balance"
                                        class="form-select bg-transparent text-white border-secondary">
                                        <option value="">-- اختار الحساب --</option>
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
                        <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2">النسخ الاحتياطي وتصفير
                            النظام</h4>

                        <div class="row g-4">
                            <!-- Backup Section -->
                            <div class="col-12">
                                <h5 class="text-info fw-bold mb-3"><i class="bi bi-cloud-arrow-up me-2"></i> النسخ الاحتياطي
                                    (Backup)</h5>
                                <div class="bg-info bg-opacity-10 p-3 rounded-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-info fw-bold">حماية البيانات</div>
                                            <div class="text-white-50 small">يُنصح بأخذ نسخة احتياطية بشكل دوري لضمان سلامة
                                                البيانات.</div>
                                        </div>
                                        <a href="{{ route('settings.backup.index') }}" class="btn btn-info px-4">إدارة النسخ
                                            الاحتياطية</a>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-secondary">

                            <!-- Danger Zone Section -->
                            <div class="col-12">
                                <h5 class="text-danger fw-bold mb-3"><i class="bi bi-exclamation-triangle me-2"></i> منطقة
                                    الخطر (Danger Zone)</h5>
                                <div class="border border-danger border-opacity-25 bg-danger bg-opacity-10 p-4 rounded-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-9">
                                            <h6 class="text-white fw-bold">تصفير بيانات العمليات (System Safe Wipe)</h6>
                                            <p class="text-white-50 small mb-0">
                                                سيقوم هذا الإجراء بمسح كافة المبيعات، المشتريات، المخزون، الحسابات الجارية،
                                                والقيود المحاسبية.
                                                <br>
                                                <strong>سيتم الحفاظ على:</strong> دليل الحسابات، المستخدمين، والصلاحيات،
                                                وإعدادات النظام.
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button type="button" class="btn btn-danger w-100 fw-bold shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#systemResetModal">
                                                تصفير العمليات الآن
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
                        <i class="bi bi-save me-2"></i> حفظ الإعدادات
                    </button>
                </div>
            </div>
        </div>
    </form>


    <style>
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }

        .list-group-item {
            border: none;
            color: rgba(255, 255, 255, 0.6);
            background: transparent;
            padding: 1rem 1.5rem;
            transition: all 0.2s;
            border-right: 4px solid transparent;
        }

        .list-group-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
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
            color: white;
        }

        /* Fix for invisible select options in dark theme */
        select option {
            background-color: #111827 !important;
            color: white !important;
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
                <div class="modal-header border-bottom border-white border-opacity-10">
                    <h5 class="modal-title text-danger fw-bold"><i class="bi bi-shield-lock me-2"></i> تأكيد تصفير النظام
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="text-danger display-4 mb-3">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                    <h5 class="text-white mb-3">هل أنت متأكد من مسح كافة العمليات؟</h5>
                    <p class="text-white-50 small mb-4 text-center">
                        هذا الإجراء سيقوم بتصفير كافة الأرصدة والكميات وحذف جميع الفواتير والقيود.
                        <br>
                        <span class="text-warning">لا يمكن التراجع عن هذه الخطوة!</span>
                    </p>

                    <form action="{{ route('settings.reset') }}" method="POST">
                        @csrf
                        <div class="mb-4 text-start">
                            <label class="form-label text-white-50 small">أدخل رمز مرور المدير (Admin PIN) للمتابعة:</label>
                            <input type="password" name="pin"
                                class="form-control form-control-lg bg-transparent text-white border-danger text-center font-monospace"
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
                            <button type="button" class="btn btn-glass-outline" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection