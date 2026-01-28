@extends('layouts.app')

@section('title', 'وحدات القياس - Twinx ERP')
@section('page-title', 'إدارة وحدات القياس')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">وحدات القياس</li>
@endsection

@section('content')
    <div class="row">
        <!-- Units List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-rulers me-2"></i>وحدات القياس</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                        <i class="bi bi-plus-circle me-1"></i>وحدة جديدة
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الاسم</th>
                                    <th>الاختصار</th>
                                    <th>النوع</th>
                                    <th>الوحدة الأساسية</th>
                                    <th>معامل التحويل</th>
                                    <th>عدد المنتجات</th>
                                    <th class="text-center" style="width: 100px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units ?? [] as $unit)
                                    <tr>
                                        <td>
                                            <i class="bi bi-rulers text-primary me-2"></i>
                                            <strong>{{ $unit->name }}</strong>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $unit->abbreviation }}</span></td>
                                        <td>
                                            @if($unit->is_base)
                                                <span class="badge bg-success">أساسية</span>
                                            @else
                                                <span class="badge bg-info">مشتقة</span>
                                            @endif
                                        </td>
                                        <td>{{ $unit->baseUnit?->name ?? '-' }}</td>
                                        <td>{{ number_format($unit->conversion_factor, 2) }}</td>
                                        <td>{{ $unit->products_count ?? 0 }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                    onclick="editUnit({{ $unit->id }}, '{{ $unit->name }}', '{{ $unit->abbreviation }}', {{ $unit->base_unit_id ?? 'null' }}, {{ $unit->conversion_factor }})"
                                                    title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if(($unit->products_count ?? 0) == 0)
                                                    <form action="{{ route('units.destroy', $unit) }}" method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('هل أنت متأكد من حذف هذه الوحدة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="bi bi-rulers fs-1 d-block mb-3"></i>
                                            <h5>لا توجد وحدات قياس</h5>
                                            <p class="mb-3">ابدأ بإضافة وحدة جديدة</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addUnitModal">
                                                <i class="bi bi-plus-circle me-1"></i>إضافة وحدة
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Add Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة سريعة</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('units.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">اسم الوحدة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                value="{{ old('name') }}" placeholder="مثال: قطعة" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الاختصار <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('abbreviation') is-invalid @enderror"
                                name="abbreviation" value="{{ old('abbreviation') }}" placeholder="مثال: pcs" required
                                maxlength="10">
                            @error('abbreviation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الوحدة الأساسية (للتحويل)</label>
                            <select class="form-select @error('base_unit_id') is-invalid @enderror" name="base_unit_id">
                                <option value="">بدون (وحدة أساسية)</option>
                                @foreach($units ?? [] as $u)
                                    @if($u->is_base)
                                        <option value="{{ $u->id }}" {{ old('base_unit_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->abbreviation }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('base_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">معامل التحويل</label>
                            <input type="number" class="form-control @error('conversion_factor') is-invalid @enderror"
                                name="conversion_factor" value="{{ old('conversion_factor', 1) }}" step="0.000001"
                                min="0.000001">
                            <small class="text-muted">مثال: 12 إذا كانت الكرتونة = 12 قطعة</small>
                            @error('conversion_factor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg me-1"></i>حفظ
                        </button>
                    </form>
                </div>
            </div>

            <!-- Common Units -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>وحدات شائعة</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark">قطعة (pcs)</span>
                        <span class="badge bg-light text-dark">كيلو (kg)</span>
                        <span class="badge bg-light text-dark">جرام (g)</span>
                        <span class="badge bg-light text-dark">متر (m)</span>
                        <span class="badge bg-light text-dark">لتر (L)</span>
                        <span class="badge bg-light text-dark">كرتونة (ctn)</span>
                        <span class="badge bg-light text-dark">علبة (box)</span>
                        <span class="badge bg-light text-dark">دستة (dzn)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div class="modal fade" id="addUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة وحدة قياس</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اسم الوحدة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الاختصار <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="abbreviation" required maxlength="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الوحدة الأساسية (اختياري)</label>
                            <select class="form-select" name="base_unit_id">
                                <option value="">بدون (وحدة أساسية)</option>
                                @foreach($units ?? [] as $u)
                                    @if($u->is_base)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->abbreviation }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">معامل التحويل</label>
                            <input type="number" class="form-control" name="conversion_factor" value="1" step="0.000001"
                                min="0.000001">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل وحدة القياس</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUnitForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اسم الوحدة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الاختصار <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="abbreviation" id="edit_abbreviation" required
                                maxlength="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الوحدة الأساسية</label>
                            <select class="form-select" name="base_unit_id" id="edit_base_unit_id">
                                <option value="">بدون (وحدة أساسية)</option>
                                @foreach($units ?? [] as $u)
                                    @if($u->is_base)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->abbreviation }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">معامل التحويل</label>
                            <input type="number" class="form-control" name="conversion_factor" id="edit_conversion_factor"
                                step="0.000001" min="0.000001">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editUnit(id, name, abbreviation, baseUnitId, conversionFactor) {
            document.getElementById('editUnitForm').action = '/units/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_abbreviation').value = abbreviation;
            document.getElementById('edit_base_unit_id').value = baseUnitId || '';
            document.getElementById('edit_conversion_factor').value = conversionFactor;
            new bootstrap.Modal(document.getElementById('editUnitModal')).show();
        }
    </script>
@endpush