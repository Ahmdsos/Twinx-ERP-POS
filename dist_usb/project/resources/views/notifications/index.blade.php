@extends('layouts.app')

@section('title', __('Notifications'))

@section('header', __('Notification Center'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-heading mb-1">{{ __('Notifications & Alerts') }}</h4>
                    <p class="text-body-50 mb-0">{{ __('Full log of all system events and alerts') }}</p>
                </div>

                @if($notifications->count() > 0 && auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-check-all me-1"></i> {{ __('Mark all as read') }}
                        </button>
                    </form>
                @endif
            </div>

            <div class="glass-card p-0 overflow-hidden">
                @forelse($notifications as $notification)
                    <div
                        class="p-4 border-bottom border-secondary border-opacity-10 {{ $notification->read_at ? 'bg-transparent' : 'bg-primary bg-opacity-10' }}">
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
                                        <h6 class="mb-1 text-heading fw-bold">
                                            {{ $notification->data['title'] ?? __('New Notification') }}
                                            @if(!$notification->read_at)
                                                <span class="badge bg-primary ms-1 small">{{ __('New') }}</span>
                                            @endif
                                        </h6>
                                        <p class="mb-2 text-secondary">{{ $notification->data['description'] ?? '' }}</p>
                                    </div>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>

                                <div class="d-flex gap-2">
                                    @if(isset($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-light">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('View Details') }}
                                        </a>
                                    @endif

                                    @if(!$notification->read_at)
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm btn-link text-muted text-decoration-none p-0 ms-2">
                                                {{ __('Mark as read') }}
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
                        <h5 class="text-heading">{{ __('No notifications') }}</h5>
                        <p class="text-body-50">{{ __('All your notifications will appear here') }}</p>
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