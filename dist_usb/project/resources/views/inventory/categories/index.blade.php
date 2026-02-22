@extends('layouts.app')

@section('title', 'إدارة التصنيفات')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-box bg-gradient-primary shadow-neon">
                    <i class="bi bi-diagram-3-fill fs-3 text-body"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-heading mb-1 tracking-wide">إدارة التصنيفات</h2>
                    <p class="mb-0 text-secondary small">مركز التحكم في هيكلة وتنظيم المنتجات</p>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="dropdown">
                    <button
                        class="btn btn-dark-glass d-flex align-items-center gap-2 border-0 text-secondary hover-text-white dropdown-toggle"
                        type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-cloud-download"></i>
                        <span class="d-none d-md-block">{{ __('Export') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark bg-surface border-secondary border-opacity-10-10 shadow-neon"
                        aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('export.categories', ['format' => 'xlsx']) }}">
                                <i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('export.categories', ['format' => 'csv']) }}">
                                <i class="bi bi-file-earmark-code text-info"></i> CSV (.csv)
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('categories.import.form') }}"
                    class="btn btn-dark-glass d-flex align-items-center gap-2 border-0 text-secondary hover-text-white">
                    <i class="bi bi-cloud-upload"></i>
                    <span class="d-none d-md-block">{{ __('Import') }}</span>
                </a>
                <a href="{{ route('categories.create') }}"
                    class="btn btn-action-primary d-flex align-items-center gap-2 shadow-lg">
                    <i class="bi bi-plus-lg"></i>
                    <span class="fw-bold">{{ __('Create Expense Category') }}</span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card glass-panel position-relative overflow-hidden h-100 p-4">
                    <div class="z-1 position-relative">
                        <p class="text-cyan-400 small fw-bold text-uppercase mb-2 tracking-wider">إجمالي التصنيفات</p>
                        <h1 class="fw-bold text-heading mb-0 display-4">{{ $categories->count() }}</h1>
                        <p class="text-gray-500 small mt-2">تصنيف مسجل بالنظام</p>
                    </div>
                    <i
                        class="bi bi-tags-fill position-absolute bottom-0 end-0 display-1 text-body opacity-5 transform-scale-150"></i>
                    <div class="glow-orb bg-cyan-500"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card glass-panel position-relative overflow-hidden h-100 p-4">
                    <div class="z-1 position-relative">
                        <p class="text-purple-400 small fw-bold text-uppercase mb-2 tracking-wider">التصنيفات الرئيسية</p>
                        <h1 class="fw-bold text-heading mb-0 display-4">{{ $categories->whereNull('parent_id')->count() }}
                        </h1>
                        <p class="text-gray-500 small mt-2">جذور لشجرة الأصناف</p>
                    </div>
                    <i
                        class="bi bi-diagram-3-fill position-absolute bottom-0 end-0 display-1 text-body opacity-5 transform-scale-150"></i>
                    <div class="glow-orb bg-purple-500"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card glass-panel position-relative overflow-hidden h-100 p-4">
                    <div class="z-1 position-relative">
                        <p class="text-emerald-400 small fw-bold text-uppercase mb-2 tracking-wider">التصنيفات الفرعية</p>
                        <h1 class="fw-bold text-heading mb-0 display-4">{{ $categories->whereNotNull('parent_id')->count() }}
                        </h1>
                        <p class="text-gray-500 small mt-2">تفرعات دقيقة</p>
                    </div>
                    <i
                        class="bi bi-arrow-return-right position-absolute bottom-0 end-0 display-1 text-body opacity-5 transform-scale-150"></i>
                    <div class="glow-orb bg-emerald-500"></div>
                </div>
            </div>
        </div>

        <!-- Main Content Panel -->
        <div class="glass-panel border-top-gradient p-0 overflow-hidden">
            <!-- Toolbar -->
            <div class="p-4 border-bottom border-secondary border-opacity-10-10 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-heading mb-0">قائمة التصنيفات</h5>
                <div class="position-relative" style="width: 300px;">
                    <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-gray-500"></i>
                    <input type="text" id="searchInput" class="form-control form-control ps-2 pe-5"
                        placeholder="بحث سريع...">
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">اسم التصنيف</th>
                            <th>المسار الهيكلي</th>
                            <th class="text-center">{{ __('Products') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="pe-4 text-end">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr class="table-row-hover">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="avatar-icon {{ $category->parent_id ? 'bg-secondary-soft text-secondary' : 'bg-primary-soft text-cyan-400' }}">
                                            <i
                                                class="bi {{ $category->parent_id ? 'bi-arrow-return-right' : 'bi-folder-fill' }}"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('categories.show', $category) }}"
                                                class="fw-bold text-body text-decoration-none hover-glow transition-all">{{ $category->name }}</a>
                                            <div class="small text-gray-500">
                                                {{ Str::limit($category->description ?? 'لا يوجد وصف', 40) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($category->parent)
                                        <a href="{{ route('categories.show', $category->parent) }}"
                                            class="badge badge-outline-secondary text-decoration-none">
                                            <i class="bi bi-folder2-open me-1 opacity-50"></i> {{ $category->parent->name }}
                                        </a>
                                    @else
                                        <span class="badge badge-outline-primary">رئيسي (Root)</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div
                                        class="d-inline-flex align-items-center justify-content-center bg-cyan-500 bg-opacity-20 rounded-pill px-3 py-1 border border-cyan-500 border-opacity-30">
                                        <span class="fw-bold text-cyan-400">{{ $category->products_count }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="pulse-dot bg-success"></div>
                                            <span class="text-success small fw-bold">{{ __('Active') }}</span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="pulse-dot bg-danger"></div>
                                            <span class="text-danger small fw-bold">{{ __('Inactive') }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-icon-only text-secondary hover-text-white" type="button"
                                            data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul
                                            class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-neon-sm border-secondary border-opacity-10-10">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2"
                                                    href="{{ route('categories.show', $category) }}">
                                                    <i class="bi bi-eye text-cyan-400"></i>{{ __('View Details') }}</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2"
                                                    href="{{ route('categories.edit', $category) }}">
                                                    <i class="bi bi-pencil text-warning"></i>{{ __('Edit') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider border-secondary border-opacity-10-10">
                                            </li>
                                            <li>
                                                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                                    data-confirm="هل أنت متأكد من حذف هذا التصنيف؟">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                        <i class="bi bi-trash"></i>{{ __('Delete') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="icon mb-3 text-gray-600"><i class="bi bi-folder-x display-4"></i></div>
                                        <h5 class="text-secondary">لا توجد تصنيفات</h5>
                                        <p class="text-gray-600 mb-0">ابدأ بإضافة تصنيفات لترتيب منتجاتك</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        /* Premium Theme Variables */
        /* :root override removed for theme compatibility */

        /* Core Layout */
        .btn-dark-glass {
            background: var(--btn-glass-bg);
            border: 1px solid var(--glass-border);
            transition: all 0.3s;
        }

        .btn-dark-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .btn-action-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none;
            color: var(--text-primary);
            transition: all 0.3s;
            box-shadow: var(--primary-glow);
        }

        .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.5);
        }

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .border-top-gradient {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #0ea5e9, #8b5cf6) 1;
        }

        .icon-box {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2) 0%, rgba(2, 132, 199, 0.2) 100%);
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        /* Stats Cards */
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .glow-orb {
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.15;
            top: -50px;
            right: -50px;
        }

        .transform-scale-150 {
            transform: scale(1.5) rotate(-10deg);
        }

        /* Inputs */
        .form-control-dark {
            background: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid var(--glass-border) !important;
            color: var(--text-primary); !important;
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }

        .form-control-dark:focus {
            border-color: #0ea5e9 !important;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1) !important;
        }

        /* Table Styles */
        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--glass-border);
            color: var(--text-body);
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            padding: 1.25rem 1rem;
            border-bottom-width: 0;
        }

        .table-dark-custom td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .table-row-hover:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .table-row-hover:last-child td {
            border-bottom: 0;
        }

        /* Components */
        .avatar-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .bg-primary-soft {
            background: rgba(14, 165, 233, 0.15);
        }

        .bg-secondary-soft {
            background: rgba(148, 163, 184, 0.1);
        }

        .badge-outline-primary {
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #38bdf8;
            background: rgba(14, 165, 233, 0.05);
            padding: 0.35em 0.8em;
            border-radius: 6px;
        }

        .badge-outline-secondary {
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: var(--text-secondary);
            background: rgba(148, 163, 184, 0.05);
            padding: 0.35em 0.8em;
            border-radius: 6px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            box-shadow: 0 0 0 rgba(255, 255, 255, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(74, 222, 128, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0);
            }
        }

        /* Utilities */
        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-emerald-400 {
            color: #34d399 !important;
        }

        .text-gray-300 {
            color: #cbd5e1 !important;
        }

        .text-gray-400 {
            color: var(--text-secondary); !important;
        }

        .text-gray-500 {
            color: #64748b !important;
        }

        .text-gray-600 {
            color: #475569 !important;
        }

        .hover-glow:hover {
            text-shadow: 0 0 10px rgba(34, 211, 238, 0.5);
            color: #fff !important;
        }

        .tracking-wide {
            letter-spacing: 0.025em;
        }

        .tracking-wider {
            letter-spacing: 0.05em;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Simple Search Filter
        document.getElementById('searchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endpush