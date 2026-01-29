@extends('layouts.app')

@section('title', $account->name . ' - دفتر الأستاذ')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-right"></i>
            </a>
            <div>
                <h1 class="h3 mb-0">{{ $account->code }} - {{ $account->name }}</h1>
                <p class="text-muted mb-0">
                    <span class="badge bg-primary">{{ $account->type->label() }}</span>
                    @if($account->description)
                        <span class="ms-2">{{ $account->description }}</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>
            تعديل
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search me-1"></i>
                        عرض
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Summary Cards -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">ملخص الفترة</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">الرصيد الافتتاحي</div>
                        <div class="h5 mb-0 {{ $openingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format(abs($openingBalance), 2) }}
                            {{ $openingBalance < 0 ? '(دائن)' : '(مدين)' }}
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="text-muted small">إجمالي المدين</div>
                        <div class="h5 mb-0 text-success">{{ number_format($totalDebit, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">إجمالي الدائن</div>
                        <div class="h5 mb-0 text-danger">{{ number_format($totalCredit, 2) }}</div>
                    </div>
                    <hr>
                    <div>
                        <div class="text-muted small">الرصيد الختامي</div>
                        <div class="h4 mb-0 {{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format(abs($closingBalance), 2) }}
                            {{ $closingBalance < 0 ? '(دائن)' : '(مدين)' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">كشف الحساب</h6>
                    <span class="badge bg-secondary">{{ count($ledgerEntries) }} حركة</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>التاريخ</th>
                                <th>رقم القيد</th>
                                <th>البيان</th>
                                <th class="text-end">مدين</th>
                                <th class="text-end">دائن</th>
                                <th class="text-end">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Opening Balance Row -->
                            <tr class="table-light">
                                <td>{{ $startDate }}</td>
                                <td>-</td>
                                <td><em>رصيد افتتاحي</em></td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end fw-bold">{{ number_format($openingBalance, 2) }}</td>
                            </tr>

                            @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td>{{ $entry['date']->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('journal-entries.show', $entry['journal_entry_id']) }}" class="text-decoration-none">
                                            {{ $entry['entry_number'] }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $entry['description'] ?? '-' }}
                                        @if($entry['reference'])
                                            <span class="text-muted small">({{ $entry['reference'] }})</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($entry['debit'] > 0)
                                            <span class="text-success">{{ number_format($entry['debit'], 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($entry['credit'] > 0)
                                            <span class="text-danger">{{ number_format($entry['credit'], 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $entry['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($entry['balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        لا توجد حركات في هذه الفترة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">الإجمالي:</td>
                                <td class="text-end text-success">{{ number_format($totalDebit, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($totalCredit, 2) }}</td>
                                <td class="text-end {{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($closingBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
