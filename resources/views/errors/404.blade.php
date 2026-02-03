<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة | Twinx ERP</title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .error-container {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            background: rgba(18, 25, 45, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .logo-box {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .logo-box img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #0d6efd 0%, #63b3ed 100%);
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
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn-home {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            padding: 0.8rem 2.5rem;
            border-radius: 50rem;
            color: white;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(13, 110, 253, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(13, 110, 253, 0.4);
            color: white;
        }

        /* Decorative background elements */
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 1;
            filter: blur(50px);
        }

        .glow-1 { top: -100px; right: -100px; }
        .glow-2 { bottom: -100px; left: -100px; }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="error-container">
        <div class="logo-box">
             <img src="{{ asset('images/logo.png') }}" alt="Twinx ERP">
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">عذراً، الصفحة غير موجودة</h1>
        <p class="error-message">
            يبدو أن الرابط الذي تحاول الوصول إليه غير موجود أو تم نقله. تأكد من صحة الرابط أو عد للرئيسية.
        </p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door-fill me-2"></i> العودة للرئيسية
        </a>
    </div>
</body>
</html>