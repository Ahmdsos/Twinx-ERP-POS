@extends('layouts.app')

@section('title', 'الماركات / العلامات التجارية')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-white mb-2 tracking-wide">الماركات التجارية</h3>
                <p class="text-gray-400 mb-0 small">إدارة الشركات والعلامات التجارية</p>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button
                        class="btn btn-icon-glass px-3 d-flex align-items-center gap-2 text-decoration-none dropdown-toggle border-0"
                        type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                        style="width: auto; height: 42px; background: rgba(30, 41, 59, 0.5); color: #cbd5e1;">
                        <i class="bi bi-cloud-download"></i>
                        <span class="d-none d-md-block small">تصدير</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark bg-slate-900 border-white-10 shadow-neon"
                        aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('export.brands', ['format' => 'xlsx']) }}">
                                <i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('export.brands', ['format' => 'csv']) }}">
                                <i class="bi bi-file-earmark-code text-info"></i> CSV (.csv)
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('brands.import.form') }}"
                    class="btn btn-icon-glass px-3 d-flex align-items-center gap-2 text-decoration-none"
                    style="width: auto; height: 42px;">
                    <i class="bi bi-cloud-upload"></i>
                    <span class="d-none d-md-block small">استيراد</span>
                </a>
                <button type="button"
                    class="btn btn-action-purple px-4 py-2 rounded-pill fw-bold shadow-neon-purple d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#createBrandModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>إضافة ماركة</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div
                class="alert bg-success bg-opacity-10 border border-success border-opacity-20 text-success rounded-3 mb-4 d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Brands Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-purple">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>اسم الماركة</th>
                            <th>الموقع الإلكتروني</th>
                            <th>الحالة</th>
                            <th>وقت الإضافة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                            <tr class="table-row-hover group-hover-actions">
                                <td class="ps-4 text-gray-500 font-monospace">{{ $loop->iteration }}</td>
                                <td>
                                    <div>
                                        <span class="text-white fw-bold d-block mb-1">{{ $brand->name }}</span>
                                        <span class="text-gray-500 x-small">{{ Str::limit($brand->description, 50) }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($brand->website)
                                        <a href="{{ $brand->website }}" target="_blank"
                                            class="text-cyan-400 text-decoration-none small hover-text-white transition-all">
                                            <i class="bi bi-link-45deg me-1"></i>
                                            {{ parse_url($brand->website, PHP_URL_HOST) ?? 'زيارة الموقع' }}
                                        </a>
                                    @else
                                        <span class="text-gray-600 small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($brand->is_active)
                                        <span class="d-inline-flex align-items-center gap-2 text-success small fw-bold">
                                            <span class="indicator-dot bg-success shadow-neon-sm"></span> نشط
                                        </span>
                                    @else
                                        <span class="d-inline-flex align-items-center gap-2 text-gray-500 small">
                                            <span class="indicator-dot bg-secondary"></span> غير نشط
                                        </span>
                                    @endif
                                </td>
                                <td class="text-gray-400 small font-monospace">
                                    {{ $brand->created_at->format('Y-m-d') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div
                                        class="opacity-0 group-hover-opacity-100 transition-all d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-icon-glass btn-sm"
                                            onclick="editBrand({{ $brand->id }}, '{{ $brand->name }}', '{{ $brand->description }}', '{{ $brand->website }}', {{ $brand->is_active ? 1 : 0 }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" class="d-inline"
                                            data-confirm="هل أنت متأكد من حذف هذه الماركة؟">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon-glass btn-sm text-danger hover-bg-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-gray-500">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox fs-1 opacity-20 mb-3"></i>
                                        <p class="mb-0">لا توجد ماركات مضافة</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0">
                <div class="modal-header border-bottom border-white-10">
                    <h5 class="modal-title text-white fw-bold">إضافة ماركة جديدة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('brands.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">اسم الماركة <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الوصف</label>
                            <textarea name="description"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الموقع
                                الإلكتروني</label>
                            <input type="url" name="website"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                placeholder="https://example.com">
                        </div>
                        <div class="mt-4">
                            <label class="custom-toggle d-flex align-items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked id="createActive">
                                <span class="toggle-switch"></span>
                                <div>
                                    <span class="text-white fw-bold d-block small">ماركة نشطة</span>
                                    <span class="text-gray-500 x-small">تظهر في قوائم المنتجات</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10">
                        <button type="button" class="btn btn-link text-gray-400 text-decoration-none"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-action-purple px-4 rounded-pill fw-bold">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0">
                <div class="modal-header border-bottom border-white-10">
                    <h5 class="modal-title text-white fw-bold">تعديل الماركة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">اسم الماركة <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الوصف</label>
                            <textarea name="description" id="editDescription"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الموقع
                                الإلكتروني</label>
                            <input type="url" name="website" id="editWebsite"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple">
                        </div>
                        <div class="mt-4">
                            <label class="custom-toggle d-flex align-items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" id="editActive">
                                <span class="toggle-switch"></span>
                                <div>
                                    <span class="text-white fw-bold d-block small">ماركة نشطة</span>
                                    <span class="text-gray-500 x-small">تظهر في قوائم المنتجات</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10">
                        <button type="button" class="btn btn-link text-gray-400 text-decoration-none"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-action-purple px-4 rounded-pill fw-bold">تحديث</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editBrand(id, name, description, website, isActive) {
            document.getElementById('editForm').action = '/brands/' + id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description || '';
            document.getElementById('editWebsite').value = website || '';
            document.getElementById('editActive').checked = isActive == 1;

            new bootstrap.Modal(document.getElementById('editBrandModal')).show();
        }
    </script>

    <style>
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 16px;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 0.8rem 1rem;
            border-radius: 12px;
        }

        .form-control-dark:focus {
            background: rgba(15, 23, 42, 0.8) !important;
        }

        .focus-ring-purple:focus {
            border-color: #a855f7 !important;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.1) !important;
        }

        .bg-dark-input {
            background: rgba(15, 23, 42, 0.8) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8;
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            border: none;
            color: white;
            transition: all 0.3s;
        }

        .btn-action-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.5);
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        .btn-icon-glass {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .hover-bg-danger:hover {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #fca5a5 !important;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .text-purple-400 {
            color: #c084fc !important;
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }

        .border-top-gradient-purple {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #a855f7, #c084fc) 1;
        }

        .hover-text-white:hover {
            color: white !important;
        }

        .indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .shadow-neon-sm {
            box-shadow: 0 0 5px rgba(34, 197, 94, 0.5);
        }

        /* Custom Toggles */
        .custom-toggle {
            position: relative;
            display: flex;
            align-items: center;
        }

        .custom-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-switch {
            position: relative;
            width: 48px;
            height: 26px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            transition: .3s;
        }

        .toggle-switch:before {
            content: "";
            position: absolute;
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: .3s;
        }

        .custom-toggle input:checked+.toggle-switch {
            background-color: #a855f7;
            border-color: #a855f7;
        }

        .custom-toggle input:checked+.toggle-switch:before {
            transform: translateX(20px);
        }
    </style>
@endsection