@extends('layouts.app')

@section('title', 'الإعدادات - Twinx ERP')
@section('page-title', 'إعدادات النظام')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإعدادات</li>
@endsection

@section('content')
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Company Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-building me-2"></i>معلومات الشركة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" class="form-control" name="company_name"
                                    value="{{ $settings['company']['company_name'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" name="company_tax_number"
                                    value="{{ $settings['company']['company_tax_number'] ?? '' }}" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الهاتف</label>
                                <input type="text" class="form-control" name="company_phone"
                                    value="{{ $settings['company']['company_phone'] ?? '' }}" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="company_email"
                                    value="{{ $settings['company']['company_email'] ?? '' }}" dir="ltr">
                            </div>
                            <div class="col-12">
                                <label class="form-label">العنوان</label>
                                <textarea class="form-control" name="company_address"
                                    rows="2">{{ $settings['company']['company_address'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">شعار الشركة</label>
                                <input type="file" class="form-control" name="company_logo" accept="image/*">
                                @if(!empty($settings['company']['company_logo']))
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($settings['company']['company_logo']) }}" alt="Logo"
                                            class="img-thumbnail" style="max-height: 80px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-percent me-2"></i>إعدادات الضريبة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">نسبة الضريبة الافتراضية (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="default_tax_rate"
                                        value="{{ $settings['tax']['default_tax_rate'] ?? 14 }}" step="0.01" min="0"
                                        max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="tax_inclusive" id="tax_inclusive"
                                        value="1" {{ ($settings['tax']['tax_inclusive'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tax_inclusive">
                                        الأسعار شاملة الضريبة افتراضياً
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>إعدادات الفواتير</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">بادئة رقم الفاتورة</label>
                                <input type="text" class="form-control" name="invoice_prefix"
                                    value="{{ $settings['invoice']['invoice_prefix'] ?? 'INV' }}" maxlength="10" dir="ltr">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الرقم التالي</label>
                                <input type="number" class="form-control" name="invoice_next_number"
                                    value="{{ $settings['invoice']['invoice_next_number'] ?? 1 }}" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">معاينة</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ ($settings['invoice']['invoice_prefix'] ?? 'INV') }}-{{ str_pad($settings['invoice']['invoice_next_number'] ?? 1, 6, '0', STR_PAD_LEFT) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">تذييل الفاتورة</label>
                                <textarea class="form-control" name="invoice_footer" rows="2"
                                    placeholder="مثال: شكراً لتعاملكم معنا">{{ $settings['invoice']['invoice_footer'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- POS Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cart-check me-2"></i>إعدادات نقطة البيع</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="pos_allow_negative_stock"
                                id="pos_allow_negative_stock" value="1" {{ ($settings['pos']['pos_allow_negative_stock'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pos_allow_negative_stock">
                                السماح بالبيع بمخزون سالب
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="pos_print_receipt" id="pos_print_receipt"
                                value="1" {{ ($settings['pos']['pos_print_receipt'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pos_print_receipt">
                                طباعة الإيصال تلقائياً
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Printer Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-printer me-2"></i>إعدادات الطابعة الحرارية</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">نوع الطابعة</label>
                            <select class="form-select" name="printer_type">
                                <option value="network" {{ ($settings['printer']['printer_type'] ?? '') === 'network' ? 'selected' : '' }}>شبكية (Network)</option>
                                <option value="usb" {{ ($settings['printer']['printer_type'] ?? '') === 'usb' ? 'selected' : '' }}>USB</option>
                                <option value="bluetooth" {{ ($settings['printer']['printer_type'] ?? '') === 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">عنوان IP أو اسم الطابعة</label>
                            <input type="text" class="form-control" name="printer_address"
                                value="{{ $settings['printer']['printer_address'] ?? '' }}"
                                placeholder="192.168.1.100 أو Printer Name" dir="ltr">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">عرض الورق</label>
                            <select class="form-select" name="printer_width">
                                <option value="80" {{ ($settings['printer']['printer_width'] ?? 80) == 80 ? 'selected' : '' }}>80mm</option>
                                <option value="58" {{ ($settings['printer']['printer_width'] ?? 80) == 58 ? 'selected' : '' }}>58mm</option>
                            </select>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="printer_cut_paper" id="printer_cut_paper"
                                value="1" {{ ($settings['printer']['printer_cut_paper'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="printer_cut_paper">
                                قص الورق تلقائياً
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Currency Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>إعدادات العملة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">العملة الأساسية</label>
                                <select class="form-select" name="currency_code">
                                    <option value="EGP" {{ ($settings['currency']['currency_code'] ?? 'EGP') === 'EGP' ? 'selected' : '' }}>جنيه مصري (EGP)</option>
                                    <option value="SAR" {{ ($settings['currency']['currency_code'] ?? '') === 'SAR' ? 'selected' : '' }}>ريال سعودي (SAR)</option>
                                    <option value="AED" {{ ($settings['currency']['currency_code'] ?? '') === 'AED' ? 'selected' : '' }}>درهم إماراتي (AED)</option>
                                    <option value="USD" {{ ($settings['currency']['currency_code'] ?? '') === 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                                    <option value="EUR" {{ ($settings['currency']['currency_code'] ?? '') === 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">رمز العملة</label>
                                <input type="text" class="form-control" name="currency_symbol"
                                    value="{{ $settings['currency']['currency_symbol'] ?? 'ج.م' }}" maxlength="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">عدد الخانات العشرية</label>
                                <select class="form-select" name="currency_decimals">
                                    <option value="0" {{ ($settings['currency']['currency_decimals'] ?? 2) == 0 ? 'selected' : '' }}>0</option>
                                    <option value="2" {{ ($settings['currency']['currency_decimals'] ?? 2) == 2 ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ ($settings['currency']['currency_decimals'] ?? 2) == 3 ? 'selected' : '' }}>3</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الفاصل العشري</label>
                                <select class="form-select" name="currency_decimal_separator">
                                    <option value="." {{ ($settings['currency']['currency_decimal_separator'] ?? '.') === '.' ? 'selected' : '' }}>نقطة (.)</option>
                                    <option value="," {{ ($settings['currency']['currency_decimal_separator'] ?? '.') === ',' ? 'selected' : '' }}>فاصلة (,)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">فاصل الآلاف</label>
                                <select class="form-select" name="currency_thousands_separator">
                                    <option value="," {{ ($settings['currency']['currency_thousands_separator'] ?? ',') === ',' ? 'selected' : '' }}>فاصلة (,)</option>
                                    <option value="." {{ ($settings['currency']['currency_thousands_separator'] ?? ',') === '.' ? 'selected' : '' }}>نقطة (.)</option>
                                    <option value=" " {{ ($settings['currency']['currency_thousands_separator'] ?? ',') === ' ' ? 'selected' : '' }}>مسافة</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>إعدادات البريد الإلكتروني</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            يُستخدم لإرسال الفواتير وكشوف الحساب للعملاء والموردين
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">خادم SMTP</label>
                                <input type="text" class="form-control" name="email_smtp_host"
                                    value="{{ $settings['email']['email_smtp_host'] ?? '' }}" placeholder="smtp.gmail.com"
                                    dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">منفذ SMTP</label>
                                <input type="number" class="form-control" name="email_smtp_port"
                                    value="{{ $settings['email']['email_smtp_port'] ?? 587 }}" placeholder="587" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="email_username"
                                    value="{{ $settings['email']['email_username'] ?? '' }}" placeholder="your@email.com"
                                    dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="email_password"
                                    value="{{ $settings['email']['email_password'] ?? '' }}" placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">اسم المُرسِل</label>
                                <input type="text" class="form-control" name="email_from_name"
                                    value="{{ $settings['email']['email_from_name'] ?? '' }}" placeholder="Twinx ERP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">التشفير</label>
                                <select class="form-select" name="email_encryption">
                                    <option value="tls" {{ ($settings['email']['email_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ ($settings['email']['email_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ ($settings['email']['email_encryption'] ?? '') === '' ? 'selected' : '' }}>بدون</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-database me-2"></i>النسخ الاحتياطي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">النسخ الاحتياطي التلقائي</label>
                                <select class="form-select" name="backup_frequency">
                                    <option value="disabled" {{ ($settings['backup']['backup_frequency'] ?? 'daily') === 'disabled' ? 'selected' : '' }}>معطل</option>
                                    <option value="daily" {{ ($settings['backup']['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>يومياً</option>
                                    <option value="weekly" {{ ($settings['backup']['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>أسبوعياً</option>
                                    <option value="monthly" {{ ($settings['backup']['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>شهرياً</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">عدد النسخ المحتفظ بها</label>
                                <input type="number" class="form-control" name="backup_keep_count"
                                    value="{{ $settings['backup']['backup_keep_count'] ?? 7 }}" min="1" max="30">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">مسار حفظ النسخ</label>
                                <input type="text" class="form-control" name="backup_path"
                                    value="{{ $settings['backup']['backup_path'] ?? 'backups' }}" placeholder="backups"
                                    dir="ltr">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="backup_notify" id="backup_notify"
                                        value="1" {{ ($settings['backup']['backup_notify'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="backup_notify">
                                        إرسال إشعار بريدي عند اكتمال النسخ
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex gap-2">
                            <a href="{{ route('settings.backup.create') }}" class="btn btn-success"
                                onclick="return confirm('هل تريد إنشاء نسخة احتياطية الآن؟')">
                                <i class="bi bi-download me-1"></i>إنشاء نسخة احتياطية الآن
                            </a>
                            <a href="{{ route('settings.backup.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-folder me-1"></i>عرض النسخ الاحتياطية
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg me-1"></i>حفظ الإعدادات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection