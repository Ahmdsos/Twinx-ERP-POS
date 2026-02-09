@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-white">النسخ الاحتياطية</h4>
                <p class="text-white-50 mb-0">إدارة النسخ الاحتياطية لقاعدة البيانات</p>
            </div>
            <div>
                <a href="{{ route('settings.backup.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>نسخة جديدة</span>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- System Comparison / Status -->
        <div class="glass-card mb-4 p-4 border border-primary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="text-white fw-bold mb-1"><i class="bi bi-database-check me-2 text-primary"></i> حالة النظام
                        الحالية</h5>
                    <p class="text-white-50 mb-0 small">بيانات النسخة الحية (Live)</p>
                </div>
                <div class="text-end">
                    <div class="display-6 fw-bold text-white">
                        {{ App\Http\Controllers\BackupController::formatBytes($currentSystemSize ?? 0) }}
                    </div>
                    <div class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">Active
                        Database</div>
                </div>
            </div>
        </div>

        <!-- Backups List -->
        <div class="glass-card table-responsive mb-4">
            <h5 class="text-white fw-bold px-4 pt-4 mb-3"><i class="bi bi-archive me-2"></i> أرشيف النسخ الاحتياطية</h5>
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">اسم الملف</th>
                        <th>الحجم</th>
                        <th>تاريخ الإنشاء</th>
                        <th>مقارنة (Diff)</th>
                        <th class="text-end pe-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-square bg-secondary bg-opacity-10 text-white rounded me-3">
                                        <i class="bi bi-filetype-sql fs-4"></i>
                                    </div>
                                    <div class="text-white fw-medium font-monospace">{{ $backup['name'] }}</div>
                                </div>
                            </td>
                            <td class="text-white-50">{{ $backup['size'] }}</td>
                            <td class="text-white-50" dir="ltr">{{ $backup['created_at']->format('Y-m-d H:i') }}</td>
                            <td>
                                <!-- Simple size comparison visual -->
                                @if(abs(($backup['raw_size'] ?? 0) - $currentSystemSize) < 1024)
                                    <span class="badge bg-secondary bg-opacity-25 text-white">مطابق للحجم الحالي</span>
                                @elseif(($backup['raw_size'] ?? 0) > $currentSystemSize)
                                    <span class="text-success small"><i class="bi bi-arrow-up"></i> أكبر
                                        ({{ $backup['size'] }})</span>
                                @else
                                    <span class="text-warning small"><i class="bi bi-arrow-down"></i> أصغر
                                        ({{ $backup['size'] }})</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('settings.backup.compare', $backup['name']) }}"
                                        class="btn btn-sm btn-info text-dark fw-bold d-flex align-items-center gap-1"
                                        title="مقارنة">
                                        <i class="bi bi-scale"></i>
                                        <span class="d-none d-md-inline">مقارنة</span>
                                    </a>

                                    <a href="{{ route('settings.backup.open_folder', $backup['name']) }}"
                                        class="btn btn-sm btn-outline-warning" title="فتح المجلد">
                                        <i class="bi bi-folder2-open"></i>
                                    </a>

                                    <a href="{{ route('settings.backup.download', $backup['name']) }}"
                                        class="btn btn-sm btn-outline-light" title="تحميل">
                                        <i class="bi bi-download"></i>
                                    </a>

                                    <form action="{{ route('settings.backup.restore', $backup['name']) }}" method="POST"
                                        class="d-inline"
                                        data-confirm="⚠️ مقارنة سريعة:\nالنسخة الحالية: {{ App\Http\Controllers\BackupController::formatBytes($currentSystemSize) }}\nالنسخة المختارة: {{ $backup['size'] }}\n\nسيتم استبدال قاعدة البيانات بالكامل. هل أنت متأكد؟">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger px-3 fw-bold" title="استعادة النظام">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> استعادة
                                        </button>
                                    </form>

                                    <form action="{{ route('settings.backup.destroy', $backup['name']) }}" method="POST"
                                        class="d-inline" data-confirm="هل أنت متأكد من حذف هذه النسخة؟">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm btn-outline-danger border-0 opacity-50 hover-opacity-100"
                                            title="حذف">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-white-50">
                                <i class="bi bi-cloud-slash fs-1 d-block mb-3 opacity-50"></i>
                                لا توجد نسخ احتياطية حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Restoration History -->
        <div class="glass-card p-4 mb-4">
            <h5 class="text-white fw-bold mb-3"><i class="bi bi-clock-history me-2 text-info"></i> سجل الاستعادات السابق
                (History)</h5>
            <div class="table-responsive">
                <table class="table table-sm table-borderless text-white-50 align-middle">
                    <thead>
                        <tr class="border-bottom border-secondary border-opacity-25 text-uppercase small">
                            <th class="py-2">بواسطة</th>
                            <th>التفاصيل</th>
                            <th class="text-end">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($restorationHistory as $log)
                            <tr>
                                <td class="py-2 text-white">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle"></i>
                                        {{ $log->user_name }}
                                    </span>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td class="text-end font-monospace small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-white-50">
                                    <i class="bi bi-clock-history fs-3 d-block mb-2 opacity-50"></i>
                                    لم يتم إجراء أي عمليات استعادة للنظام بعد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="glass-card mt-4 p-4 border-dashed border-secondary border-opacity-25">
            <h5 class="text-white mb-3"><i class="bi bi-upload me-2"></i> استيراد نسخة خارجية</h5>
            <form action="{{ route('settings.backup.upload') }}" method="POST" enctype="multipart/form-data"
                class="row align-items-end g-3">
                @csrf
                <div class="col-md-8">
                    <label class="form-label text-white-50 small">ملف SQL</label>
                    <input type="file" name="backup_file"
                        class="form-control bg-transparent text-white border-secondary border-opacity-25" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-cloud-upload me-2"></i> رفع للقائمة
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection