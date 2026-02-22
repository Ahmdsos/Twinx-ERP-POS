@props(['headers' => []])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table table-hover align-middle mb-0']) }}>
        <thead class="bg-surface-secondary bg-opacity-10">
            <tr>
                @foreach($headers as $header)
                    <th class="px-4 py-3 fw-semibold text-uppercase small text-secondary">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>