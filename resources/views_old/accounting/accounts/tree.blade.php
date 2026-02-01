@extends('layouts.app')

@section('title', 'دليل الحسابات - شجرة')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">دليل الحسابات</h1>
                <p class="text-muted mb-0">عرض شجري للحسابات</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i>
                    عرض جدول
                </a>
                <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    حساب جديد
                </a>
            </div>
        </div>

        <!-- Account Type Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            @foreach($accountsByType as $type => $accounts)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" href="#type-{{ $type }}"
                        role="tab">
                        {{ $typeLabels[$type] ?? $type }}
                        <span class="badge bg-secondary ms-1">{{ count($accounts) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            @foreach($accountsByType as $type => $accounts)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="type-{{ $type }}" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            @if(count($accounts) > 0)
                                <div class="account-tree">
                                    @include('accounting.accounts.partials.tree-node', ['accounts' => $accounts, 'level' => 0])
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    لا توجد حسابات في هذا النوع
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .account-tree {
            font-size: 0.95rem;
        }

        .tree-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .tree-item:last-child {
            border-bottom: none;
        }

        .tree-toggle {
            cursor: pointer;
            user-select: none;
            color: #6c757d;
        }

        .tree-toggle:hover {
            color: #0d6efd;
        }

        .tree-children {
            margin-right: 1.5rem;
            border-right: 2px solid #e9ecef;
            padding-right: 1rem;
        }

        .tree-children.collapsed {
            display: none;
        }

        .account-code {
            font-family: monospace;
            background: #f8f9fa;
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .account-balance {
            font-weight: 600;
        }

        .account-balance.positive {
            color: #198754;
        }

        .account-balance.negative {
            color: #dc3545;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle tree nodes
            document.querySelectorAll('.tree-toggle').forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    const item = this.closest('.tree-item');
                    const children = item.querySelector('.tree-children');
                    const icon = this.querySelector('i');

                    if (children) {
                        children.classList.toggle('collapsed');
                        if (children.classList.contains('collapsed')) {
                            icon.classList.remove('bi-chevron-down');
                            icon.classList.add('bi-chevron-left');
                        } else {
                            icon.classList.remove('bi-chevron-left');
                            icon.classList.add('bi-chevron-down');
                        }
                    }
                });
            });

            // Expand All / Collapse All buttons
            document.querySelectorAll('.expand-all').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const container = this.closest('.tab-pane');
                    container.querySelectorAll('.tree-children').forEach(function (children) {
                        children.classList.remove('collapsed');
                    });
                    container.querySelectorAll('.tree-toggle i').forEach(function (icon) {
                        icon.classList.remove('bi-chevron-left');
                        icon.classList.add('bi-chevron-down');
                    });
                });
            });

            document.querySelectorAll('.collapse-all').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const container = this.closest('.tab-pane');
                    container.querySelectorAll('.tree-children').forEach(function (children) {
                        children.classList.add('collapsed');
                    });
                    container.querySelectorAll('.tree-toggle i').forEach(function (icon) {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-left');
                    });
                });
            });
        });
    </script>
@endsection