<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Twinx ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
        }

        .btn-login {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-boxes"></i>
            <h2 class="mb-0">Twinx ERP</h2>
            <p class="mb-0 opacity-75">نظام إدارة الموارد المتكامل</p>
        </div>
        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                            placeholder="example@company.com" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">كلمة المرور</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-left me-2"></i>
                    تسجيل الدخول
                </button>
            </form>

            <div class="text-center mt-4 text-muted small">
                <p class="mb-0">نظام محمي &copy; {{ date('Y') }} Twinx ERP</p>
            </div>
        </div>
    </div>
</body>

</html>