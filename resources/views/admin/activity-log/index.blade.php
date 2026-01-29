@extends('layouts.app')

@section('title', 'سجل النشاطات - Twinx ERP')
@section('page-title', 'سجل النشاطات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سجل النشاطات</li>
@endsection

@section('content')
    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>تصفية النتائج</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('activity-log.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">المستخدم</label>
                        <select name="user_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الإجراء</label>
                        <select name="action" class="form-select">
                            <option value="">الكل</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="بحث..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>بحث
                        </button>
                        <a href="{{ route('activity-log.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>سجل النشاطات</h5>
            <span class="badge bg-info">{{ $activities->total() }} سجل</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">التاريخ</th>
                            <th style="width: 120px;">المستخدم</th>
                            <th style="width: 100px;">الإجراء</th>
                            <th>الوصف</th>
                            <th style="width: 120px;">IP</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        {{ $activity->created_at->format('Y-m-d') }}<br>
                                        {{ $activity->created_at->format('H:i:s') }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $activity->user_name ?? 'System' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $activity->action_color }}">
                                        {{ $activity->action_label }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $activity->description }}</div>
                                    @if($activity->subject_name)
                                        <small class="text-muted">{{ $activity->subject_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $activity->ip_address }}</small>
                                </td>
                                <td>
                                    @if($activity->old_values || $activity->new_values)
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                            data-bs-target="#detailsModal{{ $activity->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                    <p class="text-muted">لا توجد نشاطات مسجلة</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($activities->hasPages())
            <div class="card-footer">
                {{ $activities->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Details Modals -->
    @foreach($activities->filter(fn($a) => $a->old_values || $a->new_values) as $activity)
        <div class="modal fade" id="detailsModal{{ $activity->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تفاصيل التغييرات</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @if($activity->old_values)
                                <div class="col-md-6">
                                    <h6 class="text-danger"><i class="bi bi-dash-circle me-1"></i>القيم القديمة</h6>
                                    <pre
                                        class="bg-light p-3 rounded"><code>{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </div>
                            @endif
                            @if($activity->new_values)
                                <div class="col-md-6">
                                    <h6 class="text-success"><i class="bi bi-plus-circle me-1"></i>القيم الجديدة</h6>
                                    <pre
                                        class="bg-light p-3 rounded"><code>{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection