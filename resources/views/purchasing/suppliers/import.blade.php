@extends('layouts.app')

@section('title', 'استيراد الموردين')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-slate-800 border-slate-700 shadow-lg">
                    <div class="card-header border-slate-700">
                        <h5 class="text-white mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i> استيراد الموردين من
                            Excel</h5>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-info bg-opacity-10 border-info text-info mb-4">
                            <strong>تعليمات:</strong>
                            <ul class="mb-0 small mt-2">
                                <li>قم بتحميل ملف النموذج أولاً للتأكد من تنسيق البيانات.</li>
                                <li>يمكنك تعديل الملف لإضافة المزيد من الحقول.</li>
                                <li>تأكد من عدم تكرار البريد الإلكتروني أو كود المورد.</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end mb-4">
                            <a href="{{ route('suppliers.import.sample') }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-download me-1"></i> تحميل نموذج Excel
                            </a>
                        </div>

                        <form action="{{ route('suppliers.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label text-white">اختر ملف Excel</label>
                                <input type="file" name="file" class="form-control bg-dark border-secondary text-white"
                                    accept=".xlsx,.xls,.csv" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="bi bi-cloud-upload me-2"></i> بدء الاستيراد
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection