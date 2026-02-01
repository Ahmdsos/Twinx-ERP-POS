@extends('layouts.app')

@section('title', 'تقرير برنامج الولاء')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تقرير برنامج الولاء</h1>
                <p class="text-muted mb-0">Loyalty Program Report</p>
            </div>
            <a href="{{ route('loyalty.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>
                العودة
            </a>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center bg-success bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-muted">النقاط المكتسبة</h6>
                        <h3 class="text-success">+{{ number_format($earned) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center bg-primary bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-muted">النقاط المستبدلة</h6>
                        <h3 class="text-primary">{{ number_format(abs($redeemed)) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Tier Breakdown -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>توزيع المستويات</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>المستوى</th>
                                    <th class="text-center">الأعضاء</th>
                                    <th class="text-end">إجمالي النقاط</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tierBreakdown as $tier)
                                    <tr>
                                        <td>
                                            <span class="badge 
                                                        @if($tier->tier === 'platinum') bg-dark
                                                        @elseif($tier->tier === 'gold') bg-warning text-dark
                                                        @elseif($tier->tier === 'silver') bg-secondary
                                                        @else bg-danger
                                                        @endif">
                                                {{ ucfirst($tier->tier) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $tier->count }}</td>
                                        <td class="text-end">{{ number_format($tier->total_points) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Earners -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>أكثر العملاء اكتساباً للنقاط</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>العميل</th>
                                    <th class="text-end">النقاط</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topEarners as $index => $earner)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $earner->customer?->name ?? 'عميل #' . $earner->customer_id }}</td>
                                        <td class="text-end fw-bold text-success">+{{ number_format($earner->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection