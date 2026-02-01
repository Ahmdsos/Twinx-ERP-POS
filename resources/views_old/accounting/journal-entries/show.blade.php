@extends('layouts.app')

@section('title', $journalEntry->entry_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-right"></i>
            </a>
            <div>
                <h1 class="h3 mb-0">{{ $journalEntry->entry_number }}</h1>
                <p class="text-muted mb-0">
                    @switch($journalEntry->status->value)
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
                    <span class="ms-2">{{ $journalEntry->entry_date->format('Y-m-d') }}</span>
                </p>
            </div>
        </div>
        <div class="btn-group">
            @if($journalEntry->canBePosted())
                <form action="{{ route('journal-entries.post', $journalEntry) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('هل أنت متأكد من ترحيل هذا القيد؟');">
                        <i class="bi bi-check-circle me-1"></i>
                        ترحيل
                    </button>
                </form>
            @endif
            @if($journalEntry->canBeReversed())
                <form action="{{ route('journal-entries.reverse', $journalEntry) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من عكس هذا القيد؟');">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        عكس
                    </button>
                </form>
            @endif
            @if($journalEntry->isEditable())
                <a href="{{ route('journal-entries.edit', $journalEntry) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>
                    تعديل
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Entry Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">بيانات القيد</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" width="40%">رقم القيد</td>
                            <td class="fw-bold">{{ $journalEntry->entry_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">التاريخ</td>
                            <td>{{ $journalEntry->entry_date->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">المرجع</td>
                            <td>{{ $journalEntry->reference ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الوصف</td>
                            <td>{{ $journalEntry->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الحالة</td>
                            <td>
                                @switch($journalEntry->status->value)
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
                        </tr>
                        @if($journalEntry->posted_at)
                            <tr>
                                <td class="text-muted">تاريخ الترحيل</td>
                                <td>{{ $journalEntry->posted_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">رحّل بواسطة</td>
                                <td>{{ $journalEntry->postedByUser?->name ?? '-' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Totals Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">ملخص</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <div class="text-success small">المدين</div>
                                <div class="h4 mb-0">{{ number_format($journalEntry->total_debit, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <div class="text-danger small">الدائن</div>
                                <div class="h4 mb-0">{{ number_format($journalEntry->total_credit, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        @if($journalEntry->isBalanced())
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> متوازن</span>
                        @else
                            <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i> غير متوازن</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Entry Lines -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">سطور القيد ({{ $journalEntry->lines->count() }} سطر)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>الحساب</th>
                                <th>البيان</th>
                                <th class="text-end">مدين</th>
                                <th class="text-end">دائن</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journalEntry->lines as $line)
                                <tr>
                                    <td>
                                        <a href="{{ route('accounts.show', $line->account) }}" class="text-decoration-none">
                                            <span class="text-muted">{{ $line->account->code }}</span>
                                            {{ $line->account->name }}
                                        </a>
                                    </td>
                                    <td>{{ $line->description ?? '-' }}</td>
                                    <td class="text-end">
                                        @if($line->debit > 0)
                                            <span class="text-success fw-bold">{{ number_format($line->debit, 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($line->credit > 0)
                                            <span class="text-danger fw-bold">{{ number_format($line->credit, 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">الإجمالي:</td>
                                <td class="text-end text-success">{{ number_format($journalEntry->total_debit, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($journalEntry->total_credit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
