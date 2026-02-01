@extends('layouts.app')

@section('title', 'تعديل شركة الشحن - ' . $courier->name)

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="d-flex align-items-center mb-4">
                    <a href="{{ route('couriers.show', $courier) }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">تعديل شركة الشحن</h1>
                        <p class="text-muted mb-0">{{ $courier->name }} ({{ $courier->code }})</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('couriers.update', $courier) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <!-- Code -->
                                <div class="col-md-6">
                                    <label class="form-label">كود الشركة <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                        value="{{ old('code', $courier->code) }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $courier->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Contact Person -->
                                <div class="col-md-6">
                                    <label class="form-label">جهة الاتصال</label>
                                    <input type="text" name="contact_person" class="form-control"
                                        value="{{ old('contact_person', $courier->contact_person) }}">
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label class="form-label">الهاتف</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $courier->phone) }}">
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label">البريد الإلكتروني</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $courier->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tracking URL Template -->
                                <div class="col-md-6">
                                    <label class="form-label">رابط تتبع الشحنات</label>
                                    <input type="url" name="tracking_url_template"
                                        class="form-control @error('tracking_url_template') is-invalid @enderror"
                                        value="{{ old('tracking_url_template', $courier->tracking_url_template) }}"
                                        placeholder="https://track.company.com/{tracking_number}">
                                    <div class="form-text">استخدم {tracking_number} مكان رقم التتبع</div>
                                    @error('tracking_url_template')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="col-12">
                                    <label class="form-label">العنوان</label>
                                    <textarea name="address" class="form-control"
                                        rows="2">{{ old('address', $courier->address) }}</textarea>
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea name="notes" class="form-control"
                                        rows="2">{{ old('notes', $courier->notes) }}</textarea>
                                </div>

                                <!-- Is Active -->
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                            value="1" {{ old('is_active', $courier->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            الشركة نشطة ويمكن استخدامها في الشحنات
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <form action="{{ route('couriers.destroy', $courier) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف شركة الشحن؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i>
                                        حذف
                                    </button>
                                </form>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('couriers.show', $courier) }}" class="btn btn-secondary">إلغاء</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i>
                                        حفظ التغييرات
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection