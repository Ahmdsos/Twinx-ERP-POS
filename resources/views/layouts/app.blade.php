<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Twinx ERP') }} - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 RTL -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <!-- Google Fonts (Cairo) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/cairo/cairo.css') }}">
    <!-- Alpine.js -->
    <script defer src="{{ asset('assets/vendor/alpinejs/alpine.min.js') }}"></script>

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #111827;
            /* Gray 900 */
            --header-height: 70px;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f3f4f6;
            /* Light gray background for contrast */
            color: #1f2937;
            min-height: 100vh;
        }

        /* Dark Mode overrides if needed */
        [data-bs-theme="dark"] body {
            background-color: #0f172a;
            color: #e2e8f0;
        }

        /* Professional Fixed Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            /* RTL */
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease-in-out;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.2);
        }

        /* Main Content Offset */
        .main-wrapper {
            margin-right: var(--sidebar-width);
            /* Push content left in RTL */
            transition: margin-right 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(100%);
                /* Hide visually */
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-right: 0;
            }
        }

        /* Elegant Scrollbar */
        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Nav Item Styling */
        .nav-link {
            color: #9ca3af;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.2s ease;
            border-right: 3px solid transparent;
        }

        .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.03);
        }

        .nav-link.active-gradient {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, transparent 100%);
            border-right-color: #3b82f6;
        }

        .nav-group-label {
            padding: 24px 24px 8px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            font-weight: 700;
        }

        /* Card & Table Tweaks */
        .card {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .table {
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
    </style>
    @stack('styles')
</head>

<body>

    <!-- Sidebar Container (Fixed) -->
    <nav class="sidebar collapse d-lg-flex" id="sidebarMenu">
        <!-- Logo -->
        <div class="d-flex align-items-center flex-shrink-0 px-4"
            style="height: var(--header-height); border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div class="me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <img src="{{ asset('images/logo.png') }}" alt="Twinx ERP" class="img-fluid rounded-circle shadow-sm">
            </div>
            <h5 class="mb-0 fw-bold text-white tracking-wide">Twinx <span class="text-primary">ERP</span></h5>
        </div>

        <!-- Scrollable Menu -->
        <div class="sidebar-scroll py-3">
            @include('partials.sidebar')
        </div>

        <!-- User Profile (Fixed Bottom) -->
        <div class="mt-auto border-top border-secondary border-opacity-10 p-3 bg-opacity-10 bg-black">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset('images/default_avatar.png') }}" alt="" width="36" height="36"
                        class="rounded-circle me-2">
                    <div class="overflow-hidden">
                        <div class="fw-medium small text-truncate">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-secondary x-small text-truncate" style="font-size: 11px;">متصل الان</div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0">
                    <li><a class="dropdown-item small" href="{{ route('settings.index') }}"><i
                                class="bi bi-gear me-2"></i> الإعدادات</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item small text-danger"><i
                                    class="bi bi-box-arrow-right me-2"></i> تسجيل خروج</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3" style="height: var(--header-height);">
            <button class="navbar-toggler me-2 border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <h5 class="mb-0 fw-bold text-white">@yield('header')</h5>

            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button
                        class="btn btn-icon btn-outline-secondary border-0 rounded-circle text-white position-relative"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell{{ $dashboardNotifications->count() > 0 ? '-fill' : '' }}"></i>
                        @if($dashboardNotifications->count() > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-0 p-0"
                        style="width: 320px; max-height: 480px; overflow-y: auto;">
                        <li class="p-3 border-bottom border-secondary border-opacity-25 bg-secondary bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-white">الإشعارات</h6>
                                <span
                                    class="badge bg-primary rounded-pill">{{ $dashboardNotifications->count() }}</span>
                            </div>
                        </li>

                        @forelse($dashboardNotifications as $notification)
                            <li>
                                <a class="dropdown-item p-3 border-bottom border-secondary border-opacity-10 d-flex gap-3"
                                    href="{{ $notification['url'] }}">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 36px; height: 36px; background-color: rgba(var(--bs-{{ $notification['type'] }}-rgb), 0.15);">
                                            <i class="bi {{ $notification['icon'] }} text-{{ $notification['type'] }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <h6 class="mb-1 text-white small fw-bold text-truncate">{{ $notification['title'] }}
                                        </h6>
                                        <p class="mb-1 text-secondary x-small text-truncate" style="font-size: 11px;">
                                            {{ $notification['description'] }}
                                        </p>
                                        <small
                                            class="text-white-50 x-small">{{ $notification['time']->diffForHumans() }}</small>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-4 text-center text-white-50">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2 opacity-50"></i>
                                لا توجد إشعارات جديدة
                            </li>
                        @endforelse

                        <li><a class="dropdown-item text-center small text-primary py-2 fw-bold"
                                href="{{ route('notifications.index') }}">عرض كل الإشعارات</a></li>
                    </ul>
                </div>
        </nav>

        <!-- Page Content -->
        <main class="content px-4 pb-5 flex-grow-1">
            <!-- Flash Messages -->
            @if(session('success'))
                <x-alert type="success" class="mb-4 shadow-sm border-0">{{ session('success') }}</x-alert>
            @endif
            @if(session('error'))
                <x-alert type="danger" class="mb-4 shadow-sm border-0">{{ session('error') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert type="danger" class="mb-4 shadow-sm border-0">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    @stack('scripts')
</body>

</html>