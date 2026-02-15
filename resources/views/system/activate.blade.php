@extends('layouts.app')

@section('title', __('Activate System'))

@section('content')
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="glass-panel p-5 shadow-neon text-center" style="max-width: 600px; width: 100%;">
            <div class="icon-box bg-gradient-purple shadow-neon mx-auto mb-4" style="width: 80px; height: 80px;">
                <i class="bi bi-shield-lock-fill fs-1 text-body"></i>
            </div>

            <h2 class="fw-bold text-heading mb-3 tracking-wide">{{ __('Activate Twinx ERP') }}</h2>
            <p class="text-secondary mb-5">{{ __('System not activated message') }}</p>

            <div class="bg-slate-900 bg-opacity-50 p-4 rounded-4 border border-secondary border-opacity-10-10 mb-5">
                <h3 class="text-heading fw-bold tracking-tight mb-3">{{ __('Activate System') }}</h3>

                @if(isset($details) && $details)
                    <div class="mb-4 p-3 rounded-lg bg-surface bg-opacity-10 border border-secondary border-opacity-10 border-opacity-10">
                        <p class="mb-1 text-secondary small">{{ __('Current license registered to') }}</p>
                        <p class="mb-2 text-body fw-bold">{{ $details['client_name'] }}</p>

                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-{{ $details['is_expired'] ? 'danger' : 'warning' }}"></i>
                            <span class="small {{ $details['is_expired'] ? 'text-danger fw-bold' : 'text-secondary' }}">
                                @if($details['is_expired'])
                                    {{ __('License expired') }}
                                @else
                                    {{ __('Days left', ['days' => $details['days_left']]) }}
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

                <label class="text-purple-400 x-small fw-bold text-uppercase d-block mb-2">{{ __('Machine ID') }}</label>
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <code class="text-body fs-4 tracking-widest">{{ $machineId }}</code>
                    <button class="btn btn-sm btn-dark-glass" onclick="copyToClipboard('{{ $machineId }}')"
                        title="{{ __('Copy') }}">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('system.activate.submit') }}" method="POST">
                @csrf
                <div class="mb-4 text-start">
                    <label class="form-label text-gray-300 small">{{ __('License Key') }}</label>
                    <textarea name="license_key" class="form-control form-control-dark font-monospace small" rows="5"
                        required placeholder="{{ __('Paste code here') }}"></textarea>
                </div>

                <button type="submit" class="btn btn-action-purple w-100 py-3 fw-bold fs-5">
                    <i class="bi bi-check2-circle me-2"></i> {{ __('Activate System Now') }}
                </button>
            </form>

            <p class="mt-4 text-secondary x-small">Twinx ERP &copy; {{ date('Y') }} - {{ __('All rights reserved') }}</p>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            // You could add a toast here
            alert('{{ __('Copied') }}: ' + text);
        }
    </script>

    <style>
        /* Reuse existing styles from index but scoped to activation */
        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
        }

        .bg-gradient-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--btn-glass-border);
            border-radius: 24px;
        }

        .form-control-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
            padding: 1rem;
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            border: none;
            color: var(--text-primary);
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.3);
            border-radius: 12px;
        }

        .btn-dark-glass {
            background: var(--btn-glass-bg);
            border: 1px solid var(--btn-glass-border);
            color: var(--text-secondary);
        }
    </style>
@endsection