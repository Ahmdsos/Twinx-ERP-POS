@extends('layouts.app')

@section('title', 'إدارة السائقين والتوصيل')
@section('header', 'إدارة أسطول التوصيل')

@section('content')
    <div class="dashboard-wrapper">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h3 class="fw-black text-white mb-0">أسطول التوصيل</h3>
                <p class="text-secondary small opacity-75 mt-1">
                    <i class="bi bi-truck text-primary me-1"></i>
                    متابعة السائقين، حالات التوصيل، وتقييم الأداء الميداني.
                </p>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <!-- Create Button Removed: Drivers are now created via Employees module -->
        </div>
    </div>

    <!-- Stats Section -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="glass-card rounded-4 p-4 border-white border-opacity-10">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div>
                        <span class="text-secondary x-small d-block fw-bold">إجمالي السائقين</span>
                        <h4 class="text-white fw-black mb-0">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="glass-card rounded-4 p-4 mb-4 border-white border-opacity-10">
        <form action="{{ route('hr.delivery.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label text-secondary small fw-bold">بحث شامل</label>
                <div class="input-group">
                    <span class="input-group-text bg-white bg-opacity-5 border-white border-opacity-10 text-secondary"><i
                            class="bi bi-search"></i></span>
                    <input type="text" name="search"
                        class="form-control bg-white bg-opacity-5 text-white border-white border-opacity-10 shadow-none"
                        placeholder="اسم السائق، الكود، أو لوحة السيارة..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 fw-bold">
                    <i class="bi bi-funnel"></i> تصفية الأسطول
                </button>
                <a href="{{ route('hr.delivery.index') }}"
                    class="btn btn-outline-secondary rounded-pill fw-bold border-2 px-3" title="إعادة تعيين"><i
                        class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="glass-card rounded-4 overflow-hidden border-white border-opacity-10">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-white bg-opacity-5">
                        <th class="ps-4 py-3 text-secondary small fw-bold border-0">السائق وتفاصيل التواصل</th>
                        <th class="py-3 text-secondary small fw-bold border-0">المركبة والرخصة</th>
                        <th class="py-3 text-secondary small fw-bold border-0 text-center">نسبة النجاح</th>
                        <th class="py-3 text-secondary small fw-bold border-0 text-center">إجمالي المهام</th>
                        <th class="pe-4 py-3 text-end text-secondary small fw-bold border-0">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-white">
                    @forelse($drivers as $driver)
                        <tr class="border-bottom border-white border-opacity-5">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        class="avatar-circle rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $driver->employee->full_name ?? '---' }}</div>
                                        <div class="text-secondary x-small opacity-75"><i class="bi bi-telephone me-1"></i>
                                            {{ $driver->employee->phone ?? 'لا يوجد رقم' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $driver->vehicle_info ?? 'غير محدد' }}</div>
                                <div class="x-small text-secondary opacity-75">رخصة: {{ $driver->license_number ?? '---' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div
                                    class="fw-bold {{ $driver->success_rate > 80 ? 'text-success' : ($driver->success_rate > 50 ? 'text-warning' : 'text-danger') }}">
                                    {{ $driver->success_rate }}%
                                </div>
                                <div class="x-small text-muted">معدل الإنجاز</div>
                            </td>
                            <td class="text-center">
                                <span class="fw-black text-secondary opacity-75">{{ $driver->total_deliveries }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('hr.delivery.show', $driver) }}"
                                        class="btn btn-sm btn-icon btn-outline-primary rounded-circle" title="عرض الملف الذكي">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('hr.delivery.edit', $driver) }}"
                                        class="btn btn-sm btn-icon btn-outline-info rounded-circle" title="تعديل">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('hr.delivery.destroy', $driver) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا السائق؟ لن يتم حذف الموظف.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-outline-danger rounded-circle" title="حذف"><i
                                                class="bi bi-trash-fill"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-truck text-secondary display-1 opacity-10"></i>
                                <p class="text-secondary mt-3">لم يتم العثور على سائقين مسجلين بهذه المعايير.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($drivers->hasPages())
            <div class="p-4 border-top border-white border-opacity-10">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>
    </div>

    <style>
        .fw-black {
            font-weight: 900 !important;
        }

        .x-small {
            font-size: 0.75rem !important;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            color: white !important;
        }
    </style>
@endsection