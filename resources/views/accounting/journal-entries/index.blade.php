@extends('layouts.app')

@section('title', __('Journal Entries'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-heading mb-1">{{ __('Journal Entries') }}</h4>
        <div class="text-muted small">سجل العمليات المالية والقيود</div>
    </div>
    <a href="{{ route('journal-entries.create') }}" class="btn btn-primary shadow-lg fw-bold px-4 py-2">
        <i class="bi bi-plus-lg me-1"></i> قيد جديد
    </a>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="glass-stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted small">إجمالي القيود</div>
                <i class="bi bi-journal-album text-primary opacity-50 fs-4"></i>
            </div>
            <div class="fs-4 fw-bold text-body mb-1">{{ $stats['total'] }}</div>
            <div class="small text-muted">قيد مسجل بالنظام</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted small">مرحلة (Posted)</div>
                <i class="bi bi-check-circle text-success opacity-50 fs-4"></i>
            </div>
            <div class="fs-4 fw-bold text-success mb-1">{{ $stats['posted'] }}</div>
            <div class="small text-muted">قيد معتمد</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted small">مسودة (Draft)</div>
                <i class="bi bi-hourglass-split text-warning opacity-50 fs-4"></i>
            </div>
            <div class="fs-4 fw-bold text-warning mb-1">{{ $stats['draft'] }}</div>
            <div class="small text-muted">تحت المراجعة</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted small">إجمالي الحركات (مدين)</div>
                <i class="bi bi-cash-stack text-info opacity-50 fs-4"></i>
            </div>
            <div class="fs-4 fw-bold text-info mb-1">{{ number_format($stats['total_debit'], 2) }}</div>
            <div class="small text-muted">ج.م قيمة الحركات</div>
        </div>
    </div>
</div>

<div class="glass-card">
    <div class="table-responsive">
        <table class="table align-middle text-body mb-0 custom-table">
            <thead>
                <tr>
                    <th class="px-4 py-4 text-secondary-50 fw-normal">{{ __('Entry Number') }}</th>
                    <th class="py-4 text-secondary-50 fw-normal">{{ __('Date') }}</th>
                    <th class="py-4 text-secondary-50 fw-normal">الوصف / البيان</th>
                    <th class="py-4 text-secondary-50 fw-normal">إجمالي القيد</th>
                    <th class="py-4 text-center text-secondary-50 fw-normal">{{ __('Status') }}</th>
                    <th class="px-4 py-4 text-end text-secondary-50 fw-normal">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                <tr class="table-row-hover">
                    <td class="px-4 py-3 font-monospace text-info fs-5">#{{ $entry->entry_number }}</td>
                    <td class="py-3 fs-6">{{ $entry->entry_date->format('Y-m-d') }}</td>
                    <td class="py-3">
                        <div class="text-body fw-bold mb-1">{{ Str::limit($entry->description, 60) }}</div>
                        @if($entry->reference)
                        <div class="small text-muted font-monospace opacity-75">REF: {{ $entry->reference }}</div>
                        @endif
                    </td>
                    <td class="py-3 fw-bold fs-5 text-nowrap">{{ number_format($entry->total_debit, 2) }}</td>
                    <td class="text-center py-3">
                        @if($entry->status == \Modules\Accounting\Enums\JournalStatus::POSTED)
                            <div class="d-inline-flex align-items-center text-success bg-success bg-opacity-10 px-3 py-1 rounded-pill border border-success border-opacity-10">
                                <i class="bi bi-check-circle-fill me-2 small"></i> <span class="small fw-bold">Posted</span>
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center text-warning bg-warning bg-opacity-10 px-3 py-1 rounded-pill border border-warning border-opacity-10">
                                <i class="bi bi-hourglass-split me-2 small"></i> <span class="small fw-bold">Draft</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 text-end py-3">
                         <div class="d-flex justify-content-end gap-2">
                             <a href="{{ route('journal-entries.show', $entry) }}" class="btn btn-sm btn-glass text-info shadow-sm" title="التفاصيل">
                                <i class="bi bi-eye fs-6"></i>
                            </a>
                            @if($entry->status != \Modules\Accounting\Enums\JournalStatus::POSTED)
                            <a href="#" class="btn btn-sm btn-glass text-warning shadow-sm" title="{{ __('Edit') }}">
                                <i class="bi bi-pencil fs-6"></i>
                            </a>
                            @endif
                         </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                         <div class="d-flex flex-column align-items-center justify-content-center py-5 opacity-50">
                            <i class="bi bi-journal-album display-1 mb-4"></i>
                            <h4 class="text-heading-50">لا توجد قيود يومية</h4>
                            <p class="mb-4">لم يتم تسجيل أي عمليات مالية بعد</p>
                            <a href="{{ route('journal-entries.create') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">إضافة قيد يدوي</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 border-opacity-10 py-4">
        {{ $entries->links('partials.pagination') }}
    </div>
</div>

<style>
    
    .glass-stat-card {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px;
        transition: transform 0.2s;
    }
    .glass-stat-card:hover {
        transform: translateY(-5px);
        background: rgba(30, 41, 59, 0.8);
    }
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
        transform: translateY(-1px);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    .table-row-hover td { border: none; }
    
    .btn-glass {
        background: var(--btn-glass-bg);
        border: 1px solid var(--btn-glass-border);
        border-radius: 8px;
        width: 36px; height: 36px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .btn-glass:hover {
         background: rgba(255, 255, 255, 0.15);
         border-color: rgba(255, 255, 255, 0.3);
         transform: scale(1.05);
    }
</style>
@endsection