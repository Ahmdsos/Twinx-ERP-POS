@extends('layouts.app')

@section('title', 'إعدادات الإشعارات - Twinx ERP')
@section('page-title', 'إعدادات الإشعارات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">الإعدادات</a></li>
    <li class="breadcrumb-item active">الإشعارات</li>
@endsection

@section('content')
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Email Notifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>إشعارات البريد الإلكتروني</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_low_stock"
                                        name="email_low_stock" value="1">
                                    <label class="form-check-label" for="email_low_stock">تنبيه نقص المخزون</label>
                                    <small class="d-block text-muted">إرسال بريد عند انخفاض المخزون عن الحد الأدنى</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_new_order"
                                        name="email_new_order" value="1">
                                    <label class="form-check-label" for="email_new_order">طلب جديد</label>
                                    <small class="d-block text-muted">إرسال بريد عند استلام طلب جديد</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_daily_report"
                                        name="email_daily_report" value="1">
                                    <label class="form-check-label" for="email_daily_report">التقرير اليومي</label>
                                    <small class="d-block text-muted">إرسال ملخص يومي بالمبيعات والمشتريات</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_overdue_invoice"
                                        name="email_overdue_invoice" value="1">
                                    <label class="form-check-label" for="email_overdue_invoice">فواتير متأخرة</label>
                                    <small class="d-block text-muted">تنبيه بالفواتير المتأخرة السداد</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Notifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bell me-2"></i>إشعارات النظام</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notify_browser"
                                        name="notify_browser" value="1" checked>
                                    <label class="form-check-label" for="notify_browser">إشعارات المتصفح</label>
                                    <small class="d-block text-muted">عرض إشعارات فورية في المتصفح</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notify_sound" name="notify_sound"
                                        value="1">
                                    <label class="form-check-label" for="notify_sound">صوت الإشعارات</label>
                                    <small class="d-block text-muted">تشغيل صوت عند وصول إشعار</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>حفظ الإعدادات
                            </button>
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>العودة للإعدادات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection