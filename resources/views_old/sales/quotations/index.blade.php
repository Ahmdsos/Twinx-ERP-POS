@extends('layouts.app')

@section('title', 'عروض الأسعار - Twinx ERP')
@section('page-title', 'عروض الأسعار')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">عروض الأسعار</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">عروض قيد الانتظار</h6>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-hourglass-split text-info fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">عروض مقبولة</h6>
                            <h3 class="mb-0 text-success">{{ $stats['accepted'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle text-success fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">قيمة العروض المعلقة</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_value'], 2) }} ج.م</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cash text-primary fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>عرض سعر جديد
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $quotations->total() }}</strong> عرض
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('quotations.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم العرض">
                </div>
                <div class="col-md-2">
                    <label class="form-label">العميل</label>
                    <select class="form-select" name="customer_id">
                        <option value="">الكل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" name="status">
                        <option value="">الكل</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>بحث
                    </button>
                    <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quotations Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم العرض</th>
                            <th>العميل</th>
                            <th>التاريخ</th>
                            <th>صالح حتى</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            <tr
                                class="{{ $quotation->isExpired() && !in_array($quotation->status->value, ['converted', 'rejected']) ? 'table-warning' : '' }}">
                                <td>
                                    <a href="{{ route('quotations.show', $quotation) }}" class="fw-bold text-decoration-none">
                                        {{ $quotation->quotation_number }}
                                    </a>
                                </td>
                                <td>{{ $quotation->customer?->name ?? '-' }}</td>
                                <td>{{ $quotation->quotation_date?->format('Y-m-d') }}</td>
                                <td>
                                    {{ $quotation->valid_until?->format('Y-m-d') }}
                                    @if($quotation->isExpired() && !in_array($quotation->status->value, ['converted', 'rejected']))
                                        <span class="badge bg-warning text-dark">منتهي</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ number_format($quotation->total, 2) }} ج.م</td>
                                <td>
                                    <span class="badge bg-{{ $quotation->status->color() }}">
                                        <i class="bi {{ $quotation->status->icon() }} me-1"></i>
                                        {{ $quotation->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-primary"
                                            title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($quotation->status->canEdit())
                                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-secondary"
                                                title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($quotation->status->canConvert())
                                            <form action="{{ route('quotations.convert', $quotation) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="تحويل لأمر بيع"
                                                    onclick="return confirm('تحويل العرض إلى أمر بيع؟')">
                                                    <i class="bi bi-arrow-right-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                                    لا توجد عروض أسعار
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($quotations->hasPages())
            <div class="card-footer">
                {{ $quotations->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection