<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <!-- Toggle Sidebar Button -->
        <button class="btn btn-link text-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Page Title -->
        <span class="navbar-brand mb-0 h5">@yield('page-title', 'لوحة التحكم')</span>

        <!-- Right Side -->
        <div class="d-flex align-items-center ms-auto">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        id="notificationBadge" style="display: none;">
                        0
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                    <li>
                        <h6 class="dropdown-header">الإشعارات</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-muted text-center" href="#">لا توجد إشعارات جديدة</a></li>
                </ul>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-link text-dark d-flex align-items-center" data-bs-toggle="dropdown">
                    <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 35px; height: 35px;">
                        <span
                            class="text-white small">{{ auth()->user() ? substr(auth()->user()->name, 0, 1) : 'U' }}</span>
                    </div>
                    <span class="d-none d-md-inline">{{ auth()->user()?->name ?? 'User' }}</span>
                    <i class="bi bi-chevron-down ms-1 small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>الملف الشخصي</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>الإعدادات</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>