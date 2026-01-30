@extends('layouts.app')

@section('title', 'الإشعارات - Twinx ERP')
@section('page-title', 'الإشعارات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإشعارات</li>
@endsection

@section('actions')
    <a href="{{ route('notifications.settings') }}" class="btn btn-outline-primary">
        <i class="bi bi-gear me-1"></i>
        الإعدادات
    </a>
    <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">
            <i class="bi bi-check2-all me-1"></i>
            تحديد الكل كمقروء
        </button>
    </form>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @if(isset($notifications) && $notifications->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                            <div class="me-auto">
                                <div class="d-flex align-items-center">
                                    @if($notification->type === 'order')
                                        <i class="bi bi-cart text-primary me-2 fs-4"></i>
                                    @elseif($notification->type === 'payment')
                                        <i class="bi bi-cash text-success me-2 fs-4"></i>
                                    @elseif($notification->type === 'stock')
                                        <i class="bi bi-box text-warning me-2 fs-4"></i>
                                    @else
                                        <i class="bi bi-bell text-secondary me-2 fs-4"></i>
                                    @endif
                                    <div>
                                        <h6 class="mb-1">{{ $notification->title }}</h6>
                                        <p class="text-muted mb-0 small">{{ $notification->message }}</p>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 5rem;"></i>
                    <h4 class="mt-3 text-muted">لا توجد إشعارات</h4>
                    <p class="text-muted">ستظهر هنا الإشعارات الجديدة عند وصولها</p>
                </div>
            @endif
        </div>
    </div>
@endsection