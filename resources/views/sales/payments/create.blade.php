@extends('layouts.app')

@section('title', 'تسجيل تحصيل جديد')

@section('content')
    <div class="container p-0" style="max-width: 900px;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-gradient-green shadow-neon-green rounded-circle text-white">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-white mb-0">تسجيل تحصيل جديد</h2>
                    <p class="text-gray-400 mb-0 x-small">إثبات استلام نقدية أو شيك من عميل</p>
                </div>
            </div>
            <a href="{{ route('customer-payments.index') }}" class="btn btn-glass-outline px-4 fw-bold rounded-pill">
                <i class="bi bi-arrow-right me-2"></i> السجل
            </a>
        </div>

        <div class="glass-panel p-5 position-relative overflow-hidden">
            <div class="absolute-glow top-0 start-0 bg-green-500/10"></div>

            <form action="{{ route('customer-payments.store') }}" method="POST">
                @csrf

                <div class="row g-4 position-relative z-1">
                    <!-- Customer Selection -->
                    <div class="col-md-12">
                        <label class="section-label mb-2 text-green-400">العميل <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select glass-select ps-4 py-3" required autofocus>
                            <option value="" class="text-gray-500">-- اختر العميل --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                                    class="bg-gray-900 text-white">
                                    {{ $customer->name }} • الرصيد: {{ number_format($customer->balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <hr class="border-white/10 my-2">
                    </div>

                    <!-- Amount & Date -->
                    <div class="col-md-6">
                        <label class="section-label mb-2">المبلغ المحصل <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text glass-input border-end-0 text-success fw-bold">EGP</span>
                            <input type="number" step="0.01" name="amount"
                                class="form-control glass-input border-start-0 ps-0 fw-bold fs-5 text-white"
                                value="{{ old('amount') }}" required placeholder="0.00">
                        </div>
                        @error('amount') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="section-label mb-2">تاريخ التحصيل <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control glass-input py-3"
                            value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        @error('payment_date') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-6">
                        <label class="section-label mb-2">طريقة الدفع <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select glass-select py-3" required>
                            <option value="cash" class="bg-gray-900">نقدي (Cash)</option>
                            <option value="bank_transfer" class="bg-gray-900">تحويل بنكي</option>
                            <option value="check" class="bg-gray-900">شيك</option>
                            <option value="card" class="bg-gray-900">بطاقة ائتمان</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="section-label mb-2">الخزنة / الحساب <span class="text-danger">*</span></label>
                        <select name="payment_account_id" class="form-select glass-select py-3" required>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" class="bg-gray-900">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reference & Notes -->
                    <div class="col-md-12">
                        <label class="section-label mb-2">رقم مرجعي</label>
                        <input type="text" name="reference_number" class="form-control glass-input py-3"
                            placeholder="رقم الشيك / التحويل / الإيصال اليدوي" value="{{ old('reference_number') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="section-label mb-2">ملاحظات</label>
                        <textarea name="notes" class="form-control glass-textarea" rows="3"
                            placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top border-white/10">
                        <button type="submit"
                            class="btn btn-action-success w-100 py-3 fw-bold shadow-neon-green hover-scale">
                            <i class="bi bi-check-circle-fill me-2"></i> تأكيد وحفظ التحصيل
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
        }

        .glass-select,
        .glass-input,
        .glass-textarea {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .glass-select:focus,
        .glass-input:focus,
        .glass-textarea:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: #22c55e !important;
            /* Green for payments */
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        .btn-action-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-action-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .shadow-neon-green {
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.3);
        }

        .bg-gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .absolute-glow {
            position: absolute;
            width: 150px;
            height: 150px;
            filter: blur(40px);
            pointer-events: none;
        }
    </style>
@endsection