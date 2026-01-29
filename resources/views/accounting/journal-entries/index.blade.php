@extends('layouts.app')

@section('title', 'قيود اليومية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">قيود اليومية</h1>
            <p class="text-muted mb-0">إدارة القيود المحاسبية</p>
        </div>
        <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            قيد جديد
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                            <i class="bi bi-journal-text text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">إجمالي القيود</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3">
                            <i class="bi bi-pencil text-warning fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">مسودة</h6>
                            <h3 class="mb-0">{{ number_format($stats['draft']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">مرحّل</h6>
                            <h3 class="mb-0">{{ number_format($stats['posted']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                            <i class="bi bi-currency-dollar text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">إجمالي المدين</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_debit'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" 
                        placeholder="بحث برقم القيد أو المرجع..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>مرحّل</option>
                        <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>معكوس</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" 
                        placeholder="من تاريخ" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" 
                        placeholder="إلى تاريخ" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search me-1"></i>
                        بحث
                    </button>
                    <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>رقم القيد</th>
                        <th>التاريخ</th>
                        <th>المرجع</th>
                        <th>الوصف</th>
                        <th class="text-end">المدين</th>
                        <th class="text-end">الدائن</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td>
                                <a href="{{ route('journal-entries.show', $entry) }}" class="fw-bold text-decoration-none">
                                    {{ $entry->entry_number }}
                                </a>
                            </td>
                            <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                            <td>{{ $entry->reference ?? '-' }}</td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                    {{ $entry->description ?? '-' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($entry->total_debit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->total_credit, 2) }}</td>
                            <td class="text-center">
                                @switch($entry->status->value)
                                    @case('draft')
                                        <span class="badge bg-warning">مسودة</span>
                                        @break
                                    @case('posted')
                                        <span class="badge bg-success">مرحّل</span>
                                        @break
                                    @case('reversed')
                                        <span class="badge bg-secondary">معكوس</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('journal-entries.show', $entry) }}" class="btn btn-outline-primary" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($entry->isEditable())
                                        <a href="{{ route('journal-entries.edit', $entry) }}" class="btn btn-outline-secondary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد قيود يومية
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="card-footer">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
