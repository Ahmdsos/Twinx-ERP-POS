@extends('layouts.auth')

@section('content')

    <!-- ═══ Theme Toggle ═══ -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 100;">
        <button class="auth-theme-btn" onclick="toggleTheme()" aria-label="تبديل المظهر">
            <i class="bi bi-sun-fill auth-icon-light"></i>
            <i class="bi bi-moon-stars-fill auth-icon-dark"></i>
        </button>
    </div>

    <div class="min-vh-100 d-flex align-items-center py-3 py-lg-0">
        <div class="container-fluid px-3 px-lg-5">
            <div class="row g-0 align-items-stretch" style="min-height: 100vh;">

                <!-- ════════════════════════════════════════
                             LEFT SIDE — SHOWCASE PANEL
                             ════════════════════════════════════════ -->
                <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-center py-5">
                    <div class="showcase-panel pe-5">

                        <!-- Brand Header -->
                        <div class="showcase-header mb-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="brand-icon-box">
                                    <span>TX</span>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-800" style="letter-spacing: -0.5px;">
                                        <span class="auth-gradient-text">Twinx ERP</span>
                                    </h5>
                                    <span class="version-badge">V8.0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hero Text -->
                        <h1 class="showcase-title mb-3">
                            نظام إدارة المؤسسات<br>
                            <span class="auth-gradient-text">Twinx ERP</span>
                        </h1>

                        <p class="showcase-subtitle mb-5">
                            منظومة متكاملة تضم <strong>9 وحدات</strong> لإدارة المبيعات، المخازن، المحاسبة،
                            المشتريات، والموارد البشرية — من نقاط البيع حتى التقارير المالية
                            في واجهة واحدة سلسة.
                        </p>

                        <!-- ═══ Module Showcase Grid ═══ -->
                        <div class="modules-showcase mb-5">
                            <div class="row g-3">
                                <!-- Sales & POS -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #00d4ff;">
                                        <div class="pill-icon"><i class="bi bi-cart-check-fill"></i></div>
                                        <div class="pill-info">
                                            <h6>المبيعات و POS</h6>
                                            <p>نقاط بيع فائقة السرعة مع طباعة صامتة وفواتير احترافية</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inventory -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #8b5cf6;">
                                        <div class="pill-icon"><i class="bi bi-box-seam-fill"></i></div>
                                        <div class="pill-info">
                                            <h6>إدارة المخازن</h6>
                                            <p>تتبع لحظي للمخزون مع باركود ذكي وتنبيهات آلية</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accounting -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #10b981;">
                                        <div class="pill-icon"><i class="bi bi-bank2"></i></div>
                                        <div class="pill-info">
                                            <h6>المحاسبة المالية</h6>
                                            <p>قيود آلية ودليل حسابات متكامل وميزان مراجعة لحظي</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Purchasing -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #f59e0b;">
                                        <div class="pill-icon"><i class="bi bi-truck"></i></div>
                                        <div class="pill-info">
                                            <h6>المشتريات</h6>
                                            <p>أوامر شراء ذكية مع تتبع الموردين والمرتجعات</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- HR -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #ec4899;">
                                        <div class="pill-icon"><i class="bi bi-people-fill"></i></div>
                                        <div class="pill-info">
                                            <h6>شؤون الموظفين</h6>
                                            <p>رواتب وحضور وإجازات مع ربط محاسبي تلقائي</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Finance -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #3b82f6;">
                                        <div class="pill-icon"><i class="bi bi-wallet2"></i></div>
                                        <div class="pill-info">
                                            <h6>الخزينة والمالية</h6>
                                            <p>إدارة التدفقات النقدية والسندات والشيكات</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reporting -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #6366f1;">
                                        <div class="pill-icon"><i class="bi bi-graph-up-arrow"></i></div>
                                        <div class="pill-info">
                                            <h6>التقارير والتحليلات</h6>
                                            <p>أكثر من 50 تقرير جاهز مع رسوم بيانية تفاعلية</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Scanner -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #14b8a6;">
                                        <div class="pill-icon"><i class="bi bi-phone-fill"></i></div>
                                        <div class="pill-info">
                                            <h6>ماسح الباركود</h6>
                                            <p>مسح المنتجات بكاميرا الموبايل عبر WiFi مباشرة</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Settings Core -->
                                <div class="col-6 col-xl-4">
                                    <div class="module-pill" style="--pill-color: #a855f7;">
                                        <div class="pill-icon"><i class="bi bi-gear-wide-connected"></i></div>
                                        <div class="pill-info">
                                            <h6>الإعدادات والنظام</h6>
                                            <p>صلاحيات متقدمة ومستخدمين متعددين وأمان شامل</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Bar -->
                        <div class="stats-bar">
                            <div class="stat-item">
                                <div class="stat-num auth-gradient-text">9</div>
                                <div class="stat-txt">وحدة متكاملة</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-num auth-gradient-text">+50</div>
                                <div class="stat-txt">تقرير جاهز</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-num auth-gradient-text">∞</div>
                                <div class="stat-txt">مستخدمين</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-num auth-gradient-text">24/7</div>
                                <div class="stat-txt">دعم فني</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════
                             RIGHT SIDE — LOGIN FORM
                             ════════════════════════════════════════ -->
                <div class="col-lg-5 d-flex align-items-center justify-content-center py-4 py-lg-5">
                    <div class="login-panel w-100" style="max-width: 440px;">

                        <!-- Mobile-only Brand -->
                        <div class="d-lg-none text-center mb-4">
                            <div class="d-inline-flex align-items-center gap-2 mb-2">
                                <div class="brand-icon-box brand-icon-sm"><span>TX</span></div>
                                <h5 class="mb-0 fw-800 auth-gradient-text">Twinx ERP</h5>
                                <span class="version-badge">V8</span>
                            </div>
                        </div>

                        <!-- Login Card -->
                        <div class="login-card">
                            <!-- Glow Top Border -->
                            <div class="card-glow-top"></div>

                            <!-- Logo -->
                            <div class="text-center mb-4">
                                <div class="login-logo-ring">
                                    <img src="{{ asset('images/logo.png') }}" alt="Twinx" class="login-logo-img">
                                </div>
                            </div>

                            <!-- Welcome Text -->
                            <div class="text-center mb-4">
                                <h2 class="login-title">تسجيل الدخول</h2>
                                <p class="login-desc">أدخل بياناتك للوصول إلى لوحة التحكم</p>
                            </div>

                            <!-- Form -->
                            <form method="POST" action="{{ route('login.submit') }}" id="loginForm" novalidate>
                                @csrf

                                <div class="form-field mb-3">
                                    <label class="field-label">
                                        <i class="bi bi-envelope"></i>
                                        البريد الإلكتروني
                                    </label>
                                    <input type="email" name="email" class="field-input" placeholder="أدخل بريدك الإلكتروني"
                                        value="{{ old('email') }}" autocomplete="email" required>
                                </div>

                                <div class="form-field mb-4">
                                    <label class="field-label">
                                        <i class="bi bi-shield-lock"></i>
                                        كلمة المرور
                                    </label>
                                    <div class="field-input-wrap">
                                        <input type="password" name="password" id="pwdField" class="field-input"
                                            placeholder="أدخل كلمة المرور" autocomplete="current-password" required>
                                        <button type="button" class="pwd-eye" onclick="togglePwd()">
                                            <i class="bi bi-eye" id="pwdIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="submit-btn" id="submitBtn">
                                    <span class="btn-idle">
                                        تسجيل الدخول
                                        <i class="bi bi-arrow-left"></i>
                                    </span>
                                    <span class="btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        جاري التحقق...
                                    </span>
                                </button>

                                @if($errors->any())
                                    <div class="error-toast mt-3">
                                        <i class="bi bi-exclamation-octagon-fill"></i>
                                        <span>{{ $errors->first() }}</span>
                                    </div>
                                @endif
                            </form>

                            <!-- Card Footer -->
                            <div class="card-footer-section">
                                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                    <span class="footer-system">Twinx ERP v8.0</span>
                                    <span class="footer-dot">·</span>
                                    <span class="footer-agency">
                                        <strong class="auth-gradient-text">Twinx Agency</strong>
                                    </span>
                                </div>
                                <div class="footer-copy">
                                    &copy; {{ date('Y') }} Twinx Agency — جميع الحقوق محفوظة
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Features Quick View -->
                        <div class="d-lg-none mt-4">
                            <div class="mobile-features-scroll">
                                <div class="mobile-feature-chip" style="--chip-color: #00d4ff;">
                                    <i class="bi bi-cart-check-fill"></i> المبيعات
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #8b5cf6;">
                                    <i class="bi bi-box-seam-fill"></i> المخازن
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #10b981;">
                                    <i class="bi bi-bank2"></i> المحاسبة
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #f59e0b;">
                                    <i class="bi bi-truck"></i> المشتريات
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #ec4899;">
                                    <i class="bi bi-people-fill"></i> الموظفين
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #3b82f6;">
                                    <i class="bi bi-wallet2"></i> الخزينة
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #6366f1;">
                                    <i class="bi bi-graph-up-arrow"></i> التقارير
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #14b8a6;">
                                    <i class="bi bi-phone-fill"></i> الماسح
                                </div>
                                <div class="mobile-feature-chip" style="--chip-color: #a855f7;">
                                    <i class="bi bi-gear-wide-connected"></i> النظام
                                </div>
                            </div>
                            <p class="text-center mt-3" style="font-size: 0.88rem; color: var(--auth-text-3);">
                                9 وحدات متكاملة • +50 تقرير • دعم فني 24/7
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ══════════════════════════════════════════════
                   LOGIN PAGE — COMPONENT STYLES
                   ══════════════════════════════════════════════ */

        /* ── Brand Icon ── */
        .brand-icon-box {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--auth-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 14px;
            color: white;
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.2);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .brand-icon-box:hover {
            transform: rotate(8deg) scale(1.1);
        }

        .brand-icon-sm {
            width: 36px;
            height: 36px;
            font-size: 12px;
            border-radius: 10px;
        }

        .version-badge {
            font-size: 0.55rem;
            font-weight: 800;
            background: var(--auth-purple);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* ── Showcase Panel ── */
        .showcase-panel {
            padding-inline-start: 3rem;
        }

        .showcase-title {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 900;
            line-height: 1.35;
            letter-spacing: -0.5px;
        }

        .showcase-subtitle {
            font-size: 1.1rem;
            color: var(--auth-text-2);
            line-height: 1.9;
            max-width: 600px;
        }

        /* ── Module Pills ── */
        .module-pill {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border-radius: 16px;
            background: var(--auth-surface);
            border: 1px solid var(--auth-border);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            cursor: default;
        }

        .module-pill:hover {
            transform: translateY(-4px);
            border-color: color-mix(in srgb, var(--pill-color) 40%, transparent);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15),
                0 0 20px color-mix(in srgb, var(--pill-color) 10%, transparent);
            background: var(--auth-surface-hover);
        }

        .pill-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 12px;
            background: color-mix(in srgb, var(--pill-color) 12%, transparent);
            color: var(--pill-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .module-pill:hover .pill-icon {
            transform: scale(1.15) rotate(-5deg);
        }

        .pill-info h6 {
            font-size: 0.92rem;
            font-weight: 700;
            margin: 0 0 3px;
        }

        .pill-info p {
            font-size: 0.78rem;
            color: var(--auth-text-2);
            margin: 0;
            line-height: 1.6;
        }

        /* ── Stats Bar ── */
        .stats-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 16px 24px;
            border-radius: 16px;
            background: var(--auth-surface);
            border: 1px solid var(--auth-border);
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-num {
            font-size: 1.7rem;
            font-weight: 900;
            line-height: 1;
        }

        .stat-txt {
            font-size: 0.82rem;
            color: var(--auth-text-3);
            font-weight: 500;
            margin-top: 4px;
        }

        .stat-divider {
            width: 1px;
            height: 36px;
            background: var(--auth-border);
        }

        /* ══════ LOGIN CARD ══════ */
        .login-card {
            padding: 36px 32px;
            border-radius: 24px;
            background: var(--auth-surface);
            border: 1px solid var(--auth-border);
            backdrop-filter: blur(30px) saturate(180%);
            box-shadow: var(--auth-shadow);
            position: relative;
            overflow: hidden;
        }

        .card-glow-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--auth-gradient);
        }

        /* Logo Ring */
        .login-logo-ring {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 88px;
            height: 88px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.08), rgba(139, 92, 246, 0.08));
            border: 1px solid var(--auth-border);
            padding: 18px;
            position: relative;
            animation: logoBreath 4s ease-in-out infinite;
        }

        @keyframes logoBreath {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(139, 92, 246, 0);
            }

            50% {
                box-shadow: 0 0 30px 5px rgba(139, 92, 246, 0.12);
            }
        }

        .login-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 0 8px rgba(139, 92, 246, 0.2));
        }

        /* Titles */
        .login-title {
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .login-desc {
            font-size: 0.95rem;
            color: var(--auth-text-2);
        }

        /* ── Form Fields ── */
        .field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--auth-text-2);
            margin-bottom: 8px;
            padding-inline-start: 2px;
        }

        .field-label i {
            font-size: 0.85rem;
        }

        .field-input-wrap {
            position: relative;
        }

        .field-input {
            width: 100%;
            padding: 15px 16px;
            border-radius: 14px;
            border: 1.5px solid var(--auth-input-border);
            background: var(--auth-input-bg);
            color: var(--auth-text);
            font-size: 1rem;
            font-family: inherit;
            outline: none;
            transition: all 0.25s ease;
        }

        .field-input::placeholder {
            color: var(--auth-text-3);
        }

        .field-input:focus {
            border-color: var(--auth-purple);
            background: var(--auth-input-focus);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .pwd-eye {
            position: absolute;
            top: 50%;
            inset-inline-end: 14px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--auth-text-3);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 2px;
            transition: color 0.2s;
        }

        .pwd-eye:hover {
            color: var(--auth-purple);
        }

        .field-input-wrap .field-input {
            padding-inline-end: 44px;
        }

        /* ── Submit Button ── */
        .submit-btn {
            width: 100%;
            padding: 16px;
            border-radius: 14px;
            border: none;
            background: var(--auth-gradient);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.12), transparent);
            transition: left 0.6s ease;
        }

        .submit-btn:hover::after {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 40px rgba(139, 92, 246, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .btn-idle,
        .btn-loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Error Toast ── */
        .error-toast {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--auth-red);
            font-size: 0.85rem;
            font-weight: 500;
            animation: toastShake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97);
        }

        @keyframes toastShake {

            10%,
            90% {
                transform: translateX(-2px);
            }

            20%,
            80% {
                transform: translateX(4px);
            }

            30%,
            50%,
            70% {
                transform: translateX(-6px);
            }

            40%,
            60% {
                transform: translateX(6px);
            }
        }

        /* ── Card Footer ── */
        .card-footer-section {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--auth-border);
            text-align: center;
        }

        .footer-system {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--auth-text-2);
        }

        .footer-dot {
            color: var(--auth-text-3);
            font-size: 0.65rem;
        }

        .footer-agency {
            font-size: 0.8rem;
            color: var(--auth-text-2);
        }

        .footer-agency strong {
            font-weight: 800;
        }

        .footer-copy {
            font-size: 0.72rem;
            color: var(--auth-text-3);
            margin-top: 6px;
        }

        /* ── Mobile Feature Chips ── */
        .mobile-features-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .mobile-features-scroll::-webkit-scrollbar {
            display: none;
        }

        .mobile-feature-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 30px;
            background: var(--auth-surface);
            border: 1px solid var(--auth-border);
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
            color: var(--pill-color, var(--auth-text));
            transition: all 0.2s;
        }

        .mobile-feature-chip i {
            color: var(--chip-color);
        }

        /* ── Theme Toggle ── */
        .auth-theme-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid var(--auth-border);
            background: var(--auth-surface);
            backdrop-filter: blur(12px);
            color: var(--auth-text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .auth-theme-btn:hover {
            transform: scale(1.1) rotate(12deg);
            border-color: var(--auth-purple);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.2);
        }

        .auth-icon-light,
        .auth-icon-dark {
            display: none;
        }

        [data-theme="light"] .auth-icon-light {
            display: block;
        }

        [data-theme="dark"] .auth-icon-dark {
            display: block;
        }

        /* ══════ RESPONSIVE ══════ */
        @media (max-width: 991.98px) {
            .login-card {
                padding: 28px 22px;
                border-radius: 20px;
            }

            .login-logo-ring {
                width: 72px;
                height: 72px;
                border-radius: 20px;
                padding: 14px;
            }

            .login-title {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 575.98px) {
            .login-card {
                padding: 24px 18px;
                border-radius: 18px;
            }

            .login-logo-ring {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                padding: 12px;
            }

            .login-title {
                font-size: 1.25rem;
            }

            .login-desc {
                font-size: 0.82rem;
            }

            .submit-btn {
                padding: 14px;
                font-size: 0.95rem;
            }

            .field-input {
                padding: 12px 14px;
                font-size: 0.9rem;
            }

            .brand-icon-sm {
                width: 30px;
                height: 30px;
                font-size: 10px;
            }
        }

        /* ══════ ANIMATION ENTRY ══════ */
        .showcase-panel>* {
            animation: slideEntry 0.7s cubic-bezier(0.16, 1, 0.3, 1) backwards;
        }

        .showcase-panel>*:nth-child(1) {
            animation-delay: 0s;
        }

        .showcase-panel>*:nth-child(2) {
            animation-delay: 0.1s;
        }

        .showcase-panel>*:nth-child(3) {
            animation-delay: 0.15s;
        }

        .showcase-panel>*:nth-child(4) {
            animation-delay: 0.2s;
        }

        .showcase-panel>*:nth-child(5) {
            animation-delay: 0.3s;
        }

        .login-panel {
            animation: slideEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards 0.1s;
        }

        @keyframes slideEntry {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .module-pill {
            animation: pillEntry 0.5s cubic-bezier(0.16, 1, 0.3, 1) backwards;
        }

        .row .col-6:nth-child(1) .module-pill {
            animation-delay: 0.2s;
        }

        .row .col-6:nth-child(2) .module-pill {
            animation-delay: 0.25s;
        }

        .row .col-6:nth-child(3) .module-pill {
            animation-delay: 0.3s;
        }

        .row .col-6:nth-child(4) .module-pill {
            animation-delay: 0.35s;
        }

        .row .col-6:nth-child(5) .module-pill {
            animation-delay: 0.4s;
        }

        .row .col-6:nth-child(6) .module-pill {
            animation-delay: 0.45s;
        }

        .row .col-6:nth-child(7) .module-pill {
            animation-delay: 0.5s;
        }

        .row .col-6:nth-child(8) .module-pill {
            animation-delay: 0.55s;
        }

        .row .col-6:nth-child(9) .module-pill {
            animation-delay: 0.6s;
        }

        @keyframes pillEntry {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
        }

        function togglePwd() {
            const f = document.getElementById('pwdField');
            const i = document.getElementById('pwdIcon');
            if (f.type === 'password') {
                f.type = 'text';
                i.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                f.type = 'password';
                i.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        document.getElementById('loginForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.querySelector('.btn-idle').classList.add('d-none');
            btn.querySelector('.btn-loading').classList.remove('d-none');
            btn.disabled = true;
            btn.style.opacity = '0.8';
        });
    </script>
@endsection