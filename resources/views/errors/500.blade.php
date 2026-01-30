@extends('layouts.app')

@section('title', '500 - خطأ في الخادم')

@section('content')
    <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
        <div class="text-center">
            <h1 class="display-1 text-danger fw-bold">500</h1>
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 5rem;"></i>
            <h2 class="mt-4 mb-3">خطأ في الخادم</h2>
            <p class="text-muted mb-4">عذراً، حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى لاحقاً.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-house me-2"></i>الصفحة الرئيسية
                </a>
                <button onclick="location.reload()" class="btn btn-outline-warning btn-lg">
                    <i class="bi bi-arrow-clockwise me-2"></i>إعادة المحاولة
                </button>
            </div>
        </div>
    </div>
@endsection