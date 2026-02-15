@extends('layouts.app')

@section('title', 'سند صرف نقدية')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ route('treasury.store-payment') }}" method="POST">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-heading mb-1 text-danger">
                            <i class="bi bi-arrow-up-circle me-2"></i> سند صرف نقدية (Out)
                        </h4>
                        <div class="text-muted small">تسجيل مصروفات أو دفعات موردين</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('treasury.index') }}" class="btn btn-glass-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ السند
                        </button>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <div class="row g-4">
                        <!-- Date & Amount -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">تاريخ المعاملة <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}"
                                class="form-control bg-transparent text-body border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">{{ __('Amount') }}<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="amount"
                                    class="form-control bg-transparent text-body border-secondary fw-bold fs-5 text-end"
                                    placeholder="0.00" required>
                                <span class="input-group-text bg-transparent text-body border-secondary">{{ __('EGP') }}</span>
                            </div>
                        </div>

                        <hr class="border-secondary border-opacity-25 my-4">

                        <!-- Accounts -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">من خزينة / بنك (الدائن) <span
                                    class="text-danger">*</span></label>
                            <select name="treasury_account_id"
                                class="form-select bg-transparent text-body border-secondary" required>
                                <option value="">-- اختر حساب النقدية --</option>
                                @foreach($treasuryAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}
                                        ({{ number_format($account->balance, 2) }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-danger opacity-75 small">الحساب الذي سيتم خصم المبلغ منه.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-300">إلى حساب (المدين) <span
                                    class="text-danger">*</span></label>
                            <select name="counter_account_id" class="form-select bg-transparent text-body border-secondary"
                                required>
                                <option value="">-- اختر الحساب المستفيد --</option>
                                @foreach($counterAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">مصروف، مورد، أو أي حساب آخر.</div>
                        </div>

                        <!-- Details -->
                        <div class="col-md-12">
                            <label class="form-label text-gray-300">البيان / الوصف</label>
                            <textarea name="description" class="form-control bg-transparent text-body border-secondary"
                                rows="2" placeholder="اكتب تفاصيل عملية الصرف هنا..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-300">رقم مرجعي (اختياري)</label>
                            <input type="text" name="reference"
                                class="form-control bg-transparent text-body border-secondary"
                                placeholder="رقم إيصال ورقي مثلاً">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        

        .btn-glass-outline {
            background: var(--btn-glass-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(30, 41, 59, 0.9);
            border-color: #ef4444;
            /* Red for Payment */
            box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.25);
            color: var(--text-primary);
        }

        option {
            background-color: var(--input-bg);
            color: var(--text-primary);
        }
    </style>
@endsection