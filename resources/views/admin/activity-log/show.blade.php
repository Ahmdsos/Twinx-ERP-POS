@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-white">تفاصيل الحركة #{{ $activityLog->id }}</h4>
                <div class="text-white-50">
                    قام
                    <span class="text-white fw-bold">{{ $activityLog->user ? $activityLog->user->name : 'النظام' }}</span>
                    بـ
                    <span
                        class="badge bg-{{ $activityLog->action_color }} bg-opacity-10 text-{{ $activityLog->action_color }}">{{ $activityLog->action_label }}</span>
                    في {{ $activityLog->created_at->format('Y-m-d h:i A') }}
                </div>
            </div>
            <div>
                <a href="{{ route('activity-log.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-right me-2"></i>عودة
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Info -->
            <div class="col-md-4">
                <div class="glass-card p-4 rounded mb-4">
                    <h6 class="text-secondary text-uppercase small ls-1 mb-3">بيانات أساسية</h6>

                    <div class="mb-3">
                        <label class="text-white-50 d-block small mb-1">الموضوع (Subject)</label>
                        <div class="text-white font-monospace">{{ $activityLog->subject_type }}</div>
                        <div class="text-info font-monospace small">ID: {{ $activityLog->subject_id }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-white-50 d-block small mb-1">الوصف</label>
                        <div class="text-white">{{ $activityLog->description }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-white-50 d-block small mb-1">IP Address</label>
                        <div class="text-white font-monospace">{{ $activityLog->ip_address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Changes Diff -->
            <div class="col-md-8">
                <div class="glass-card p-4 rounded">
                    <h6 class="text-secondary text-uppercase small ls-1 mb-3">التغييرات (Audit Trail)</h6>

                    @if($activityLog->old_values || $activityLog->new_values)
                        <div class="table-responsive">
                            <table class="table table-dark table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-white-50 w-25">الحقل</th>
                                        <th class="text-danger bg-danger bg-opacity-10 w-37">القيمة القديمة</th>
                                        <th class="text-success bg-success bg-opacity-10 w-37">القيمة الجديدة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allKeys = array_unique(array_merge(
                                            array_keys($activityLog->old_values ?? []),
                                            array_keys($activityLog->new_values ?? [])
                                        ));
                                    @endphp

                                    @foreach($allKeys as $key)
                                        @php
                                            $old = $activityLog->old_values[$key] ?? null;
                                            $new = $activityLog->new_values[$key] ?? null;
                                            $isDifferent = $old !== $new;
                                        @endphp
                                        <tr class="{{ $isDifferent ? '' : 'opacity-50' }}">
                                            <td class="font-monospace text-white-50">{{ $key }}</td>
                                            <td class="font-monospace text-danger small">
                                                @if(is_array($old))
                                                    <pre
                                                        class="m-0 bg-transparent text-danger p-0">{{ json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                @else
                                                    {{ $old ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="font-monospace text-success small">
                                                @if(is_array($new))
                                                    <pre
                                                        class="m-0 bg-transparent text-success p-0">{{ json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                @else
                                                    {{ $new ?? '-' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-secondary bg-transparent border-secondary border-opacity-25 text-white-50">
                            <i class="bi bi-info-circle me-2"></i> لا توجد تغييرات مسجلة (قد يكون إجراء عرض أو حذف بدون تتبع)
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection