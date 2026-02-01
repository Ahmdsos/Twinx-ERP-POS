@extends('layouts.app')

@section('title', 'استيراد المنتجات - Twinx ERP')
@section('page-title', 'استيراد المنتجات من CSV')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">المنتجات</a></li>
    <li class="breadcrumb-item active">استيراد</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>استيراد المنتجات من ملف CSV</h5>
                </div>
                <div class="card-body">
                    <!-- Instructions -->
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>تعليمات الاستيراد:</h6>
                        <ul class="mb-0">
                            <li>قم بتحميل ملف CSV النموذجي وأضف بياناتك</li>
                            <li>الأعمدة المطلوبة: <strong>sku</strong>, <strong>name</strong></li>
                            <li>الأعمدة الاختيارية: barcode, description, category, unit, cost_price, selling_price,
                                tax_rate, reorder_level</li>
                            <li>إذا كان SKU موجود بالفعل، سيتم تحديث المنتج</li>
                            <li>الحد الأقصى لحجم الملف: 5 ميجا</li>
                        </ul>
                    </div>

                    <!-- Download Sample -->
                    <div class="mb-4">
                        <a href="{{ route('products.import.sample') }}" class="btn btn-outline-primary">
                            <i class="bi bi-download me-2"></i>تحميل ملف CSV نموذجي
                        </a>
                    </div>

                    <hr>

                    <!-- Upload Form -->
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">ملف CSV</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" name="csv_file"
                                accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">اختر ملف CSV يحتوي على بيانات المنتجات</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
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