@extends('layouts.app')

@section('title', 'الموردين - Twinx ERP')
@section('page-title', 'إدارة الموردين')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الموردين</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-truck me-2"></i>الموردين</h5>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>مورد جديد
            </a>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-truck fs-1 text-muted"></i>
            <h5 class="mt-3">قسم الموردين</h5>
            <p class="text-muted">سيتم إضافة قائمة الموردين قريباً</p>
        </div>
    </div>
@endsection