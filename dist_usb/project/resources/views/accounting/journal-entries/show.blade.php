@extends('layouts.app')

@section('title', 'تفاصيل قيد اليومية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-heading mb-1">قيد يومية <span class="text-info font-monospace">#{{ $journalEntry->entry_number }}</span></h4>
        <div class="text-muted small">
            <i class="bi bi-calendar-event me-1"></i> {{ $journalEntry->entry_date->format('Y-m-d') }}
            @if($journalEntry->reference)
                <span class="mx-2">|</span> تفاصيل المرجع: <span class="text-body">{{ $journalEntry->reference }}</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('journal-entries.index') }}" class="btn btn-glass-outline">عودة للقائمة</a>
        <button onclick="window.print()" class="btn btn-glass-outline">
            <i class="bi bi-printer me-2"></i>{{ __('Print') }}</button>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-3 p-4">
        <div>
            <h5 class="text-heading mb-1 fw-bold">{{ $journalEntry->description }}</h5>
            <div class="text-muted small">الوصف / البيان</div>
        </div>
        <div>
             @if($journalEntry->status == \Modules\Accounting\Enums\JournalStatus::POSTED)
                <div class="d-inline-flex align-items-center text-success bg-success bg-opacity-10 px-4 py-2 rounded-pill border border-success border-opacity-10">
                    <i class="bi bi-check-circle-fill me-2"></i> <span class="fw-bold">مرحل (Posted)</span>
                </div>
            @elseif($journalEntry->status == \Modules\Accounting\Enums\JournalStatus::DRAFT)
                <div class="d-inline-flex align-items-center text-warning bg-warning bg-opacity-10 px-4 py-2 rounded-pill border border-warning border-opacity-10">
                    <i class="bi bi-hourglass-split me-2"></i> <span class="fw-bold">مسودة (Draft)</span>
                </div>
            @else
                <span class="badge bg-secondary bg-opacity-10 text-white border border-secondary border-opacity-10 border-opacity-25 px-3 py-2 rounded-pill">
                    {{ $journalEntry->status }}
                </span>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle text-body mb-0 custom-table">
            <thead>
                <tr>
                    <th class="px-4 py-4 text-secondary-50 fw-normal">{{ __('Account Number') }}</th>
                    <th class="py-4 text-secondary-50 fw-normal">{{ __('Account Name') }}</th>
                    <th class="py-4 text-secondary-50 fw-normal">البيان (سطر)</th>
                    <th class="py-4 text-secondary-50 fw-normal text-end">مدين (Debit)</th>
                    <th class="px-4 py-4 text-secondary-50 fw-normal text-end">دائن (Credit)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journalEntry->lines as $line)
                <tr class="table-row-hover">
                    <td class="px-4 py-3 font-monospace text-info opacity-75 fs-5">
                        {{ $line->account->code }}
                    </td>
                    <td class="py-3 fw-medium fs-5">{{ $line->account->name }}</td>
                    <td class="py-3 text-muted small pe-5">{{ $line->description ?? '-' }}</td>
                    <td class="py-3 text-end font-monospace fs-5 {{ $line->debit > 0 ? 'text-body fw-bold' : 'text-muted opacity-25' }}">
                        {{ number_format($line->debit, 2) }}
                    </td>
                    <td class="px-4 py-3 text-end font-monospace fs-5 {{ $line->credit > 0 ? 'text-body fw-bold' : 'text-muted opacity-25' }}">
                        {{ number_format($line->credit, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-surface bg-opacity-5">
                <tr>
                    <td colspan="3" class="px-4 py-4 fw-bold text-end text-info border-0 fs-5">الإجمالي:</td>
                    <td class="py-4 text-end fw-bold text-info border-0 fs-4 font-monospace">{{ number_format($journalEntry->total_debit, 2) }}</td>
                    <td class="px-4 py-4 text-end fw-bold text-info border-0 fs-4 font-monospace">{{ number_format($journalEntry->total_credit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="glass-card p-4 h-100">
            <h6 class="text-heading-50 border-bottom border-secondary border-opacity-10 border-opacity-10 pb-2 mb-3">معلومات إضافية</h6>
            <div class="row g-3">
                 <div class="col-6">
                    <div class="text-muted small mb-1">سجل بواسطة</div>
                    <div class="text-body fw-bold">{{ $journalEntry->creator->name ?? 'System' }}</div>
                 </div>
                 <div class="col-6">
                    <div class="text-muted small mb-1">{{ __('Created At') }}</div>
                    <div class="text-body font-monospace">{{ $journalEntry->created_at->format('Y-m-d H:i') }}</div>
                 </div>
                 @if($journalEntry->source_type)
                     <div class="col-12 border-top border-secondary border-opacity-10 border-opacity-10 pt-3 mt-2">
                        <div class="text-muted small mb-1">المصدر (Source Document)</div>
                        <div class="text-body font-monospace d-flex align-items-center gap-2">
                             <i class="bi bi-file-earmark-text"></i>
                            {{ class_basename($journalEntry->source_type) }} #{{ $journalEntry->source_id }}
                        </div>
                     </div>
                 @endif
            </div>
        </div>
    </div>
</div>

<style>
    
    .custom-table thead th {
        background-color: rgba(255, 255, 255, 0.03);
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border-color);
    }
    .table-row-hover {
        transition: all 0.2s ease;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }
    .table-row-hover:hover {
        background-color: var(--table-head-bg); 
    }
    .table-row-hover td { border: none; }
    .btn-glass-outline {
        background: var(--btn-glass-bg);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-primary);
        transition: all 0.2s;
    }
    .btn-glass-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--text-primary);
    }
</style>
@endsection