@extends('layouts.app')

@section('title', 'إنشاء قيد - Twinx ERP')
@section('page-title', 'إنشاء قيد يومية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('journals.index') }}">القيود اليومية</a></li>
    <li class="breadcrumb-item active">قيد جديد</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-journal-plus me-2"></i>إنشاء قيد يومية</h5>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-plus fs-1 text-muted"></i>
            <h5 class="mt-3">نموذج إنشاء القيد</h5>
            <p class="text-muted">سيتم إضافة النموذج قريباً</p>
            <a href="{{ route('journals.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-right me-1"></i>العودة للقائمة
            </a>
        </div>
    </div>
@endsection