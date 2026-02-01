@props(['headers' => []])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table table-hover align-middle mb-0']) }}>
        <thead class="bg-light text-secondary">
            <tr>
                @foreach($headers as $header)
                    <th class="px-4 py-3 fw-semibold text-uppercase small">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>