<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
    data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'Twinx ERP') }} — نظام إدارة المؤسسات المتكامل</title>
    <meta name="description"
        content="Twinx ERP — نظام إدارة المؤسسات الأكثر تطوراً. المبيعات، المخازن، الحسابات، الموارد البشرية، والمشتريات في منظومة واحدة متكاملة.">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Bootstrap -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
        const storedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>

    <style>
        /* ═══════════════════════════════════════════════════
           TWINX AUTH — ULTRA PREMIUM DESIGN SYSTEM
           ═══════════════════════════════════════════════════ */

        :root {
            /* ── Dark Mode (Default) ── */
            --auth-bg: #06060e;
            --auth-bg-2: #0c0c1d;
            --auth-surface: rgba(15, 15, 35, 0.65);
            --auth-surface-hover: rgba(25, 25, 55, 0.8);
            --auth-border: rgba(255, 255, 255, 0.06);
            --auth-border-hover: rgba(255, 255, 255, 0.12);
            --auth-text: #f0f0f8;
            --auth-text-2: #8888aa;
            --auth-text-3: #555570;
            --auth-input-bg: rgba(255, 255, 255, 0.04);
            --auth-input-border: rgba(255, 255, 255, 0.08);
            --auth-input-focus: rgba(139, 92, 246, 0.15);
            --auth-cyan: #00d4ff;
            --auth-purple: #8b5cf6;
            --auth-pink: #ec4899;
            --auth-green: #10b981;
            --auth-orange: #f59e0b;
            --auth-blue: #3b82f6;
            --auth-red: #ef4444;
            --auth-gradient: linear-gradient(135deg, #00d4ff 0%, #8b5cf6 50%, #ec4899 100%);
            --auth-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            --auth-glow-1: rgba(0, 212, 255, 0.06);
            --auth-glow-2: rgba(139, 92, 246, 0.06);
            --auth-glow-3: rgba(236, 72, 153, 0.04);
        }

        [data-theme="light"] {
            --auth-bg: #f8fafc;
            --auth-bg-2: #f1f5f9;
            --auth-surface: rgba(255, 255, 255, 0.85);
            --auth-surface-hover: rgba(255, 255, 255, 0.95);
            --auth-border: rgba(0, 0, 0, 0.08);
            --auth-border-hover: rgba(0, 0, 0, 0.15);
            --auth-text: #0f172a;
            --auth-text-2: #64748b;
            --auth-text-3: #94a3b8;
            --auth-input-bg: rgba(0, 0, 0, 0.03);
            --auth-input-border: rgba(0, 0, 0, 0.1);
            --auth-input-focus: rgba(139, 92, 246, 0.1);
            --auth-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            --auth-glow-1: rgba(59, 130, 246, 0.08);
            --auth-glow-2: rgba(139, 92, 246, 0.08);
            --auth-glow-3: rgba(236, 72, 153, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: rgba(139, 92, 246, 0.3) transparent;
        }

        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: var(--auth-bg);
            color: var(--auth-text);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background 0.4s ease, color 0.4s ease;
        }

        /* ── Animated Mesh Background ── */
        .auth-bg-mesh {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 80% 50% at 20% 20%, var(--auth-glow-1), transparent),
                radial-gradient(ellipse 60% 40% at 80% 80%, var(--auth-glow-2), transparent),
                radial-gradient(ellipse 50% 60% at 50% 50%, var(--auth-glow-3), transparent);
        }

        /* ── Floating Orbs ── */
        .auth-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .auth-orb-1 {
            width: 600px;
            height: 600px;
            top: -200px;
            left: -200px;
            background: rgba(0, 212, 255, 0.07);
            animation: orbDrift 25s ease-in-out infinite;
        }

        .auth-orb-2 {
            width: 500px;
            height: 500px;
            bottom: -150px;
            right: -150px;
            background: rgba(139, 92, 246, 0.07);
            animation: orbDrift 25s ease-in-out infinite -8s;
        }

        .auth-orb-3 {
            width: 400px;
            height: 400px;
            top: 40%;
            left: 60%;
            background: rgba(236, 72, 153, 0.04);
            animation: orbDrift 25s ease-in-out infinite -16s;
        }

        [data-theme="light"] .auth-orb-1 {
            background: rgba(59, 130, 246, 0.08);
        }

        [data-theme="light"] .auth-orb-2 {
            background: rgba(139, 92, 246, 0.08);
        }

        [data-theme="light"] .auth-orb-3 {
            background: rgba(236, 72, 153, 0.06);
        }

        @keyframes orbDrift {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(40px, -50px) scale(1.06);
            }

            50% {
                transform: translate(-30px, 30px) scale(0.94);
            }

            75% {
                transform: translate(50px, 40px) scale(1.03);
            }
        }

        /* ── Particles ── */
        .auth-particle {
            position: fixed;
            width: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: particleFloat linear infinite;
        }

        [data-theme="light"] .auth-particle {
            background: rgba(139, 92, 246, 0.15);
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-10vh) scale(1);
                opacity: 0;
            }
        }

        /* ── Content Layer ── */
        .auth-content {
            position: relative;
            z-index: 1;
        }

        /* ── Gradient Text Utility ── */
        .auth-gradient-text {
            background: var(--auth-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body>
    <div class="auth-bg-mesh"></div>
    <div class="auth-orb auth-orb-1"></div>
    <div class="auth-orb auth-orb-2"></div>
    <div class="auth-orb auth-orb-3"></div>
    <div id="auth-particles"></div>

    <div class="auth-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Particle Generator -->
    <script>
        (function () {
            const container = document.getElementById('auth-particles');
            if (!container) return;
            for (let i = 0; i < 25; i++) {
                const p = document.createElement('div');
                p.className = 'auth-particle';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (10 + Math.random() * 15) + 's';
                p.style.animationDelay = Math.random() * 12 + 's';
                p.style.width = (1 + Math.random() * 2) + 'px';
                p.style.height = p.style.width;
                container.appendChild(p);
            }
        })();
    </script>
</body>

</html>