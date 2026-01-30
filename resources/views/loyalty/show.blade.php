@extends('layouts.app')

@section('title', 'تفاصيل ولاء العميل - Twinx ERP')
@section('page-title', 'تفاصيل برنامج الولاء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('loyalty.index') }}">برنامج الولاء</a></li>
    <li class="breadcrumb-item active">تفاصيل العميل</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <!-- Customer Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>معلومات العميل</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle" style="font-size: 5rem; color: var(--bs-primary);"></i>
                    </div>
                    <h4>{{ $customer->name }}</h4>
                    <p class="text-muted mb-0">{{ $customer->code }}</p>
                    @if($customer->phone)
                        <p class="mb-0"><i class="bi bi-phone me-1"></i>{{ $customer->phone }}</p>
                    @endif
                    @if($customer->email)
                        <p class="mb-0"><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</p>
                    @endif
                </div>
            </div>

            <!-- Points Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>ملخص النقاط</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>النقاط الحالية</span>
                        <span class="badge bg-success fs-5">{{ number_format($loyalty->current_balance ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>إجمالي النقاط المكتسبة</span>
                        <span class="text-muted">{{ number_format($loyalty->total_earned ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>النقاط المستخدمة</span>
                        <span class="text-muted">{{ number_format($loyalty->total_redeemed ?? 0) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>المستوى</span>
                        @php
                            $tier = $loyalty->tier ?? 'bronze';
                            $tierInfo = match ($tier) {
                                'silver' => ['label' => 'فضي', 'class' => 'bg-secondary'],
                                'gold' => ['label' => 'ذهبي', 'class' => 'bg-warning text-dark'],
                                'platinum' => ['label' => 'بلاتيني', 'class' => 'bg-dark'],
                                default => ['label' => 'برونزي', 'class' => 'bg-orange']
                            };
                        @endphp
                        <span class="badge {{ $tierInfo['class'] }} fs-6">{{ $tierInfo['label'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Points History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>سجل النقاط</h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($transactions) && $transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>النوع</th>
                                        <th>النقاط</th>
                                        <th>الوصف</th>
                                        <th>المرجع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @if($transaction->type === 'earn')
                                                    <span class="badge bg-success">اكتساب</span>
                                                @elseif($transaction->type === 'redeem')
                                                    <span class="badge bg-danger">استبدال</span>
                                                @elseif($transaction->type === 'adjust')
                                                    <span class="badge bg-warning text-dark">تعديل</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transaction->type }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($transaction->type === 'earn' || ($transaction->type === 'adjust' && $transaction->points > 0))
                                                    <span class="text-success">+{{ number_format($transaction->points) }}</span>
                                                @else
                                                    <span class="text-danger">-{{ number_format(abs($transaction->points)) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->description ?? '-' }}</td>
                                            <td>
                                                @if($transaction->reference)
                                                    <code>{{ $transaction->reference }}</code>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($transactions->hasPages())
                            <div class="d-flex justify-content-center py-3">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">لا يوجد سجل للنقاط</h5>
                            <p class="text-muted">سيظهر هنا سجل اكتساب واستبدال النقاط</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-orange {
            background-color: #cd7f32 !important;
            color: white;
        }
    </style>
@endpush