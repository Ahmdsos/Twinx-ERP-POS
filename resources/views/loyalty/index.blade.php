@extends('layouts.app')

@section('title', 'برنامج الولاء')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">برنامج الولاء</h1>
                <p class="text-muted mb-0">Loyalty Program Dashboard</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('loyalty.settings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-1"></i>
                    الإعدادات
                </a>
                <a href="{{ route('loyalty.report') }}" class="btn btn-outline-primary">
                    <i class="bi bi-bar-chart me-1"></i>
                    التقارير
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center bg-success bg-opacity-10">
                    <div class="card-body">
                        <i class="bi bi-gift display-4 text-success"></i>
                        <h6 class="text-muted mt-2">إجمالي النقاط المكتسبة</h6>
                        <h3 class="text-success">{{ number_format($stats['total_points_issued']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center bg-primary bg-opacity-10">
                    <div class="card-body">
                        <i class="bi bi-arrow-repeat display-4 text-primary"></i>
                        <h6 class="text-muted mt-2">النقاط المستبدلة</h6>
                        <h3 class="text-primary">{{ number_format($stats['total_points_redeemed']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center bg-warning bg-opacity-10">
                    <div class="card-body">
                        <i class="bi bi-wallet display-4 text-warning"></i>
                        <h6 class="text-muted mt-2">الرصيد الحالي</h6>
                        <h3 class="text-warning">{{ number_format($stats['total_current_balance']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center bg-info bg-opacity-10">
                    <div class="card-body">
                        <i class="bi bi-people display-4 text-info"></i>
                        <h6 class="text-muted mt-2">أعضاء نشطين</h6>
                        <h3 class="text-info">{{ number_format($stats['active_members']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Top Customers -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>أفضل العملاء</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th class="text-center">المستوى</th>
                                    <th class="text-end">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $lp)
                                    <tr>
                                        <td>
                                            <a href="{{ route('loyalty.show', $lp->customer_id) }}"
                                                class="text-decoration-none">
                                                {{ $lp->customer?->name ?? 'عميل #' . $lp->customer_id }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $lp->getTierBadgeClass() }}">{{ ucfirst($lp->tier) }}</span>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($lp->current_balance) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">لا يوجد أعضاء بعد</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>آخر المعاملات</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th class="text-center">النوع</th>
                                    <th class="text-end">النقاط</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                    <tr>
                                        <td>{{ $tx->customer?->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $tx->getTypeBadgeClass() }}">{{ $tx->getTypeLabel() }}</span>
                                        </td>
                                        <td class="text-end fw-bold {{ $tx->points > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $tx->points > 0 ? '+' : '' }}{{ $tx->points }}
                                        </td>
                                        <td><small>{{ $tx->created_at->format('Y-m-d H:i') }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">لا توجد معاملات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Points Modal -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>إضافة/استبدال نقاط</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('loyalty.add-points') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">العميل</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">اختر العميل...</option>
                            @foreach(\Modules\Sales\Models\Customer::where('is_active', true)->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">النقاط</label>
                        <input type="number" name="points" class="form-control" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الوصف</label>
                        <input type="text" name="description" class="form-control" required placeholder="سبب إضافة النقاط">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus me-1"></i>
                            إضافة نقاط
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection