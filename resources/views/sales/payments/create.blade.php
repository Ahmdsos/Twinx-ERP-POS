@extends('layouts.app')

@section('title', 'تسجيل دفعة - Twinx ERP')
@section('page-title', 'تسجيل دفعة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customer-payments.index') }}">المدفوعات</a></li>
    <li class="breadcrumb-item active">دفعة جديدة</li>
@endsection

@section('content')
    <form action="{{ route('customer-payments.store') }}" method="POST" id="payment-form">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Payment Header -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash me-2"></i>معلومات الدفعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">العميل <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" name="customer_id"
                                    id="customer_id" required {{ $customer ? 'disabled' : '' }}>
                                    <option value="">اختر العميل...</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}" {{ ($customer?->id ?? old('customer_id')) == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->code }} - {{ $cust->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($customer)
                                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                @endif
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                    name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                        name="amount" id="payment_amount" step="0.01" min="0.01"
                                        value="{{ old('amount', $invoice?->balance_due) }}" required>
                                    <span class="input-group-text">ج.م</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror"
                                    name="payment_method" required>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>نقداً
                                    </option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>شيك
                                    </option>
                                    <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>بطاقة ائتمان</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">حساب الاستلام <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_account_id') is-invalid @enderror"
                                    name="payment_account_id" required>
                                    <option value="">اختر الحساب...</option>
                                    @foreach($paymentAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">رقم المرجع</label>
                                <input type="text" class="form-control" name="reference" value="{{ old('reference') }}"
                                    placeholder="رقم الشيك / رقم التحويل">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ملاحظات</label>
                                <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Allocation -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>توزيع على الفواتير</h5>
                        <small class="text-muted">اختياري - يمكن توزيع المبلغ على أكثر من فاتورة</small>
                    </div>
                    <div class="card-body p-0">
                        @if($pendingInvoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">الفاتورة</th>
                                            <th>تاريخ الاستحقاق</th>
                                            <th>المتبقي</th>
                                            <th>المبلغ المخصص</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoices-body">
                                        @foreach($pendingInvoices as $index => $inv)
                                            <tr class="{{ $inv->isOverdue() ? 'table-danger' : '' }}">
                                                <td>
                                                    <strong>{{ $inv->invoice_number }}</strong>
                                                    @if($inv->isOverdue())
                                                        <br>
                                                        <small class="text-danger">متأخرة {{ $inv->getDaysOverdue() }} يوم</small>
                                                    @endif
                                                    <input type="hidden" name="allocations[{{ $index }}][invoice_id]"
                                                        value="{{ $inv->id }}">
                                                </td>
                                                <td>{{ $inv->due_date?->format('Y-m-d') }}</td>
                                                <td class="text-danger">{{ number_format($inv->balance_due, 2) }} ج.م</td>
                                                <td>
                                                    <input type="number" class="form-control allocation-amount"
                                                        name="allocations[{{ $index }}][amount]" step="0.01" min="0"
                                                        max="{{ $inv->balance_due }}" data-balance="{{ $inv->balance_due }}"
                                                        value="{{ $invoice && $invoice->id == $inv->id ? $inv->balance_due : 0 }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-start"><strong>إجمالي التوزيع</strong></td>
                                            <td><strong id="total-allocation">0.00</strong> ج.م</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                @if($customer)
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                    لا توجد فواتير معلقة لهذا العميل
                                @else
                                    <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                                    اختر العميل أولاً لعرض الفواتير المعلقة
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                @if($customer)
                    <!-- Customer Info -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-person me-2"></i>معلومات العميل</h6>
                        </div>
                        <div class="card-body">
                            <h6>{{ $customer->name }}</h6>
                            <p class="mb-1 text-muted">{{ $customer->code }}</p>
                            @if($customer->phone)
                                <p class="mb-1"><i class="bi bi-phone me-1"></i>{{ $customer->phone }}</p>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>الرصيد الحالي:</span>
                                <strong class="{{ ($customer->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($customer->balance ?? 0, 2) }} ج.م
                                </strong>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>تسجيل الدفعة
                            </button>
                            <a href="{{ route('customer-payments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x me-2"></i>إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Calculate total allocation
            function calculateTotalAllocation() {
                let total = 0;
                document.querySelectorAll('.allocation-amount').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('total-allocation').textContent = total.toFixed(2);
            }

            // Listen for allocation changes
            document.querySelectorAll('.allocation-amount').forEach(input => {
                input.addEventListener('input', calculateTotalAllocation);
            });

            // Initial calculation
            calculateTotalAllocation();

            // Customer change - reload page
            const customerSelect = document.getElementById('customer_id');
            if (customerSelect && !customerSelect.disabled) {
                customerSelect.addEventListener('change', function () {
                    if (this.value) {
                        window.location.href = '{{ route("customer-payments.create") }}?customer_id=' + this.value;
                    }
                });
            }
        });
    </script>
@endpush