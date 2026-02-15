<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
    data-theme="{{ session('theme', 'dark') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Twinx ERP') }} - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 (RTL or LTR based on locale) -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    @endif
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <!-- Google Fonts (Cairo for Arabic, Inter for English) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/cairo/cairo.css') }}">
    <!-- Theme Token System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Alpine.js -->
    <script defer src="{{ asset('assets/vendor/alpinejs/alpine.min.js') }}"></script>

    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        /* Professional Fixed Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            inset-inline-start: 0;
            bottom: 0;
            width: var(--sidebar-width);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease-in-out;
        }

        /* Main Content Offset */
        .main-wrapper {
            margin-inline-start: var(--sidebar-width);
            transition: margin-inline-start 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(100%);
                /* AR: Hide to Right */
            }

            html[dir="ltr"] .sidebar {
                transform: translateX(-100%);
                /* EN: Hide to Left */
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-inline-start: 0;
            }
        }

        /* Elegant Scrollbar */
        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            border-radius: 4px;
        }

        /* Nav Item Styling */
        .nav-link {
            padding: 12px 24px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.2s ease;
            border-inline-start: 3px solid transparent;
        }

        .nav-group-label {
            padding: 24px 24px 8px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
        }
    </style>
    @stack('styles')
</head>

<body>

    <!-- Sidebar Container (Fixed) -->
    <nav class="sidebar collapse d-lg-flex" id="sidebarMenu">
        <!-- Logo -->
        <div class="d-flex align-items-center flex-shrink-0 px-4"
            style="height: var(--header-height); border-bottom: 1px solid var(--border-color);">
            <div class="me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <img src="{{ asset('images/logo.png') }}" alt="Twinx ERP" class="img-fluid rounded-circle shadow-sm">
            </div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Twinx <span class="text-primary">ERP</span>
            </h5>
        </div>

        <!-- Scrollable Menu -->
        <div class="sidebar-scroll py-3">
            @include('partials.sidebar')
        </div>

        <!-- User Profile (Fixed Bottom) + Theme Toggle -->
        <div class="mt-auto p-3" style="border-top: 1px solid var(--border-color); background: var(--hover-overlay);">
            <div class="d-flex align-items-center justify-content-between">
                <div class="dropdown flex-grow-1">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                        style="color: var(--text-primary);" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('images/default_avatar.png') }}" alt="" width="36" height="36"
                            class="rounded-circle me-2">
                        <div class="overflow-hidden">
                            <div class="fw-medium small text-truncate">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="x-small text-truncate" style="font-size: 11px; color: var(--text-muted);">
                                {{ __('Online') }}
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu shadow-lg border-0">
                        <li><a class="dropdown-item small" href="{{ route('settings.index') }}"><i
                                    class="bi bi-gear me-2"></i> {{ __('Settings') }}</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item small text-danger"><i
                                        class="bi bi-box-arrow-right me-2"></i> {{ __('Logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
                <!-- Theme Toggle Button -->
                <button class="theme-toggle ms-2 flex-shrink-0" id="themeToggleBtn" title="{{ __('Toggle Theme') }}"
                    aria-label="{{ __('Toggle Theme') }}">
                    <i class="bi" id="themeIcon"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg px-4 py-3" style="height: var(--header-height);">
            <button class="navbar-toggler me-2 border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <h5 class="mb-0 fw-bold" style="color: var(--text-heading);">@yield('header')</h5>

            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-icon btn-outline-secondary border-0 rounded-circle position-relative"
                        style="color: var(--text-primary);" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-bell{{ $dashboardNotifications->count() > 0 ? '-fill' : '' }}"></i>
                        @if($dashboardNotifications->count() > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                <span class="visually-hidden">{{ __('New alerts') }}</span>
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0"
                        style="width: 320px; max-height: 480px; overflow-y: auto;">
                        <li class="p-3"
                            style="border-bottom: 1px solid var(--border-color); background: var(--hover-overlay);">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold" style="color: var(--text-heading);">{{ __('Notifications') }}
                                </h6>
                                <span
                                    class="badge bg-primary rounded-pill">{{ $dashboardNotifications->count() }}</span>
                            </div>
                        </li>

                        @forelse($dashboardNotifications as $notification)
                            <li>
                                <a class="dropdown-item p-3 d-flex gap-3"
                                    style="border-bottom: 1px solid var(--border-color);" href="{{ $notification['url'] }}">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 36px; height: 36px; background-color: rgba(var(--bs-{{ $notification['type'] }}-rgb), 0.15);">
                                            <i class="bi {{ $notification['icon'] }} text-{{ $notification['type'] }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <h6 class="mb-1 small fw-bold text-truncate" style="color: var(--text-primary);">
                                            {{ $notification['title'] }}
                                        </h6>
                                        <p class="mb-1 x-small text-truncate"
                                            style="font-size: 11px; color: var(--text-muted);">
                                            {{ $notification['description'] }}
                                        </p>
                                        <small class="x-small"
                                            style="color: var(--text-muted);">{{ $notification['time']->diffForHumans() }}</small>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-4 text-center" style="color: var(--text-muted);">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2 opacity-50"></i>
                                {{ __('No new notifications') }}
                            </li>
                        @endforelse

                        <li><a class="dropdown-item text-center small text-primary py-2 fw-bold"
                                href="{{ route('notifications.index') }}">{{ __('View All Notifications') }}</a></li>
                    </ul>
                </div>
        </nav>

        <!-- Page Content -->
        <main class="content px-4 pb-5 flex-grow-1">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==========================================
        // Theme Toggle Logic
        // ==========================================
        (function () {
            const html = document.documentElement;
            const icon = document.getElementById('themeIcon');
            const btn = document.getElementById('themeToggleBtn');

            function applyIcon() {
                const theme = html.getAttribute('data-theme') || 'dark';
                if (icon) {
                    icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                }
            }
            applyIcon();

            if (btn) {
                btn.addEventListener('click', function () {
                    const current = html.getAttribute('data-theme') || 'dark';
                    const next = current === 'dark' ? 'light' : 'dark';

                    html.classList.add('theme-transitioning');
                    html.setAttribute('data-theme', next);
                    applyIcon();

                    setTimeout(function () {
                        html.classList.remove('theme-transitioning');
                    }, 400);

                    // Persist to server
                    fetch('{{ url("/theme/toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ theme: next })
                    }).catch(function () { /* silent */ });
                });
            }
        })();

        $(document).ready(function () {
            // Compute SweetAlert theme colours from CSS variables
            const cs = getComputedStyle(document.documentElement);
            const swalBg = cs.getPropertyValue('--swal-bg').trim() || '#1e293b';
            const swalColor = cs.getPropertyValue('--swal-color').trim() || '#fff';
            const swalBtn = cs.getPropertyValue('--swal-confirm').trim() || '#3b82f6';
            const swalCancel = cs.getPropertyValue('--swal-cancel').trim() || '#6b7280';

            // SweetAlert2 Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: swalBg,
                color: swalColor,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Global Flash Messages
            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}"
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Error") }}',
                    text: "{{ session('error') }}",
                    background: swalBg,
                    color: swalColor,
                    confirmButtonColor: swalBtn,
                    confirmButtonText: '{{ __("OK") }}'
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("Warning") }}',
                    html: '<div class="text-start"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>',
                    background: swalBg,
                    color: swalColor,
                    confirmButtonColor: swalBtn,
                    confirmButtonText: '{{ __("OK") }}'
                });
            @endif

            // Global Confirm Helper
            window.confirmAction = function(options) {
                return Swal.fire({
                    title: options.title || '{{ __("Are you sure?") }}',
                    text: options.text || '{{ __("You will not be able to undo this action!") }}',
                    icon: options.icon || 'warning',
                    showCancelButton: true,
                    confirmButtonColor: options.confirmColor || swalBtn,
                    cancelButtonColor: swalCancel,
                    confirmButtonText: options.confirmText || '{{ __("Yes, confirm") }}',
                    cancelButtonText: options.cancelText || '{{ __("Cancel") }}',
                    background: swalBg,
                    color: swalColor,
                    reverseButtons: true
                });
            };

            // Auto-intercept forms with data-confirm
            $(document).on('submit', 'form[data-confirm]', function (e) {
                e.preventDefault();
                const form = this;
                const message = $(form).data('confirm');
                const title = $(form).data('confirm-title') || '{{ __("Confirm Action") }}';

                window.confirmAction({
                    title: title,
                    text: message,
                    confirmText: '{{ __("Yes, continue") }}',
                    confirmColor: swalBtn
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // ==========================================
            // Barcode Scanner Listener (Invisible)
            // ==========================================
            let barcodeBuffer = '';
            let barcodeLastKeyTime = Date.now();
            const BARCODE_DELAY = 100; // ms

            document.addEventListener('keydown', function (e) {
                // Ignore if focusing on input/textarea
                if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

                // Allow pages to block global scanner (e.g. Data Entry forms)
                if (window.blockGlobalScanner) return;

                const currentTime = Date.now();

                if (currentTime - barcodeLastKeyTime > BARCODE_DELAY) {
                    barcodeBuffer = ''; // Reset buffer if too slow (manual typing)
                }

                if (e.key === 'Enter') {
                    if (barcodeBuffer.length > 3) {
                        handleBarcodeScan(barcodeBuffer);
                    }
                    barcodeBuffer = '';
                } else if (e.key.length === 1) { // Printable chars only
                    barcodeBuffer += e.key;
                }

                barcodeLastKeyTime = currentTime;
            });

            function handleBarcodeScan(code) {
                // Determine type based on prefix
                code = code.trim().toUpperCase();

                // Invoice Pattern (INV-...)
                if (code.startsWith('INV-') || /^\d+$/.test(code)) {
                    const url = "{{ route('sales-invoices.show', ':id') }}".replace(':id', code);
                    window.location.href = url + "?scanned=true";

                    Toast.fire({
                        icon: 'info',
                        title: '{{ __("Opening invoice:") }} ' + code
                    });
                }
                // Return Pattern (RET-...)
                else if (code.startsWith('RET-')) {
                    const url = "{{ route('sales-returns.show', ':id') }}".replace(':id', code);
                    window.location.href = url + "?scanned=true";

                    Toast.fire({
                        icon: 'info',
                        title: '{{ __("Opening return:") }} ' + code
                    });
                }
            }
        });
    </script>
    @stack('scripts')
</body>

</html>