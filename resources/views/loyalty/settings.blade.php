@extends('layouts.app')

@section('title', 'إعدادات برنامج الولاء')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">إعدادات برنامج الولاء</h1>
                <p class="text-muted mb-0">Loyalty Program Settings</p>
            </div>
            <a href="{{ route('loyalty.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>
                العودة
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('loyalty.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="bi bi-gift me-2"></i>إعدادات النقاط</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">عدد النقاط لكل معاملة</label>
                                <input type="number" name="points_per_amount" class="form-control" value="{{ $settings['points_per_amount'] }}" step="0.01" required>
                                <small class="text-muted">عدد النقاط التي يحصل عليها العميل</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">المبلغ المطلوب لكل نقطة (ج.م)</label>
                                <input type="number" name="amount_per_point" class="form-control" value="{{ $settings['amount_per_point'] }}" step="1" required>
                                <small class="text-muted">كل X جنيه = 1 نقطة</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">قيمة النقطة (ج.م)</label>
                                <input type="number" name="points_value" class="form-control" value="{{ $settings['points_value'] }}" step="0.01" required>
                                <small class="text-muted">قيمة كل نقطة عند الاستبدال</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="bi bi-arrow-repeat me-2"></i>إعدادات الاستبدال</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">الحد الأدنى للاستبدال (نقطة)</label>
                                <input type="number" name="min_redeem_points" class="form-control" value="{{ $settings['min_redeem_points'] }}" required>
                                <small class="text-muted">أقل عدد نقاط للاستبدال</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">مدة صلاحية النقاط (يوم)</label>
                                <input type="number" name="expiry_days" class="form-control" value="{{ $settings['expiry_days'] }}" required>
                                <small class="text-muted">بعد هذه المدة تنتهي النقاط غير المستخدمة</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">مثال:</h6>
                        <p class="mb-0">
                            بالإعدادات الحالية: عند شراء <strong>{{ $settings['amount_per_point'] * 10 }} ج.م</strong>
                            يحصل العميل على <strong>{{ $settings['points_per_amount'] * 10 }} نقطة</strong>
                            بقيمة <strong>{{ ($settings['points_per_amount'] * 10) * $settings['points_value'] }} ج.م</strong>
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        حفظ الإعدادات
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
