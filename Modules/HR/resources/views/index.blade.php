@extends('layouts.app')

@section('title', 'لوحة تحكم الموارد البشرية')
@section('header', 'نظام إدارة الموارد البشرية')

@section('content')
    <div class="dashboard-wrapper">
        <!-- Visual Accent -->
        <div class="glow-accent top-0 end-0 opacity-25"></div>
        <div class="glow-accent bottom-0 start-0 opacity-10"
            style="width: 400px; height: 400px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%);">
        </div>

        <div class="row align-items-center mb-5 position-relative">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div
                        class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill x-small fw-bold border border-primary border-opacity-10">
                        <i class="bi bi-shield-check me-1"></i> بيئة آمنة ومتكاملة
                    </div>
                </div>
                <h2 class="fw-black text-white mb-1 display-6">لوحة التحكم <span class="text-primary">الذكية</span></h2>
                <p class="text-secondary mb-0 fs-5 opacity-75">إدارة القوى العاملة والعمليات الإدارية بأعلى معايير الدقة.
                </p>
            </div>

            <div class="col-md-6 text-md-end mt-4 mt-md-0">
                <div class="glass-card d-inline-block px-4 py-3 rounded-4 border border-white border-opacity-10 shadow-lg">
                    <div class="d-flex align-items-center gap-4">
                        <div class="text-end">
                            <div id="dashboard-clock" class="fw-black text-white fs-3 lh-1 tracking-tight">--:--:--</div>
                            <div class="text-primary small fw-bold mt-1">
                                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                            </div>
                        </div>
                        <div class="vr bg-white opacity-10" style="height: 40px;"></div>
                        <div class="pulse-icon bg-primary bg-opacity-30 text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 50px; height: 50px; border: 1px solid rgba(255,255,255,0.1);">
                            <i class="bi bi-clock-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Attendance Quick Widget -->
            <div class="col-lg-5">
                <div
                    class="glass-card card-hover-plus border-0 shadow-lg h-100 overflow-hidden rounded-4 position-relative">
                    <div class="card-body p-5 text-center position-relative">
                        <div class="mb-4">
                            <h5 class="text-white fw-bold mb-1">تسجيل الوقت</h5>
                            <span class="text-secondary small">إثبات الحضور الذكي</span>
                        </div>

                        @if($attendanceStatus == 'not_linked')
                            <div class="py-4">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning mx-auto mb-4"
                                    style="width: 80px; height: 80px;">
                                    <i class="bi bi-person-exclamation fs-1"></i>
                                </div>
                                <h6 class="text-white fw-bold">حساب غير مرتبط</h6>
                                <p class="text-secondary small px-4">يرجى التواصل مع مدير النظام لربط حسابك بملف موظف لتفعيل
                                    البصمة الذكية.</p>
                            </div>
                        @elseif($attendanceStatus == 'not_checked_in')
                            <div class="py-4">
                                <form action="{{ route('hr.attendance.check-in') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="attendance-btn btn-check-in">
                                        <div class="btn-content">
                                            <i class="bi bi-fingerprint fs-1 d-block mb-2"></i>
                                            <span class="fw-black fs-4">تسجيل حضور</span>
                                        </div>
                                        <div class="btn-ripple"></div>
                                    </button>
                                </form>
                                <p class="text-secondary mt-4 small mb-0"><i class="bi bi-info-circle me-1"></i> اضغط لتسجيل
                                    حضورك الفعلي الآن</p>
                            </div>
                        @elseif($attendanceStatus == 'checked_in')
                            <div class="py-4">
                                <div class="status-indicator active mb-4">
                                    <span class="pulse"></span>
                                    <span class="text fw-bold">أنت متصل الآن (حاضر)</span>
                                </div>
                                <form action="{{ route('hr.attendance.check-out') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="attendance-btn btn-check-out">
                                        <div class="btn-content">
                                            <i class="bi bi-box-arrow-left fs-1 d-block mb-2"></i>
                                            <span class="fw-black fs-4">تسجيل انصراف</span>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        @elseif($attendanceStatus == 'checked_out')
                            <div class="py-5">
                                <div class="icon-box bg-success bg-opacity-10 text-success mx-auto mb-4"
                                    style="width: 80px; height: 80px;">
                                    <i class="bi bi-check2-circle fs-1"></i>
                                </div>
                                <h5 class="text-white fw-bold">تم إنهاء العمل بنجاح</h5>
                                <p class="text-secondary mb-4">شكراً لمجهوداتك، نراك غداً بصحة جيدة.</p>
                                <a href="{{ route('hr.attendance.index') }}"
                                    class="btn btn-outline-primary rounded-pill px-4">عرض سجل اليوم</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="col-lg-7">
                <div class="row g-4 h-100">
                    <div class="col-md-6">
                        <div
                            class="stat-card glass-card h-100 p-4 border-start border-primary border-4 rounded-4 shadow-lg overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                                <i class="bi bi-people-fill fs-1"></i>
                            </div>
                            <div class="badge bg-primary bg-opacity-10 text-primary mb-3">القوى العاملة</div>
                            <h6 class="text-secondary fw-bold mb-1">إجمالي الموظفين</h6>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="text-white fw-black mb-0 display-5">
                                    {{ number_format($stats['total_employees']) }}
                                </h2>
                                <span class="text-success small fw-bold"><i class="bi bi-arrow-up-short"></i> نمو</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div
                            class="stat-card glass-card h-100 p-4 border-start border-success border-4 rounded-4 shadow-lg overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                                <i class="bi bi-person-check-fill fs-1"></i>
                            </div>
                            <div class="badge bg-success bg-opacity-10 text-success mb-3">النشاط الحالي</div>
                            <h6 class="text-secondary fw-bold mb-1">الموظفين النشطين</h6>
                            <h2 class="text-white fw-black mb-0 display-5">{{ number_format($stats['active_employees']) }}
                            </h2>
                        </div>
                    </div>
                    <div class="col-12">
                        <div
                            class="stat-card glass-card p-4 border-start border-info border-4 rounded-4 shadow-lg overflow-hidden position-relative bg-gradient-info">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="badge bg-info bg-opacity-10 text-info mb-3 text-white">السيولة والمرتبات
                                    </div>
                                    <h6 class="text-secondary fw-bold mb-1 text-white opacity-75">إجمالي ميزانية الرواتب
                                        الشهرية</h6>
                                    <h2 class="text-white fw-black mb-0 display-4">
                                        {{ number_format($stats['total_salary'], 2) }} <small
                                            class="fs-6 fw-normal opacity-50">ج.م</small>
                                    </h2>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div
                                        class="icon-circle bg-white bg-opacity-10 text-white p-4 d-inline-block rounded-circle">
                                        <i class="bi bi-cash-stack fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Alerts & Quick Access -->
        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                @if($expiringDocuments->isNotEmpty() || $pendingLeaves->isNotEmpty())
                    <div class="glass-card rounded-4 p-4 shadow-lg h-100">
                        <h5 class="text-white fw-black mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-lightning-charge-fill text-warning"></i> تنبيهات تتطلب إجراء
                        </h5>
                        <div class="row g-4">
                            @if($expiringDocuments->isNotEmpty())
                                <div class="col-md-6">
                                    <div class="alert-box border border-danger border-opacity-25 rounded-3 p-3 h-100">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-danger fw-bold small"><i class="bi bi-file-earmark-person"></i> انتهاء
                                                وثائق</span>
                                            <span class="badge bg-danger rounded-pill">{{ $expiringDocuments->count() }}</span>
                                        </div>
                                        @foreach($expiringDocuments->take(3) as $doc)
                                            <div
                                                class="d-flex justify-content-between align-items-center py-2 border-bottom border-white border-opacity-10 last-border-0">
                                                <span class="text-white small fw-bold">{{ $doc->employee->full_name }}</span>
                                                <span class="text-danger x-small">{{ $doc->expiry_date->format('Y-m-d') }}</span>
                                            </div>
                                        @endforeach
                                        <div class="mt-3 text-center">
                                            <a href="{{ route('hr.employees.index') }}"
                                                class="text-danger x-small text-decoration-none fw-bold">عرض الكل <i
                                                    class="bi bi-chevron-left x-small"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($pendingLeaves->isNotEmpty())
                                <div class="col-md-6">
                                    <div class="alert-box border border-warning border-opacity-25 rounded-3 p-3 h-100">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-warning fw-bold small"><i class="bi bi-calendar2-week"></i> طلبات
                                                إجازة</span>
                                            <span
                                                class="badge bg-warning text-dark rounded-pill">{{ $pendingLeaves->count() }}</span>
                                        </div>
                                        @foreach($pendingLeaves->take(3) as $leave)
                                            <div
                                                class="d-flex justify-content-between align-items-center py-2 border-bottom border-white border-opacity-10 last-border-0">
                                                <span class="text-white small fw-bold">{{ $leave->employee->full_name }}</span>
                                                <a href="{{ route('hr.employees.show', $leave->employee_id) }}#leaves"
                                                    class="btn btn-primary btn-xs py-0 px-2 rounded-pill x-small">مراجعة</a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div
                        class="glass-card rounded-4 p-5 shadow-lg h-100 d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="icon-circle bg-success bg-opacity-10 text-success p-4 mb-4 rounded-circle">
                            <i class="bi bi-check-all display-4"></i>
                        </div>
                        <h4 class="text-white fw-bold">كل شيء على ما يرام</h4>
                        <p class="text-secondary opacity-75">لا توجد تنبيهات عاجلة أو طلبات معلقة حالياً.</p>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="glass-card rounded-4 p-4 shadow-lg h-100 bg-gradient-reports overflow-hidden position-relative">
                    <div class="position-relative z-index-2">
                        <h5 class="text-white fw-black mb-3">مركز التقارير <span class="badge bg-danger ms-1">جديد</span>
                        </h5>
                        <p class="text-white text-opacity-75 small mb-4">استخرج تقارير شاملة متكاملة قابلة للطباعة لجميع
                            الموظفين بضغطة واحدة.</p>
                        <a href="{{ route('hr.reports.index') }}"
                            class="btn btn-light w-100 rounded-pill fw-bold py-2 shadow-sm">
                            <i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i> دخول مركز التقارير
                        </a>
                    </div>
                    <i class="bi bi-file-bar-graph position-absolute bottom-0 end-0 fs-1 opacity-10 mb-n3 me-n3"
                        style="transform: rotate(-15deg); font-size: 8rem !important;"></i>
                </div>
            </div>
        </div>

        <!-- System Hub -->
        <h5 class="text-white fw-black mb-4 px-2">مركز إدارة العمليات System Hub</h5>
        <div class="row g-4">
            @php
                $modules = [
                    [
                        'name' => 'إدارة الموظفين',
                        'desc' => 'الملفات المركزية والبيانات',
                        'route' => 'hr.employees.index',
                        'icon' => 'bi-person-badge',
                        'color' => 'primary'
                    ],
                    [
                        'name' => 'سجل الحضور',
                        'desc' => 'متابعة الدوام والانضباط',
                        'route' => 'hr.attendance.index',
                        'icon' => 'bi-qr-code-scan',
                        'color' => 'warning'
                    ],
                    [
                        'name' => 'مسيرات الرواتب',
                        'desc' => 'الاحتساب والترحيل المالي',
                        'route' => 'hr.payroll.index',
                        'icon' => 'bi-wallet2',
                        'color' => 'success'
                    ],
                    [
                        'name' => 'إدارة التوصيل',
                        'desc' => 'السائقين والعمليات الميدانية',
                        'route' => 'hr.delivery.index',
                        'icon' => 'bi-lightning-fill',
                        'color' => 'info'
                    ],
                ];
            @endphp

            @foreach($modules as $mod)
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route($mod['route']) }}"
                        class="card-hub glass-card h-100 p-4 shadow-lg rounded-4 text-decoration-none border border-white border-opacity-5 d-block position-relative overflow-hidden">
                        <div class="icon-box-hub bg-{{ $mod['color'] }} bg-opacity-10 text-{{ $mod['color'] }} mb-4">
                            <i class="bi {{ $mod['icon'] }} fs-2"></i>
                        </div>
                        <h6 class="text-white fw-black mb-2">{{ $mod['name'] }}</h6>
                        <p class="text-secondary x-small mb-0 opacity-75">{{ $mod['desc'] }}</p>

                        <div class="arrow-hub position-absolute bottom-0 end-0 p-3 opacity-0">
                            <i class="bi bi-arrow-left-circle-fill text-{{ $mod['color'] }} fs-4"></i>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        :root {
            --glass-bg: rgba(10, 20, 40, 0.85);
            /* Much deeper dark background */
            --glass-border: rgba(255, 255, 255, 0.1);
            --bs-primary: #0d6efd;
            --bs-success: #198754;
            --bs-warning: #ffc107;
            --bs-info: #0dcaf0;
            --bs-danger: #dc3545;
        }

        .dashboard-wrapper {
            position: relative;
            padding: 20px 0;
        }

        .glow-accent {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%);
            z-index: 0;
            pointer-events: none;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            /* Stronger shadow for depth */
        }

        .card-hover-plus:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .fw-black {
            font-weight: 900 !important;
        }

        .x-small {
            font-size: 0.75rem !important;
        }

        .btn-xs {
            padding: 2px 8px;
            font-size: 0.7rem;
        }

        .last-border-0:last-child {
            border-bottom: 0 !important;
        }

        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
        }

        /* Attendance Button Premium Styles */
        .attendance-btn {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: none;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
            color: white;
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
        }

        .btn-check-in {
            background: linear-gradient(135deg, #198754 0%, #10663d 100%);
            box-shadow: 0 15px 35px rgba(25, 135, 84, 0.3);
        }

        .btn-check-out {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            box-shadow: 0 15px 35px rgba(220, 53, 69, 0.3);
        }

        .attendance-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
        }

        .attendance-btn:active {
            transform: scale(0.95);
        }

        .btn-ripple {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            animation: ripple 2s infinite;
        }

        @keyframes ripple {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        .status-indicator.active {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(25, 135, 84, 0.1);
            padding: 8px 20px;
            border-radius: 100px;
            color: #198754;
        }

        .status-indicator.active .pulse {
            width: 8px;
            height: 8px;
            background: #198754;
            border-radius: 50%;
            box-shadow: 0 0 10px #198754;
            animation: neon-pulse 1.5s infinite;
        }

        @keyframes neon-pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.5);
                opacity: 0.5;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Hub Cards */
        .card-hub:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--bs-primary);
        }

        .icon-box-hub {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-hub:hover .icon-box-hub {
            transform: rotate(-10deg) scale(1.1);
        }

        .card-hub:hover .arrow-hub {
            opacity: 1;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }

        /* Gradients */
        .bg-gradient-info {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(13, 110, 253, 0.2) 100%) !important;
        }

        .bg-gradient-reports {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(13, 110, 253, 0.15) 100%);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .display-6 {
                font-size: 2rem;
            }

            .display-4 {
                font-size: 2.5rem;
            }

            .attendance-btn {
                width: 140px;
                height: 140px;
            }

            .attendance-btn span {
                font-size: 1.2rem !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateClock() {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                document.getElementById('dashboard-clock').textContent = `${h}:${m}:${s}`;
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
@endsection