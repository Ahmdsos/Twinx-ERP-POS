@extends('layouts.app')

@section('title', 'أوامر الشراء - Twinx ERP')
@section('page-title', 'أوامر الشراء')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-bag me-2"></i>أوامر الشراء</h5>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>أمر شراء جديد
            </a>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-bag fs-1 text-muted"></i>
            <h5 class="mt-3">قسم أوامر الشراء</h5>
            <p class="text-muted">سيتم إضافة قائمة أوامر الشراء قريباً</p>
        </div>
    </div>
@endsection