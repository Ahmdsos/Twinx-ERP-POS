<li class="mb-2 tree-item">
    <div class="d-flex align-items-center justify-content-between p-2 rounded-2 hover-bg-glass group-action-container">
        <div class="d-flex align-items-center gap-2">
            <!-- Icon based on type -->
            @if(count($account['children']) > 0)
                <i class="bi bi-folder-fill text-warning opacity-75"></i>
            @else
                <i class="bi bi-file-earmark-text text-info opacity-75"></i>
            @endif

            <!-- Code & Name -->
            <span class="font-monospace text-warning small">{{ $account['code'] }}</span>
            <span class="fw-bold {{ count($account['children']) > 0 ? 'text-white' : 'text-white-50' }}">
                {{ $account['name'] }}
            </span>

            <!-- Tags -->
            @if(!$account['is_active'])
                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 ms-2"
                    style="font-size: 0.65rem;">موقف</span>
            @endif
        </div>

        <!-- Actions (Only visible on hover) -->
        <div class="d-flex gap-1 opacity-50 group-actions">
            <a href="{{ route('accounts.show', $account['id']) }}" class="btn btn-sm btn-icon-glass text-info"
                title="كشف حساب">
                <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('accounts.edit', $account['id']) }}" class="btn btn-sm btn-icon-glass text-warning"
                title="تعديل">
                <i class="bi bi-pencil"></i>
            </a>
            @if(count($account['children']) == 0 && $account['balance'] == 0)
                <form action="{{ route('accounts.destroy', $account['id']) }}" method="POST"
                    onsubmit="return confirm('حذف الحساب؟')" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon-glass text-danger" title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(count($account['children']) > 0)
        <ul class="list-unstyled pe-4 mt-1 border-end border-white border-opacity-10 account-tree">
            @foreach($account['children'] as $child)
                @include('accounting.accounts.tree-item', ['account' => $child])
            @endforeach
        </ul>
    @endif
</li>

<style>
    .hover-bg-glass:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .hover-bg-glass:hover .group-actions {
        opacity: 1;
    }

    .btn-icon-glass {
        padding: 0.15rem 0.4rem;
        font-size: 0.8rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        color: white;
    }

    .btn-icon-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.2);
    }
</style>