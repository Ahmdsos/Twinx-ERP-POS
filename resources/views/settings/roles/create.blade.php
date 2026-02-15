@extends('layouts.app')

@section('title', 'إضافة دور جديد')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-heading fw-bold"><i class="bi bi-shield-plus me-2"></i> إضافة دور وظيفي جديد</h2>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-right me-2"></i>{{ __('Back') }}</a>
            </div>

            <div class="glass-card p-4">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="mb-5">
                        <label class="form-label text-muted fs-5">اسم الدور الوظيفي <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-transparent text-body"
                            placeholder="مثال: مدير المستودع، محاسب، كاشير مسائي..." required value="{{ old('name') }}">
                    </div>

                    <h5 class="text-heading fw-bold mb-4 border-bottom border-secondary pb-2">
                        <i class="bi bi-grid-3x3-gap me-2"></i> جدول الصلاحيات
                    </h5>

                    <div class="row g-4">
                        @foreach($permissions as $category => $perms)
                            <div class="col-md-6 col-xl-4">
                                <div class="p-3 rounded border border-secondary border-opacity-25 h-100 bg-black bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-info fw-bold m-0 text-uppercase">{{ $category }}</h6>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" onchange="toggleGroup(this)">
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-2">
                                        @foreach($perms as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox"
                                                    name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                                                <label class="form-check-label text-muted" for="perm_{{ $perm->id }}">
                                                    {{ str_replace($category . '.', '', $perm->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-5">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-save me-2"></i> حفظ الدور
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        
    </style>

    <script>
        function toggleGroup(source) {
            const container = source.closest('.rounded');
            const checkboxes = container.querySelectorAll('.permission-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }
    </script>
@endsection