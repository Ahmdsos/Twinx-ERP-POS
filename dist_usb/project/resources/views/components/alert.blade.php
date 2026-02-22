@props(['type' => 'info', 'dismissible' => true])

@php
    $icons = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-octagon-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'info' => 'bi-info-circle-fill',
    ];
    $icon = $icons[$type] ?? 'bi-info-circle-fill';

    $colors = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];
    $color = $colors[$type] ?? 'alert-info';
@endphp

<div {{ $attributes->merge(['class' => "alert $color d-flex align-items-center role='alert'"]) }}>
    <i class="bi {{ $icon }} me-2 fs-5"></i>
    <div>
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>