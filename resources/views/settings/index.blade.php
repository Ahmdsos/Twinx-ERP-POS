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