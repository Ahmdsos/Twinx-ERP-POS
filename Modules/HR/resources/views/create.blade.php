@extends('layouts.app')

@section('title', 'إضافة موظف')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>إضافة موظف</h5>
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">إضافة موظف</h4>
                    <p class="text-muted">هذه الصفحة قيد التطوير وستكون متاحة قريباً</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection