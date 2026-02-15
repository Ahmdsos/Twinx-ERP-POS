<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - خطأ في النظام | Twinx ERP</title>
    <!-- Bootstrap 5.3 RTL -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <!-- Google Fonts (Cairo) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/cairo/cairo.css') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #0a0f1e;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem 0;
        }

        .error-container {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 3rem;
            max-width: 800px;
            width: 90%;
            background: rgba(18, 25, 45, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid var(--btn-glass-border);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .logo-box {
            width: 80px;
            height: 80px;
            background: var(--btn-glass-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 1px solid var(--btn-glass-border);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .logo-box img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .error-message {
            color: #cbd5e0;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .debug-info {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            text-align: left;
            direction: ltr;
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-family: monospace;
            overflow-x: auto;
        }

        .debug-title {
            color: #ef4444;
            font-weight: bold;
            margin-bottom: 0.5rem;
            font-family: 'Cairo', sans-serif;
            text-align: right;
        }

        .btn-home {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            padding: 0.8rem 2.5rem;
            border-radius: 50rem;
            color: var(--text-primary);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(13, 110, 253, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(13, 110, 253, 0.4);
            color: var(--text-primary);
        }

        /* Decorative background elements */
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 1;
            filter: blur(50px);
        }

        .glow-1 {
            top: -100px;
            right: -100px;
        }

        .glow-2 {
            bottom: -100px;
            left: -100px;
        }
    </style>
</head>

<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="error-container">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="Twinx ERP">
        </div>
        <div class="error-code">500</div>
        <h1 class="error-title">حدث خطأ داخلي في النظام</h1>
        <p class="error-message">
            نعتذر، واجه النظام مشكلة غير متوقعة أثناء معالجة طلبك. تم تسجيل الخطأ والمطورون يعملون على حله.
        </p>

        @if(isset($exception))
            <div class="debug-info">
                <div class="debug-title">تفاصيل الخطأ (للمطورين فقط):</div>
                <div class="text-danger mb-2">{{ $exception->getMessage() }}</div>
                <div class="text-secondary small">
                    File: {{ $exception->getFile() }}<br>
                    Line: {{ $exception->getLine() }}
                </div>
            </div>
        @endif

        <div class="d-flex gap-3 justify-content-center">
            <a href="{{ url()->previous() }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-arrow-right me-2"></i> العودة للخلف
            </a>
            <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-house-door-fill me-2"></i> الرئيسية
            </a>
        </div>
    </div>
</body>

</html>