@extends('layouts.app')

@section('title', __('Create Account'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-heading mb-0">{{ __('Create Account') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounts.index') }}" class="btn btn-glass-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> {{ __('Save Account') }}
                        </button>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">{{ __('Account Number') }} (Code) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="code"
                                class="form-control bg-transparent text-body border-secondary font-monospace"
                                placeholder="مثال: 1101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">{{ __('Account Name') }} (English) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-transparent text-body border-secondary"
                                placeholder="Example: Sales" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-info">{{ __('Account Name') }} ({{ __('Arabic') }}) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control bg-transparent text-body border-info"
                                placeholder="مثال: المبيعات" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-300">{{ __('Account Type') }} <span
                                    class="text-danger">*</span></label>
                            <select name="type" class="form-select bg-transparent text-body border-secondary" required>
                                <option value="">-- {{ __('Select Account Type') }} --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">{{ __('Parent Account') }}</label>
                            <select name="parent_id" class="form-select bg-transparent text-body border-secondary">
                                <option value="">-- {{ __('Root Account') }} --</option>
                                @foreach($parentAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">{{ __('Leave empty if root account') }}</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-gray-300">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control bg-transparent text-body border-secondary"
                                rows="3"></textarea>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                    checked>
                                <label class="form-check-label text-body" for="isActive">{{ __('Active Account') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .btn-glass-outline {
                background: var(--btn-glass-bg);
                border: 1px solid var(--btn-glass-border);
                color: var(--btn-glass-color);
            }

            .form-control:focus,
            .form-select:focus {
                background-color: var(--input-bg);
                border-color: var(--input-focus-border);
                box-shadow: var(--input-focus-shadow);
                color: var(--input-color);
            }

            option {
                background-color: var(--dropdown-bg);
                color: var(--body-color);
            }
        </style>
    @endpush
@endsection