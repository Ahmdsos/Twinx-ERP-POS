@extends('layouts.app')

@section('title', __('Chart of Accounts'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-heading mb-1">{{ __('Chart of Accounts') }}</h4>
            <div class="text-muted small">{{ __('View accounts list') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.tree') }}" class="btn btn-glass-outline">
                <i class="bi bi-diagram-3 me-1"></i> {{ __('View Tree') }}
            </a>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary shadow-lg fw-bold px-4 py-2">
                <i class="bi bi-plus-lg me-1"></i> {{ __('New Account') }}
            </a>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table align-middle text-body mb-0 custom-table">
                <thead>
                    <tr>
                        <th class="px-4 py-4 text-secondary-50 fw-normal">{{ __('Account Number') }}</th>
                        <th class="py-4 text-secondary-50 fw-normal">{{ __('Account Name') }}</th>
                        <th class="py-4 text-secondary-50 fw-normal">{{ __('Account Type') }}</th>
                        <th class="py-4 text-secondary-50 fw-normal">{{ __('Account Nature') }}</th>
                        <th class="py-4 text-secondary-50 fw-normal">{{ __('Current Balance') }}</th>
                        <th class="py-4 text-center text-secondary-50 fw-normal">{{ __('Status') }}</th>
                        <th class="px-4 py-4 text-end text-secondary-50 fw-normal">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr class="table-row-hover">
                            <td class="px-4 py-3 font-monospace text-info fs-5">{{ $account->code }}</td>
                            <td class="py-3">
                                <div class="fw-bold fs-5">{{ $account->display_name }}</div>
                                @if($account->parent)
                                    <div class="small text-muted">{{ __('Under') }}:
                                        {{ $account->parent->display_name ?? $account->parent->name }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span
                                    class="badge bg-surface bg-opacity-10 text-body fw-normal px-3 py-1 rounded-pill border border-secondary border-opacity-10 border-opacity-10">
                                    {{ $account->type->label() }}
                                </span>
                            </td>
                            <td class="py-3 text-muted small">
                                {{ $account->type->debitIncreases() ? __('Debit') : __('Credit') }}
                            </td>
                            <td class="py-3 fw-bold fs-5 {{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($account->balance, 2) }}
                            </td>
                            <td class="text-center py-3">
                                @if($account->is_active)
                                    <div
                                        class="d-inline-flex align-items-center text-success bg-success bg-opacity-10 px-3 py-1 rounded-pill border border-success border-opacity-10">
                                        <span class="small fw-bold">{{ __('Active') }}</span>
                                    </div>
                                @else
                                    <div
                                        class="d-inline-flex align-items-center text-danger bg-danger bg-opacity-10 px-3 py-1 rounded-pill border border-danger border-opacity-10">
                                        <span class="small fw-bold">{{ __('Inactive') }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 text-end py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('accounts.show', $account) }}"
                                        class="btn btn-sm btn-glass text-info shadow-sm" title="{{ __('Account Statement') }}">
                                        <i class="bi bi-eye fs-6"></i>
                                    </a>
                                    <a href="{{ route('accounts.edit', $account) }}"
                                        class="btn btn-sm btn-glass text-warning shadow-sm" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil fs-6"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center py-5 opacity-50">
                                    <i class="bi bi-safe2 display-1 mb-4"></i>
                                    <h4 class="text-heading-50">{{ __('No accounts found') }}</h4>
                                    <a href="{{ route('accounts.create') }}"
                                        class="btn btn-outline-light px-4 py-2 rounded-pill">{{ __('Add New Account') }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 border-opacity-10 py-4">
            {{ $accounts->links('partials.pagination') }}
        </div>
    </div>

    <style>
        .custom-table thead th {
            background-color: var(--table-head-bg);
            color: var(--table-head-color);
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--table-border);
        }

        .table-row-hover {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--table-border);
        }

        .table-row-hover:hover {
            background-color: var(--table-row-hover);
            transform: translateY(-1px);
            box-shadow: var(--card-shadow);
        }

        .table-row-hover td {
            border: none;
        }

        .btn-glass {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-glass:hover {
            background: var(--btn-glass-hover-bg);
            border-color: var(--glass-hover-border);
            transform: scale(1.05);
        }

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            color: var(--btn-glass-color);
            transition: all 0.2s;
        }

        .btn-glass-outline:hover {
            background: var(--btn-glass-hover-bg);
            border-color: var(--glass-hover-border);
        }
    </style>
@endsection