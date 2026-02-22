@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-heading">{{ __('Activity Log') }}<span class="text-primary">(Audit Log)</span></h4>
                <p class="text-body-50 mb-0">مراقبة دقيقة لكل حركة تتم داخل النظام</p>
            </div>
            <div>
                <a href="{{ route('activity-log.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Update') }}</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-3 d-flex align-items-center">
                    <div class="icon-square bg-primary bg-opacity-10 text-primary rounded me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body fw-bold fs-5">{{ $activities->total() }}</div>
                        <div class="text-muted small">إجمالي السجلات</div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <!-- Search & Filters -->
                <div class="glass-card p-3">
                    <form action="{{ route('activity-log.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <select name="user_id"
                                class="form-select bg-transparent text-body border-secondary border-opacity-25 form-select-sm">
                                <option value="">كل المستخدمين</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="action"
                                class="form-select bg-transparent text-body border-secondary border-opacity-25 form-select-sm">
                                <option value="">نوع الإجراء (All)</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ $action }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span
                                    class="input-group-text bg-transparent border-secondary border-opacity-25 text-muted"><i
                                        class="bi bi-search"></i></span>
                                <input type="text" name="search"
                                    class="form-control bg-transparent text-body border-secondary border-opacity-25"
                                    placeholder="بحث في الوصف..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Filter') }}</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('activity-log.index') }}"
                                class="btn btn-outline-secondary btn-sm w-100">{{ __('Clear') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Logs List -->
        <div class="glass-card table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 20%">{{ __('User') }}</th>
                        <th style="width: 15%">{{ __('Action') }}</th>
                        <th style="width: 40%">تفاصيل الحدث</th>
                        <th style="width: 15%">التوقيت</th>
                        <th class="text-end pe-4" style="width: 10%">{{ __('View') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $log)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-secondary bg-opacity-10 text-body rounded-circle me-2"
                                        style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                                        {{ mb_substr($log->user_name ?? 'Sys', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body fs-6">{{ $log->user_name ?? 'System' }}</div>
                                        <div class="text-muted tiny font-monospace">{{ $log->ip_address }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge rounded-pill bg-{{ $log->action_color }} bg-opacity-25 text-white border border-{{ $log->action_color }} border-opacity-50 px-3 py-2">
                                    <i
                                        class="bi bi-{{ $log->action == 'deleted' ? 'trash' : ($log->action == 'created' ? 'plus-lg' : ($log->action == 'updated' ? 'pencil' : 'info-circle')) }} me-1"></i>
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td>
                                <div class="text-body mb-1">{{ $log->description }}</div>
                                <div class="d-flex gap-2">
                                    <span
                                        class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 font-monospace">
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="text-info small fw-bold" dir="ltr">{{ $log->created_at->format('H:i:s') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('Y-m-d') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('activity-log.show', $log->id) }}"
                                    class="btn btn-sm btn-outline-light rounded-pill px-3">
                                    التفاصيل <i class="bi bi-chevron-left ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="bi bi-search fs-1 d-block mb-3"></i>
                                    <h5 class="text-heading-50">لا توجد سجلات مطابقة</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $activities->withQueryString()->links() }}
        </div>
    </div>
@endsection