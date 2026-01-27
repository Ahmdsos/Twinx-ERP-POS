@extends('layouts.app')

@section('title', 'أوامر البيع - Twinx ERP')
@section('page-title', 'أوامر البيع')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-cart me-2"></i>أوامر البيع</h5>
            <a href="{{ route('sales-orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>أمر بيع جديد
            </a>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-cart fs-1 text-muted"></i>
            <h5 class="mt-3">قسم أوامر البيع</h5>
            <p class="text-muted">سيتم إضافة قائمة أوامر البيع قريباً</p>
        </div>
    </div>
@endsection