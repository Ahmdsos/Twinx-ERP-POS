@extends('layouts.app')

@section('content')
    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h4 class="mb-1 fw-bold text-white"><i class="bi bi-scale me-2 text-info"></i> تحليل النسخة الاحتياطية</h4>
                <p class="text-white-50 mb-0">مقارنة تفصيلية قبل اتخاذ قرار الاستعادة</p>
            </div>
            <div>
                <a href="{{ route('settings.backup.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-right me-2"></i> عودة للقائمة
                </a>
            </div>
        </div>

        <!-- Comparison Grid -->
    <div class="row g-4 mb-5">
        <!-- Live Side -->
        <div class="col-md-6">
            <div class="glass-card h-100 p-0 overflow-hidden border border-success border-opacity-50 shadow-lg">
                <div class="bg-success bg-opacity-25 p-4 border-bottom border-success border-opacity-25 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-hdd-rack-fill fs-3 text-success me-3"></i>
                        <div>
                            <h5 class="fw-bold text-white mb-0">النظام الحالي (Live)</h5>
                            <small class="text-success-emphasis fw-bold">Active Database</small>
                        </div>
                    </div>
                    <span class="badge bg-success text-white px-3 py-2">System</span>
                </div>
                
                <div class="p-5">
                    <div class="row g-5">
                        <div class="col-6">
                            <label class="text-secondary text-uppercase small fw-bold mb-2">الحجم الكلي</label>
                            <div class="fs-2 fw-bold text-white font-monospace">
                                {{ \App\Http\Controllers\BackupController::formatBytes($liveStats['size']) }}
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="text-secondary text-uppercase small fw-bold mb-2">عدد الجداول</label>
                            <div class="fs-2 fw-bold text-white font-monospace">
                                {{ $liveStats['tables_count'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Side -->
        <div class="col-md-6">
            <div class="glass-card h-100 p-0 overflow-hidden border border-warning border-opacity-50 shadow-lg" style="background: rgba(30, 30, 30, 0.95);">
                <div class="bg-warning bg-opacity-10 p-4 border-bottom border-warning border-opacity-10 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-filetype-sql fs-3 text-warning me-3"></i>
                        <div>
                            <h5 class="fw-bold text-white mb-0">نسخة الاستعادة</h5>
                            <small class="text-warning font-monospace dir-ltr">{{ $backupStats['name'] }}</small>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2">Backup</span>
                </div>

                <div class="p-5">
                     <div class="row g-5">
                        <div class="col-6">
                            <label class="text-secondary text-uppercase small fw-bold mb-2">الحجم</label>
                            <div class="fs-2 fw-bold text-white font-monospace">
                                {{ \App\Http\Controllers\BackupController::formatBytes($backupStats['size']) }}
                            </div>
                            
                            <!-- Size Comparison Indicator -->
                            @if($backupStats['size'] > $liveStats['size'])
                                <div class="badge bg-success bg-opacity-25 text-success mt-2">
                                    <i class="bi bi-arrow-up"></i> أكبر بـ {{ \App\Http\Controllers\BackupController::formatBytes($backupStats['size'] - $liveStats['size']) }}
                                </div>
                            @elseif($backupStats['size'] < $liveStats['size'])
                                <div class="badge bg-danger bg-opacity-25 text-danger mt-2">
                                    <i class="bi bi-arrow-down"></i> أصغر بـ {{ \App\Http\Controllers\BackupController::formatBytes($liveStats['size'] - $backupStats['size']) }}
                                </div>
                            @else
                                <div class="badge bg-secondary text-white mt-2">مطابق للحجم</div>
                            @endif
                        </div>
                        
                        <div class="col-6">
                            <label class="text-secondary text-uppercase small fw-bold mb-2">عدد الجداول</label>
                            <div class="fs-2 fw-bold text-white font-monospace">
                                {{ $backupStats['tables_count'] }}
                            </div>
                            <!-- Tables Comparison Indicator -->
                             @if($backupStats['tables_count'] == $liveStats['tables_count'])
                                <div class="text-success small fw-bold mt-2"><i class="bi bi-check-circle-fill"></i> هيكل مطابق</div>
                            @else
                                <div class="text-warning small fw-bold mt-2"><i class="bi bi-exclamation-triangle-fill"></i> اختلاف هيكلي</div>
                            @endif
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3 text-secondary"></i>
                            <span class="text-white-50">تاريخ النسخة:</span>
                        </div>
                        <div class="text-end">
                            <div class="text-white fw-bold font-monospace mb-0">{{ date('Y-m-d H:i', $backupStats['date']) }}</div>
                            <small class="text-secondary">{{ \Carbon\Carbon::createFromTimestamp($backupStats['date'])->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Final Action -->
        <div class="text-center">
            <form action="{{ route('settings.backup.restore', $backupStats['name']) }}" method="POST">
                @csrf
                <div class="card bg-danger bg-opacity-10 border-danger border-opacity-25 d-inline-block p-4 rounded-4">
                    <h5 class="text-danger fw-bold mb-3">⚠️ منطقة الخطر</h5>
                    <p class="text-white-50 mb-4" style="max-width: 400px; margin: 0 auto;">
                        استعادة هذه النسخة سيؤدي إلى مسح كافة البيانات الحالية. تأكد من أنك قمت بعمل نسخة احتياطية للحالة
                        الحالية إذا كنت متردداً.
                    </p>
                    <button type="submit" class="btn btn-danger px-5 py-3 rounded-pill fw-bold hover-scale shadow-lg"
                        onclick="return confirm('تأكيد نهائي: هل أنت متأكد من استبدال قاعدة البيانات؟');">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> تأكيد واستعادة النسخة
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection