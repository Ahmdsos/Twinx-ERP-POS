@extends('layouts.app')

@section('title', 'عروض الأسعار')

@section('content')
    <div class="container-fluid p-0">

        <!-- Header & Actions -->
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold text-white mb-2 tracking-wide display-6">
                    <i class="bi bi-file-earmark-text text-info me-2"></i> عروض الأسعار
                </h2>
                <p class="text-gray-400 mb-0 fs-5">إدارة العروض المقدمة للعملاء ومتابعة حالتها</p>
            </div>
            <a href="{{ route('quotations.create') }}"
                class="btn btn-gradient-info shadow-lg px-4 py-2 rounded-pill fs-6 fw-bold d-flex align-items-center gap-2 hover-scale">
                <i class="bi bi-plus-circle-fill fs-4"></i>
                <span>عرض سعر جديد</span>
            </a>
        </div>

        <!-- 3D Stats Cards -->
        <div class="row g-4 mb-5">
            <!-- Pending Quotations -->
            <div class="col-md-4">
                <div class="glass-card stat-card h-100 position-relative border-0 shadow-lg overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="bi bi-hourglass-split display-1 text-white"></i>
                    </div>
                    <div class="p-4 position-relative z-1">
                        <div
                            class="badge bg-white/10 text-white mb-3 backdrop-blur-md border border-white/10 px-3 py-1 rounded-pill">
                            <i class="bi bi-clock me-1"></i> عروض قيد الانتظار
                        </div>
                        <h1 class="text-white fw-bold mb-1 display-5">{{ number_format($stats['pending'] ?? 0) }}</h1>
                        <span class="text-warning small fw-bold">
                            <i class="bi bi-exclamation-circle"></i> تحتاج متابعة
                        </span>
                    </div>
                    <div class="progress h-1 mt-3 bg-white/5">
                        <div
                            class="progress-bar bg-warning w-{{ min(($stats['pending'] ?? 0) * 10, 100) }} shadow-neon-warning">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="col-md-4">
                <div class="glass-card stat-card h-100 position-relative border-0 shadow-lg overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="bi bi-cash-coin display-1 text-white"></i>
                    </div>
                    <div class="p-4 position-relative z-1">
                        <div
                            class="badge bg-white/10 text-white mb-3 backdrop-blur-md border border-white/10 px-3 py-1 rounded-pill">
                            <i class="bi bi-graph-up me-1"></i> قيمة العروض المعلقة
                        </div>
                        <h1 class="text-white fw-bold mb-1 display-5">{{ number_format($stats['total_value'] ?? 0, 2) }}
                        </h1>
                        <span class="text-info small fw-bold">
                            <i class="bi bi-currency-dollar"></i> إجمالي متوقع
                        </span>
                    </div>
                    <div class="progress h-1 mt-3 bg-white/5">
                        <div class="progress-bar bg-info w-75 shadow-neon-blue"></div>
                    </div>
                </div>
            </div>

            <!-- Accepted/Conversion -->
            <div class="col-md-4">
                <div class="glass-card stat-card h-100 position-relative border-0 shadow-lg overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="bi bi-check-circle display-1 text-white"></i>
                    </div>
                    <div class="p-4 position-relative z-1">
                        <div
                            class="badge bg-white/10 text-white mb-3 backdrop-blur-md border border-white/10 px-3 py-1 rounded-pill">
                            <i class="bi bi-check2-all me-1"></i> تم قبولها
                        </div>
                        <h1 class="text-white fw-bold mb-1 display-5">{{ number_format($stats['accepted'] ?? 0) }}</h1>
                        <span class="text-success small fw-bold">
                            <i class="bi bi-arrow-right-circle"></i> جاهزة للتحويل لفواتير
                        </span>
                    </div>
                    <div class="progress h-1 mt-3 bg-white/5">
                        <div class="progress-bar bg-success w-50 shadow-neon"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="glass-panel p-4 mb-4 rounded-4 border border-white/10 shadow-lg position-relative overflow-hidden">
            <div
                class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-r from-blue-500/5 to-purple-500/5 pointer-events-none">
            </div>

            <form action="{{ route('quotations.index') }}" method="GET" class="position-relative z-1">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="text-gray-300 small fw-bold mb-2 ps-1">بحث برقم العرض</label>
                        <div class="input-group glass-input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-gray-400 ps-3"><i
                                    class="bi bi-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control bg-transparent border-start-0 text-white shadow-none"
                                placeholder="QT-XXXX...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-gray-300 small fw-bold mb-2 ps-1">العميل</label>
                        <select name="customer_id" class="form-select glass-select text-white shadow-none">
                            <option value="" class="bg-gray-900 text-gray-400">-- كل العملاء --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" class="bg-gray-900 text-white" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-gray-300 small fw-bold mb-2 ps-1">الحالة</label>
                        <select name="status" class="form-select glass-select text-white shadow-none">
                            <option value="" class="bg-gray-900 text-gray-400">-- الكل --</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" class="bg-gray-900 {{ $status->color() }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-gradient-info w-100 fw-bold shadow-neon-blue py-2">
                            <i class="bi bi-funnel-fill me-2"></i> تصفية
                        </button>
                        <a href="{{ route('quotations.index') }}"
                            class="btn btn-glass-icon px-3 d-flex align-items-center justify-content-center"
                            title="إعادة تعيين">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="glass-panel rounded-4 overflow-hidden shadow-lg">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="py-3 ps-4 text-gray-400 fw-normal">رقم العرض</th>
                            <th class="py-3 text-gray-400 fw-normal">العميل</th>
                            <th class="py-3 text-gray-400 fw-normal">التاريخ / الصلاحية</th>
                            <th class="py-3 text-gray-400 fw-normal">الحالة</th>
                            <th class="py-3 text-gray-400 fw-normal text-end">الإجمالي</th>
                            <th class="py-3 pe-4 text-gray-400 fw-normal text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            <tr class="hover-bg-white-5">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-square-sm bg-info/20 text-info rounded-2">
                                            <i class="bi bi-hash"></i>
                                        </div>
                                        <span
                                            class="font-monospace fw-bold text-white">{{ $quotation->quotation_number }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($quotation->customer)
                                        <span class="fw-bold text-white">{{ $quotation->customer->name }}</span>
                                    @elseif($quotation->target_customer_type)
                                        <span class="badge bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                            جميع عملاء: {{ $quotation->target_customer_type_label }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-white small">{{ $quotation->quotation_date->format('Y-m-d') }}</span>
                                        <small class="text-gray-500 x-small">ينتهي:
                                            {{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '-' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $quotation->status->badgeClass() }} border border-white/10">
                                        {{ $quotation->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <h5 class="mb-0 fw-bold text-white text-glow">{{ number_format($quotation->total, 2) }}</h5>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('quotations.show', $quotation->id) }}"
                                            class="btn btn-sm btn-icon-glass text-info" title="عرض">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('quotations.edit', $quotation->id) }}"
                                            class="btn btn-sm btn-icon-glass text-warning" title="تعديل">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="{{ route('quotations.print', $quotation->id) }}" target="_blank"
                                            class="btn btn-sm btn-icon-glass text-secondary" title="طباعة">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center opacity-50">
                                        <div class="icon-circle bg-gray-800 text-gray-500 mb-3"
                                            style="width: 80px; height: 80px;">
                                            <i class="bi bi-inbox fs-1"></i>
                                        </div>
                                        <h5 class="text-gray-400">لا توجد عروض أسعار</h5>
                                        <p class="text-gray-600 small">اضغط على زر "عرض سعر جديد" للبدء</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($quotations->hasPages())
                <div class="p-4 border-top border-white/10 d-flex justify-content-center">
                    {{ $quotations->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }

        .shadow-neon-info {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }

        .shadow-neon-warning {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
        }

        .text-glow {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .hover-bg-white-5:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-gradient-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none;
            color: white;
        }

        .icon-square-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Reusing Filter Styles from Payments */
        .glass-input-group,
        .glass-select {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .glass-input-group:focus-within,
        .glass-select:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: rgba(56, 189, 248, 0.5);
            /* Sky Blue Glow */
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
        }

        .glass-input-group .input-group-text {
            border: none;
        }

        .glass-input-group input {
            border: none;
            padding: 10px 15px;
        }

        .glass-select {
            padding: 10px 15px;
            cursor: pointer;
        }

        .btn-glass-icon {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            transition: all 0.2s;
        }

        .btn-glass-icon:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-color: rgba(255, 255, 255, 0.2);
        }
    </style>
@endsection