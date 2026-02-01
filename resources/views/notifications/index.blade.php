@extends('layouts.app')

@section('title', 'الإشعارات')

@section('header', 'مركز الإشعارات')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-white mb-1">الإشعارات والتنبيهات</h4>
                    <p class="text-white-50 mb-0">سجل كامل بجميع أحداث النظام والتنبيهات</p>
                </div>

                @if($notifications->count() > 0 && auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-check-all me-1"></i> تحديد الكل كمقروء
                        </button>
                    </form>
                @endif
            </div>

            <div class="glass-card p-0 overflow-hidden">
                @forelse($notifications as $notification)
                    <div
                        class="p-4 border-bottom border-secondary border-opacity-10 {{ $notification->read_at ? 'bg-transparent' : 'bg-primary bg-opacity-05' }}">
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0 mt-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px; background-color: rgba(var(--bs-{{ $notification->data['type'] ?? 'primary' }}-rgb), 0.15);">
                                    <i
                                        class="bi {{ $notification->data['icon'] ?? 'bi-bell' }} fs-4 text-{{ $notification->data['type'] ?? 'primary' }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 text-white fw-bold">
                                            {{ $notification->data['title'] ?? 'إشعار جديد' }}
                                            @if(!$notification->read_at)
                                                <span class="badge bg-primary ms-1 small">جديد</span>
                                            @endif
                                        </h6>
                                        <p class="mb-2 text-secondary">{{ $notification->data['description'] ?? '' }}</p>
                                    </div>
                                    <small class="text-white-50">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>

                                <div class="d-flex gap-2">
                                    @if(isset($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-light">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> عرض التفاصيل
                                        </a>
                                    @endif

                                    @if(!$notification->read_at)
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm btn-link text-white-50 text-decoration-none p-0 ms-2">
                                                تحديد كمقروء
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-5 text-center">
                        <i class="bi bi-bell-slash fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                        <h5 class="text-white">لا توجد إشعارات</h5>
                        <p class="text-white-50">جميع إشعاراتك ستظهر هنا</p>
                    </div>
                @endforelse

                @if($notifications->hasPages())
                    <div class="p-3 border-top border-secondary border-opacity-10">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection