@extends('layouts.app')

@section('title', 'تحكم مهام التوصيل - Mission Control')

@section('content')
<div class="container-fluid py-4" id="mission-app">
    <!-- Header Section with Glassmorphism -->
    <div class="mission-header p-4 rounded-4 shadow-lg mb-4 text-white d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-broadcast me-2 text-warning animate-pulse"></i> مركز إدارة العمليات اللوجستية</h2>
            <p class="mb-0 opacity-75 fw-medium">متابعة حركة المناديب في الميدان وتسوية الحسابات لحظياً</p>
        </div>
        <div class="d-flex gap-3">
            <div class="stat-glass-card">
                <span class="small opacity-75">في الميدان</span>
                <div class="h3 mb-0 fw-bold">@{{ stats.active }}</div>
            </div>
            <div class="stat-glass-card success">
                <span class="small opacity-75">نجاح (اليوم)</span>
                <div class="h3 mb-0 fw-bold">@{{ stats.delivered }}</div>
            </div>
            <div class="stat-glass-card danger">
                <span class="small opacity-75">مرتجع (اليوم)</span>
                <div class="h3 mb-0 fw-bold">@{{ stats.returned }}</div>
            </div>
        </div>
    </div>

    <!-- Filter & Tools -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body bg-white p-3">
                    <form action="{{ route('reports.mission-control') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="driver_name" class="form-control border-0 bg-light" placeholder="ابحث باسم المندوب..." value="{{ request('driver_name') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">تحديث البيانات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Missions Grid -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4 py-3">تفاصيل المهمة</th>
                                <th>المسؤول (المندوب)</th>
                                <th>الوجهة (العميل)</th>
                                <th>القيمة المالية</th>
                                <th>الحالة الحالية</th>
                                <th class="text-center pe-4">إجراءات التحكم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($missions as $mission)
                            @php
                                $isSettled = in_array($mission->status->value, [\Modules\Sales\Enums\DeliveryStatus::DELIVERED->value, \Modules\Sales\Enums\DeliveryStatus::RETURNED->value]);
                            @endphp
                            <tr class="{{ $isSettled ? 'bg-settled' : '' }}">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="mission-icon-circle me-3" :class="'{{ $mission->status->value }}'">
                                            <i class="bi @if($mission->status->value == 'delivered') bi-check-lg @elseif($mission->status->value == 'returned') bi-arrow-counterclockwise @else bi-truck @endif"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $mission->do_number }}</div>
                                            <div class="x-small text-muted">INV: {{ $mission->salesOrder->reference ?? $mission->salesInvoice->invoice_number ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-secondary">{{ $mission->driver_name ?? 'غير محدد' }}</div>
                                    <div class="x-small text-muted">{{ $mission->vehicle_number ?? 'بدون مركبة' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $mission->customer->name }}</div>
                                    <div class="x-small text-truncate" style="max-width: 200px;"><i class="bi bi-geo-alt me-1"></i> {{ $mission->shipping_address }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary h6 mb-0">
                                        @if($mission->salesOrder)
                                            {{ number_format($mission->salesOrder->total, 2) }}
                                        @elseif($mission->salesInvoice)
                                            {{ number_format($mission->salesInvoice->total_amount, 2) }}
                                        @else
                                            0.00
                                        @endif
                                        <small class="x-small">ج.م</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="mission-badge {{ $mission->status->value }}">
                                        {{ $mission->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    @if(!$isSettled)
                                    <div class="btn-group rounded-pill shadow-sm overflow-hidden">
                                        <button @click="openSettleModal({{ $mission->id }}, 'delivered')" class="btn btn-success btn-sm px-3">
                                            <i class="bi bi-check2-circle me-1"></i> نجاح
                                        </button>
                                        <button @click="openSettleModal({{ $mission->id }}, 'returned')" class="btn btn-danger btn-sm px-3">
                                            <i class="bi bi-x-circle me-1"></i> فشل
                                        </button>
                                    </div>
                                    @else
                                    <div class="text-muted small">
                                        <i class="bi bi-archive me-1"></i> تمت التسوية في {{ $mission->updated_at->format('H:i') }}
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 border-0">
                                    <img src="{{ asset('images/empty_logistics.svg') }}" style="width: 150px; opacity: 0.3;" class="mb-3 d-block mx-auto">
                                    <h5 class="text-muted fw-bold">لا يوجد مهام نشطة حالياً</h5>
                                    <p class="text-secondary small">سيتم إدراج أي أوردر يتم شحنه آلياً هنا</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($missions->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $missions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Settlement Modal -->
    <div class="modal fade" id="settleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 p-4" :class="settleType === 'delivered' ? 'bg-success text-white' : 'bg-danger text-white'">
                    <h5 class="modal-title fw-bold">
                        <i class="bi me-2" :class="settleType === 'delivered' ? 'bi-check2-all' : 'bi-arrow-counterclockwise'"></i>
                        تأكيد تسوية المهمة رقم <span class="bg-white text-dark px-2 rounded small">@{{ currentMissionId }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="display-6" :class="settleType === 'delivered' ? 'text-success' : 'text-danger'">
                            <i class="bi" :class="settleType === 'delivered' ? 'bi-cash-coin' : 'bi-box-arrow-in-left'"></i>
                        </div>
                        <h5 class="fw-bold mt-2">@{{ settleType === 'delivered' ? 'تم التسليم بنجاح' : 'تم استرجاع الطلب' }}</h5>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات العملية <span class="text-muted font-normal">(اختياري)</span></label>
                        <textarea v-model="settleNotes" class="form-control rounded-3 bg-light border-0" rows="3" placeholder="أدخل تفاصيل إضافية للمرجعية..."></textarea>
                    </div>

                    <div class="alert small rounded-3" :class="settleType === 'delivered' ? 'alert-success border-0 bg-success-soft' : 'alert-danger border-0 bg-danger-soft'">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <span v-if="settleType === 'delivered'">سيتم تحرير رسوم التوصيل وتسجيلها كإيرادات في الخزينة.</span>
                        <span v-else>سيتم إعادة الأصناف للمخزن وعكس القيد المحاسبي لرسوم التوصيل.</span>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">تراجع</button>
                    <button @click="processSettlement" class="btn rounded-pill px-4 fw-bold" :class="settleType === 'delivered' ? 'btn-success' : 'btn-danger'" :disabled="isProcessing">
                        <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
                        تأكيد وإتمام التسوية
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body { 
        background-color: #0f172a !important; 
        color: #e2e8f0 !important;
    }
    
    .mission-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid rgba(255,255,255,0.05);
    }
    
    .stat-glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(8px);
        padding: 10px 20px;
        border-radius: 15px;
        min-width: 120px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .stat-glass-card.success { border-bottom: 4px solid #10b981; }
    .stat-glass-card.danger { border-bottom: 4px solid #ef4444; }
    
    .card {
        background-color: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .table { color: #cbd5e1 !important; }
    .table thead th { background-color: #334155 !important; color: #94a3b8 !important; border-bottom: 0; }
    .table tbody td { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }

    .mission-icon-circle {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .mission-icon-circle.ready { background-color: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
    .mission-icon-circle.shipped { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .mission-icon-circle.delivered { background-color: rgba(16, 185, 129, 0.15); color: #10b981; }
    .mission-icon-circle.returned { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; }
    
    .mission-badge {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-block;
    }
    
    .mission-badge.ready { background-color: rgba(14, 165, 233, 0.2); color: #38bdf8; }
    .mission-badge.shipped { background-color: rgba(245, 158, 11, 0.2); color: #fbbf24; }
    .mission-badge.delivered { background-color: rgba(16, 185, 129, 0.2); color: #34d399; }
    .mission-badge.returned { background-color: rgba(239, 68, 68, 0.2); color: #f87171; }
    
    .bg-settled { background-color: rgba(0,0,0, 0.2) !important; opacity: 0.7; }
    
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1); color: #34d399; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1); color: #f87171; }
    
    .text-dark { color: #f1f5f9 !important; }
    .text-secondary { color: #94a3b8 !important; }
    .bg-white { background-color: #1e293b !important; }
    .bg-light { background-color: #334155 !important; }
    
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .table-hover tbody tr:hover { background-color: rgba(255, 255, 255, 0.02) !important; }
    .x-small { font-size: 0.7rem; }
    .font-normal { font-weight: 400; }

    /* Fix Pagination for Dark Mode */
    .pagination .page-link { background-color: #1e293b; border-color: #334155; color: #94a3b8; }
    .pagination .page-item.active .page-link { background-color: #3b82f6; border-color: #3b82f6; color: white; }
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                stats: @json($stats),
                settleType: '',
                currentMissionId: null,
                settleNotes: '',
                isProcessing: false
            }
        },
        methods: {
            openSettleModal(id, type) {
                this.currentMissionId = id;
                this.settleType = type;
                this.settleNotes = '';
                new bootstrap.Modal(document.getElementById('settleModal')).show();
            },
            processSettlement() {
                this.isProcessing = true;
                const url = '{{ route("reports.mission.settle", ":id") }}'.replace(':id', this.currentMissionId);
                
                axios.post(url, {
                    status: this.settleType,
                    notes: this.settleNotes
                })
                .then(res => {
                    if (res.data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('خطأ في التسوية: ' + (err.response?.data?.message || err.message));
                    this.isProcessing = false;
                });
            }
        }
    }).mount('#mission-app');
</script>
@endpush