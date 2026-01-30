@extends('layouts.app')

@section('title', 'النسخ الاحتياطية - Twinx ERP')
@section('page-title', 'إدارة النسخ الاحتياطية')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">الإعدادات</a></li>
    <li class="breadcrumb-item active">النسخ الاحتياطية</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>النسخ الاحتياطية</h5>
                    <a href="{{ route('settings.backup.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>إنشاء نسخة احتياطية
                    </a>
                </div>
                <div class="card-body">
                    @if($backups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم الملف</th>
                                        <th>الحجم</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark-zip text-warning me-2"></i>
                                                {{ $backup['name'] }}
                                            </td>
                                            <td>{{ $backup['size'] }}</td>
                                            <td>{{ $backup['created_at']->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('settings.backup.download', $backup['name']) }}"
                                                    class="btn btn-sm btn-outline-primary" title="تحميل">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <form action="{{ route('settings.backup.destroy', $backup['name']) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه النسخة؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-archive text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">لا توجد نسخ احتياطية</h5>
                            <p class="text-muted">اضغط على "إنشاء نسخة احتياطية" لعمل أول نسخة</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card border-info">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle text-info me-2"></i>ملاحظات هامة</h6>
                    <ul class="mb-0 text-muted">
                        <li>يتم حفظ النسخ الاحتياطية في مجلد <code>storage/app/backups</code></li>
                        <li>يُنصح بعمل نسخة احتياطية قبل أي تحديث للنظام</li>
                        <li>قم بتحميل النسخ الاحتياطية وحفظها في مكان آمن خارج السيرفر</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection