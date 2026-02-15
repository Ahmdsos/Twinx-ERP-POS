<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
    data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Twinx ERP') }} - {{ __('Smart Integrated Management System') }}</title>
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        // Check local storage for theme
        const storedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>
    <style>
        :root {
            /* Dark Mode Default */
            --glass-bg: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary-glow: rgba(59, 130, 246, 0.4);
            --secondary-glow: rgba(139, 92, 246, 0.4);
            --mesh-bg-1: rgba(15, 23, 42, 1);
            --mesh-bg-2: #020617;
        }

        [data-theme="light"] {
            /* Light Mode Overrides */
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.1);
            --primary-glow: rgba(59, 130, 246, 0.2);
            --secondary-glow: rgba(139, 92, 246, 0.2);
            --mesh-bg-1: #f8fafc;
            --mesh-bg-2: #f1f5f9;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Animated Mesh Gradient Background */
        .mesh-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background:
                radial-gradient(circle at 0% 0%, var(--primary-glow) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, var(--secondary-glow) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, var(--mesh-bg-1) 0%, var(--mesh-bg-2) 100%);
            animation: pulse 15s ease-infinite;
            transition: background 0.5s ease;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }



        .text-gradient {
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-premium {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: var(--text-primary);
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.4);
            color: var(--text-primary);
        }
    </style>
</head>

<body>
    <div class="mesh-gradient"></div>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>