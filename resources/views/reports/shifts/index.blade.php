@extends('layouts.app')

@section('title', 'تقارير الورديات')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-white">
                <i class="bi bi-clock-history me-2 text-primary"></i> تقارير الورديات
            </h2>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4 border-0 glass-card"
            style="background: rgba(30, 30, 40, 0.8); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="card-body rounded-3">
                <form action="{{ route('reports.shifts') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">الكاشير</label>
                        <select name="cashier_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">كل الكاشيرات</option>
                            @foreach($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" {{ request('cashier_id') == $cashier->id ? 'selected' : '' }}>
                                    {{ $cashier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">الحالة</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="">الكل</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control bg-dark text-white border-secondary"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control bg-dark text-white border-secondary"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-filter me-1"></i> تصفية
                        </button>
                        <a href="{{ route('reports.shifts') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Shifts Table -->
        <div class="card shadow-sm border-0 glass-card"
            style="background: rgba(30, 30, 40, 0.8); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead class="text-secondary" style="background: rgba(0,0,0,0.2);">
                            <tr>
                                <th class="ps-4"># الوردية</th>
                                <th>الكاشير</th>
                                <th>توقيت الفتح</th>
                                <th>توقيت الإغلاق</th>
                                <th>إجمالي المبيعات</th>
                                <th>الحالة</th>
                                <th class="text-end pe-4">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody style="border-top-color: rgba(255,255,255,0.1);">
                            @forelse($shifts as $shift)
                                <tr style="border-bottom-color: rgba(255,255,255,0.05);">
                                    <td class="ps-4 fw-bold text-white">#{{ $shift->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;">
                                                {{ substr($shift->user->name, 0, 1) }}
                                            </div>
                                            <span class="text-white">{{ $shift->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-white">{{ $shift->opened_at->format('Y-m-d') }}</div>
                                        <div class="small text-secondary">{{ $shift->opened_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        @if($shift->closed_at)
                                            <div class="text-white">{{ $shift->closed_at->format('Y-m-d') }}</div>
                                            <div class="small text-secondary">{{ $shift->closed_at->format('h:i A') }}</div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">
                                        {{ number_format($shift->total_amount, 2) }} EGP
                                    </td>
                                    <td>
                                        @if($shift->status === 'open')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-circle-fill small me-1"></i> مفتوحة
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                مغلقة
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('pos.shift.report', $shift->id) }}"
                                            class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="bi bi-eye me-1"></i> عرض التقرير
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        <div class="mb-2"><i class="bi bi-inbox fs-1 opacity-25"></i></div>
                                        لا توجد ورديات مطابقة للفلاتر
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($shifts->hasPages())
                <div class="card-footer border-top py-3"
                    style="background: transparent; border-top-color: rgba(255,255,255,0.1) !important;">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection