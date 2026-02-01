@extends('layouts.app')

@section('title', 'وحدات القياس')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-white mb-2 tracking-wide">وحدات القياس</h3>
                <p class="text-gray-400 mb-0 small">إدارة وحدات القياس الأساسية والفرعية للمنتجات</p>
            </div>
            <button type="button"
                class="btn btn-action-purple px-4 py-2 rounded-pill fw-bold shadow-neon-purple d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#createUnitModal">
                <i class="bi bi-plus-lg"></i>
                <span>إضافة وحدة جديدة</span>
            </button>
        </div>

        @if(session('success'))
            <div
                class="alert bg-success bg-opacity-10 border border-success border-opacity-20 text-success rounded-3 mb-4 d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div
                class="alert bg-danger bg-opacity-10 border border-danger border-opacity-20 text-danger rounded-3 mb-4 d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Units Table -->
        <div class="glass-panel overflow-hidden border-top-gradient-purple">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>اسم الوحدة</th>
                            <th>الاختصار</th>
                            <th>النوع</th>
                            <th>معامل التحويل</th>
                            <th>المنتجات المرتبطة</th>
                            <th class="pe-4 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr class="table-row-hover group-hover-actions">
                                <td class="ps-4 text-gray-500 font-monospace">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="text-white fw-bold">{{ $unit->name }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-slate-800 text-gray-400 border border-white-5 fw-normal font-monospace">
                                        {{ $unit->abbreviation }}
                                    </span>
                                </td>
                                <td>
                                    @if($unit->base_unit_id)
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20">فرعية</span>
                                    @else
                                        <span
                                            class="badge bg-purple-500 bg-opacity-10 text-purple-400 border border-purple-500 border-opacity-20">أساسية</span>
                                    @endif
                                </td>
                                <td class="text-gray-300">
                                    @if($unit->base_unit_id)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-white font-monospace">1 {{ $unit->name }}</span>
                                            <i class="bi bi-arrow-right text-gray-600 x-small"></i>
                                            <span class="fw-bold text-info font-monospace">{{ $unit->conversion_factor + 0 }}
                                                {{ $unit->baseUnit->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-600 small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-gray-400 font-monospace small">
                                        {{ $unit->products_count }} منتج
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div
                                        class="opacity-0 group-hover-opacity-100 transition-all d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-icon-glass btn-sm"
                                            onclick="editUnit({{ $unit->id }}, '{{ $unit->name }}', '{{ $unit->abbreviation }}', '{{ $unit->base_unit_id }}', '{{ $unit->conversion_factor }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($unit->products_count == 0)
                                            <form action="{{ route('units.destroy', $unit->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon-glass btn-sm text-danger hover-bg-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-gray-500">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox fs-1 opacity-20 mb-3"></i>
                                        <p class="mb-0">لا توجد وحدات قياس مضافة بعد</p>
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
    <div class="modal fade" id="createUnitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0">
                <div class="modal-header border-bottom border-white-10">
                    <h5 class="modal-title text-white fw-bold">إضافة وحدة قياس جديدة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">اسم الوحدة <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                required placeholder="مثال: كرتونة">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الاختصار <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="abbreviation"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                required placeholder="مثال: Box">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">نوع الوحدة</label>
                            <select name="base_unit_id" id="createBaseUnitId"
                                class="form-select form-select-dark text-white cursor-pointer"
                                onchange="toggleConversion('create')">
                                <option value="">أساسية (Base Unit)</option>
                                @foreach($units->where('base_unit_id', null) as $baseUnit)
                                    <option value="{{ $baseUnit->id }}">فرعية من: {{ $baseUnit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="createConversionDiv">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">معامل
                                التحويل</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark-input border-end-0 text-gray-500">1 وحدة جديدة
                                    =</span>
                                <input type="number" step="0.0001" name="conversion_factor"
                                    class="form-control form-control-dark border-start-0 border-end-0 text-white fw-bold text-center"
                                    placeholder="12">
                                <span class="input-group-text bg-dark-input border-start-0 text-gray-500">من الوحدة
                                    الأساسية</span>
                            </div>
                            <div class="form-text text-gray-500 x-small mt-2">مثال: لو الكرتونة فيها 12 قطعة، اكتب 12.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10">
                        <button type="button" class="btn btn-link text-gray-400 text-decoration-none"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-action-purple px-4 rounded-pill fw-bold">حفظ الوحدة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel border-0">
                <div class="modal-header border-bottom border-white-10">
                    <h5 class="modal-title text-white fw-bold">تعديل وحدة القياس</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">اسم الوحدة <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">الاختصار <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="abbreviation" id="editAbbreviation"
                                class="form-control form-control-dark text-white placeholder-gray-600 focus-ring-purple"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">نوع الوحدة</label>
                            <select name="base_unit_id" id="editBaseUnitId"
                                class="form-select form-select-dark text-white cursor-pointer"
                                onchange="toggleConversion('edit')">
                                <option value="">أساسية (Base Unit)</option>
                                @foreach($units->where('base_unit_id', null) as $baseUnit)
                                    <option value="{{ $baseUnit->id }}">فرعية من: {{ $baseUnit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="editConversionDiv">
                            <label class="form-label text-purple-400 small fw-bold text-uppercase ps-1">معامل
                                التحويل</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark-input border-end-0 text-gray-500">1 وحدة =</span>
                                <input type="number" step="0.0001" name="conversion_factor" id="editConversionFactor"
                                    class="form-control form-control-dark border-start-0 border-end-0 text-white fw-bold text-center">
                                <span class="input-group-text bg-dark-input border-start-0 text-gray-500">من الأساسية</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white-10">
                        <button type="button" class="btn btn-link text-gray-400 text-decoration-none"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-action-purple px-4 rounded-pill fw-bold">تحديث
                            البيانات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleConversion(mode) {
            const baseSelect = document.getElementById(mode + 'BaseUnitId');
            const conversionDiv = document.getElementById(mode + 'ConversionDiv');

            if (baseSelect.value) {
                conversionDiv.classList.remove('d-none');
            } else {
                conversionDiv.classList.add('d-none');
            }
        }

        function editUnit(id, name, abbr, baseId, factor) {
            document.getElementById('editForm').action = '/units/' + id;
            document.getElementById('editName').value = name;
            document.getElementById('editAbbreviation').value = abbr;
            document.getElementById('editBaseUnitId').value = baseId || '';
            document.getElementById('editConversionFactor').value = factor || '';

            toggleConversion('edit');

            new bootstrap.Modal(document.getElementById('editUnitModal')).show();
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

        .form-control-dark:focus,
        .form-select-dark:focus {
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

        .border-top-gradient-purple {
            border-top: 4px solid;
            border-image: linear-gradient(to right, #a855f7, #c084fc) 1;
        }
    </style>
@endsection