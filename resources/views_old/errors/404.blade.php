@extends('layouts.app')

@section('title', '404 - الصفحة غير موجودة')

@section('content')
    <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
        <div class="text-center">
            <h1 class="display-1 text-muted fw-bold">404</h1>
            <i class="bi bi-emoji-frown text-warning" style="font-size: 5rem;"></i>
            <h2 class="mt-4 mb-3">الصفحة غير موجودة</h2>
            <p class="text-muted mb-4">عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.</p>
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