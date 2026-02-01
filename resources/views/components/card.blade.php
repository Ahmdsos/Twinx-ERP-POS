@props(['title' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm bg-white']) }}>
    @if($title || $actions)
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            @if($title)
                <h5 class="fw-bold mb-0 text-dark">{{ $title }}</h5>
            @endif

            @if($actions)
                <div class="card-actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="card-body px-4 pb-4">
        {{ $slot }}
    </div>
</div>