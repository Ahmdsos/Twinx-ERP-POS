@extends('layouts.app')

@section('title', 'دليل الحسابات الشجري')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-heading mb-1">{{ __('Chart of Accounts') }}</h4>
            <div class="text-muted small">عرض شجري للهيكل المحاسبي</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.index') }}" class="btn btn-glass-outline">
                <i class="bi bi-list-ul me-1"></i>{{ __('List View') }}</a>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary shadow-lg fw-bold px-4 py-2">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Account') }}</a>
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="alert alert-info bg-opacity-10 border-info border-opacity-25 text-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            يمكنك التعديل أو الحذف بالضغط على أيقونات الحساب. الحسابات الرئيسية (Header) لا يمكن الترحيل عليها.
        </div>

        <div class="accordion" id="accountsAccordion">
            @foreach($accountsByType as $typeKey => $accounts)
                <div class="accordion-item bg-transparent border-0 mb-3">
                    <h2 class="accordion-header" id="heading{{ $typeKey }}">
                        <button class="accordion-button collapsed glass-accordion-btn text-white fw-bold rounded-3"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $typeKey }}">
                            <span class="d-flex w-100 justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-folder2-open me-2 text-warning"></i>
                                    {{ $typeLabels[$typeKey] }}
                                </span>
                                <span class="badge bg-surface bg-opacity-10 text-body rounded-pill ms-3">
                                    {{ count($accounts) }} جذر
                                </span>
                            </span>
                        </button>
                    </h2>
                    <div id="collapse{{ $typeKey }}" class="accordion-collapse collapse show"
                        data-bs-parent="#accountsAccordion">
                        <div class="accordion-body text-body pt-2 ps-4">
                            @if(count($accounts) > 0)
                                <ul class="list-unstyled account-tree">
                                    @foreach($accounts as $account)
                                        @include('accounting.accounts.tree-item', ['account' => $account])
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted small py-2 fst-italic">لا توجد حسابات مسجلة لهذا النوع</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        

        .glass-accordion-btn {
            background: var(--btn-glass-bg); !important;
            border: 1px solid var(--btn-glass-border);
            color: var(--text-primary); !important;
            box-shadow: none !important;
        }

        .glass-accordion-btn:not(.collapsed) {
            background: rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        .account-tree {
            border-right: 1px dashed rgba(255, 255, 255, 0.2);
        }

        .account-tree .tree-item {
            position: relative;
            padding-right: 20px;
        }

        .account-tree .tree-item::before {
            content: '';
            position: absolute;
            right: 0;
            top: 18px;
            width: 15px;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }
    </style>
@endsection