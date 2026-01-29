@foreach($accounts as $account)
    <div class="tree-item">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                @if(isset($account['children']) && count($account['children']) > 0)
                    <span class="tree-toggle me-2">
                        <i class="bi bi-chevron-down"></i>
                    </span>
                @else
                    <span class="me-2" style="width: 16px;"></span>
                @endif

                <span class="account-code me-2">{{ $account['code'] }}</span>
                <a href="{{ route('accounts.show', $account['id']) }}" class="text-decoration-none">
                    {{ $account['name'] }}
                </a>

                @if(!$account['is_active'])
                    <span class="badge bg-danger ms-2">معطل</span>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="account-balance {{ $account['balance'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format(abs($account['balance']), 2) }}
                </span>

                <div class="btn-group btn-group-sm">
                    <a href="{{ route('accounts.show', $account['id']) }}" class="btn btn-sm btn-outline-secondary"
                        title="كشف الحساب">
                        <i class="bi bi-journal-text"></i>
                    </a>
                    <a href="{{ route('accounts.edit', $account['id']) }}" class="btn btn-sm btn-outline-primary"
                        title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
        </div>

        @if(isset($account['children']) && count($account['children']) > 0)
            <div class="tree-children mt-2">
                @include('accounting.accounts.partials.tree-node', ['accounts' => $account['children'], 'level' => $level + 1])
            </div>
        @endif
    </div>
@endforeach