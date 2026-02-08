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
            if (tabName === 'system') buttons[5].classList.add('active');
        }

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
@endsection