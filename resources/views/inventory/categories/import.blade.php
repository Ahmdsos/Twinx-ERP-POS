@extends('layouts.app')

@section('title', 'استيراد التصنيفات - Twinx ERP')
@section('page-title', 'استيراد التصنيفات من CSV')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">التصنيفات</a></li>
    <li class="breadcrumb-item active">استيراد</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>استيراد التصنيفات من ملف CSV</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>تعليمات الاستيراد:</h6>
                        <ul class="mb-0">
                            <li>الأعمدة المطلوبة: <strong>name</strong></li>
                            <li>الأعمدة الاختيارية: parent (اسم التصنيف الأب), description</li>
                            <li>إذا كان الاسم موجود، لن يتم إضافته مرة أخرى</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <a href="{{ route('categories.import.sample') }}" class="btn btn-outline-primary">
                            <i class="bi bi-download me-2"></i>تحميل ملف CSV نموذجي
                        </a>
                    </div>

                    <hr>

                    <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">ملف CSV</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" name="csv_file"
                                accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-right me-2"></i>رجوع
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload me-2"></i>استيراد البيانات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection