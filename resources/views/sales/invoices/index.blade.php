@extends('layouts.app')

@section('title', 'فواتير البيع - Twinx ERP')
@section('page-title', 'فواتير البيع')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>فواتير البيع</h5>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-receipt fs-1 text-muted"></i>
            <h5 class="mt-3">قسم فواتير البيع</h5>
            <p class="text-muted">يتم إنشاء الفواتير تلقائياً من أوامر التسليم</p>
        </div>
    </div>
@endsection