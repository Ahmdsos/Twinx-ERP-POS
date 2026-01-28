@extends('layouts.app')

@section('title', $customer->name . ' - Twinx ERP')
@section('page-title', 'تفاصيل العميل')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Customer Info Card -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>معلومات العميل</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('customers.edit', $customer) }}">
                                    <i class="bi bi-pencil me-2"></i>تعديل
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-trash me-2"></i>حذف
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ mb_substr($customer->name, 0, 1) }}
                        </div>
                        <h4 class="mb-1">{{ $customer->name }}</h4>
                        <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                            {{ $customer->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </div>

                    <hr>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 40%;">الكود</td>
                            <td><strong>{{ $customer->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">البريد الإلكتروني</td>
                            <td>{{ $customer->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الهاتف</td>
                            <td dir="ltr" class="text-end">{{ $customer->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">العنوان</td>
                            <td>{{ $customer->billing_address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الرقم الضريبي</td>
                            <td>{{ $customer->tax_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">شروط الدفع</td>
                            <td>{{ $customer->payment_terms }} يوم</td>
                        </tr>
                        <tr>
                            <td class="text-muted">حد الائتمان</td>
                            <td>{{ number_format($customer->credit_limit, 2) }} ج.م</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="col-lg-8">
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">إجمالي المبيعات</p>
                                    <h4 class="mb-0">{{ number_format($totalSales ?? 0, 2) }}</h4>
                                </div>
                                <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">الرصيد المستحق</p>
                                    <h4 class="mb-0">{{ number_format($balance ?? 0, 2) }}</h4>
                                </div>
                                <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">إجمالي المدفوع</p>
                                    <h4 class="mb-0">{{ number_format($totalPaid ?? 0, 2) }}</h4>
                                </div>
                                <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>آخر الفواتير</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>التاريخ</th>
                                    <th>الإجمالي</th>
                                    <th>المستحق</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices ?? [] as $invoice)
                                    <tr>
                                        <td><a href="#">{{ $invoice->number }}</a></td>
                                        <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                        <td>{{ number_format($invoice->total, 2) }}</td>
                                        <td>{{ number_format($invoice->balance_due, 2) }}</td>
                                        <td>
                                            @if($invoice->status === 'paid')
                                                <span class="badge bg-success">مدفوعة</span>
                                            @elseif($invoice->status === 'partial')
                                                <span class="badge bg-warning">جزئي</span>
                                            @else
                                                <span class="badge bg-danger">معلقة</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد فواتير حتى الآن
                                        </td>
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