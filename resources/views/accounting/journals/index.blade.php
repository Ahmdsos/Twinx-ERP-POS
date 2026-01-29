@extends('layouts.app')

@section('title', 'القيود اليومية - Twinx ERP')
@section('page-title', 'القيود اليومية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">القيود اليومية</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-book me-2"></i>القيود اليومية</h5>
            <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>قيد جديد
            </a>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-text fs-1 text-muted"></i>
            <h5 class="mt-3">قسم القيود اليومية</h5>
            <p class="text-muted">سيتم إضافة عرض القيود قريباً</p>
            <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>إنشاء قيد جديد
            </a>
        </div>
    </div>
@endsection