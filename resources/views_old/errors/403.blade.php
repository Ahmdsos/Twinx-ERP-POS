@extends('layouts.app')

@section('title', '403 - غير مسموح')

@section('content')
    <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
        <div class="text-center">
            <h1 class="display-1 text-warning fw-bold">403</h1>
            <i class="bi bi-shield-lock text-warning" style="font-size: 5rem;"></i>
            <h2 class="mt-4 mb-3">الوصول غير مسموح</h2>
            <p class="text-muted mb-4">ليس لديك الصلاحيات الكافية للوصول إلى هذه الصفحة.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-house me-2"></i>الصفحة الرئيسية
                </a>
                <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-right me-2"></i>العودة للخلف
                </button>
            </div>
        </div>
    </div>
@endsection