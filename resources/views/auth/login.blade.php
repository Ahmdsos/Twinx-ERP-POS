@extends('layouts.auth')

@section('content')
    <!-- Theme Toggle (Floating) -->
    <div class="position-fixed top-0 end-0 p-4 z-3">
        <button class="btn btn-premium d-flex align-items-center justify-content-center gap-2 shadow-lg"
            onclick="toggleTheme()"
            style="backdrop-filter: blur(10px); background: var(--glass-bg); color: var(--text-primary); border: 1px solid var(--glass-border);">
            <i class="bi bi-sun-fill d-none d-light-inline"></i>
            <i class="bi bi-moon-stars-fill d-none d-dark-inline"></i>
            <span class="d-none d-md-inline fw-bold">{{ __('Theme') }}</span>
        </button>
    </div>

    <div class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <!-- Information Side -->
                <div class="col-lg-7">
                    <div class="pe-lg-5 text-center text-lg-start pt-lg-4">
                        <h1 class="display-3 fw-800 mb-4 lh-sm" style="color: var(--text-heading);">
                            {{ __('Manage your business') }} <span class="text-primary">{{ __('Intelligently') }}</span><br>
                            {{ __('And let your data drive your decisions') }}
                        </h1>

                        <p class="fs-5 mb-5 lh-base opacity-75" style="color: var(--text-secondary);">
                            {{ __('Twinx ERP long description') }}
                        </p>

                        <div class="row g-4 mb-5">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-box p-3 rounded-4 shadow-sm"
                                        style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                                        <i class="bi bi-cart-check fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1" style="color: var(--text-primary);">{{ __('Sales & POS') }}</h5>
                                        <p class="small text-secondary mb-0">
                                            {{ __('Fast POS and professional quotations.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-box p-3 rounded-4 glass-panel shadow-sm"
                                        style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                                        <i class="bi bi-box-seam fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1" style="color: var(--text-primary);">{{ __('Smart Inventory') }}
                                        </h5>
                                        <p class="small text-secondary mb-0">
                                            {{ __('Full control over multiple warehouses and item movements.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-box p-3 rounded-4 shadow-sm"
                                        style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                                        <i class="bi bi-bank fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1" style="color: var(--text-primary);">
                                            {{ __('Financial Accounting') }}</h5>
                                        <p class="small text-secondary mb-0">
                                            {{ __('Professional chart of accounts and real-time financial reports.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-box p-3 rounded-4 shadow-sm"
                                        style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                                        <i class="bi bi-people fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1" style="color: var(--text-primary);">{{ __('Personnel Affairs') }}
                                        </h5>
                                        <p class="small text-secondary mb-0">
                                            {{ __('Manage payroll, leaves, and employee records.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Login Side -->
                <div class="col-lg-5">
                    <div class="p-4 p-lg-5 rounded-5 shadow-2xl"
                        style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(20px);">
                        <div class="text-center mb-5">
                            <div class="d-inline-block p-3 rounded-circle border mb-4 shadow-lg"
                                style="background: var(--glass-bg); border-color: var(--glass-border) !important;">
                                <img src="{{ asset('images/logo.png') }}" alt="Twinx ERP" class="img-fluid"
                                    style="height: 60px; filter: drop-shadow(0 0 15px var(--focus-ring-color));">
                            </div>
                            <h2 class="fw-800 mb-2 tracking-tight" style="color: var(--text-heading);">{{ __('Login') }}
                            </h2>
                            <p class="text-secondary small opacity-75">
                                {{ __('Welcome to the next generation of management') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('login.submit') }}" class="needs-validation">
                            @csrf

                            <div class="mb-4">
                                <label
                                    class="form-label text-secondary small fw-bold text-uppercase tracking-wider mb-2 ms-1">{{ __('Username / Email') }}</label>
                                <div class="input-group input-group-lg overflow-hidden rounded-3 shadow-sm"
                                    style="background: var(--input-bg); border: 1px solid var(--input-border);">
                                    <span class="input-group-text bg-transparent border-0 text-gray-500 pe-3"><i
                                            class="bi bi-person"></i></span>
                                    <input type="email" name="email"
                                        class="form-control bg-transparent border-0 py-3 shadow-none fs-6"
                                        style="color: var(--text-primary);" required placeholder="admin@twinx.com"
                                        value="admin@twinx.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label
                                    class="form-label text-secondary small fw-bold text-uppercase tracking-wider mb-2 ms-1">{{ __('Password') }}</label>
                                <div class="input-group input-group-lg overflow-hidden rounded-3 shadow-sm"
                                    style="background: var(--input-bg); border: 1px solid var(--input-border);">
                                    <span class="input-group-text bg-transparent border-0 text-gray-500 pe-3"><i
                                            class="bi bi-shield-lock"></i></span>
                                    <input type="password" name="password"
                                        class="form-control bg-transparent border-0 py-3 shadow-none fs-6"
                                        style="color: var(--text-primary);" required placeholder="••••••••"
                                        value="password">
                                </div>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit"
                                    class="btn btn-premium py-3 fs-5 d-flex align-items-center justify-content-center gap-2">
                                    <span>{{ __('Login to System') }}</span>
                                    <i class="bi bi-box-arrow-in-left fs-4"></i>
                                </button>
                            </div>

                            @if($errors->any())
                                <div
                                    class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-20 text-danger text-center small animate__animated animate__shakeX">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                                </div>
                            @endif
                        </form>

                        <div class="mt-5 pt-4 border-top text-center small"
                            style="border-color: var(--glass-border) !important; color: var(--text-secondary);">
                            <span class="opacity-50">{{ __('Twinx ERP System V8') }}</span><br>
                            <span class="opacity-25">&copy; {{ date('Y') }} {{ __('All rights reserved') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-800 {
            font-weight: 800;
        }

        .text-gray-400 {
            color: var(--text-secondary);
        }

        .text-gray-500 {
            color: var(--text-secondary);
        }

        .tracking-tight {
            letter-spacing: -0.025em;
        }

        .tracking-wider {
            letter-spacing: 0.05em;
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        .form-control:focus {
            border-color: var(--focus-ring-color) !important;
            background-color: transparent !important;
            color: var(--text-primary) !important;
        }

        .input-group-text {
            border: 1px solid var(--glass-border);
        }

        .form-control {
            border: 1px solid var(--glass-border);
        }

        .icon-box {
            transition: all 0.3s ease;
        }

        .glass-card:hover .icon-box {
            background: var(--glass-border) !important;
            transform: scale(1.1);
        }

        /* Toggle Button Icons */
        [data-theme="dark"] .d-dark-inline {
            display: inline-block !important;
        }

        [data-theme="light"] .d-light-inline {
            display: inline-block !important;
        }
    </style>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const target = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
        }
    </script>
@endsection