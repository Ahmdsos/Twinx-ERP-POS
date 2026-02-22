@extends('layouts.app')

@section('title', 'تسجيل مرتجع مشتريات')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase-returns.index') }}"
                    class="btn btn-outline-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;"><i
                        class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">تسجيل مرتجع جديد</h2>
                    <p class="text-gray-400 mb-0 x-small">إرجاع أصناف لمورد (Debit Note)</p>
                </div>
            </div>
            <button type="submit" form="returnForm"
                class="btn btn-action-orange fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-save"></i> حفظ الإشعار
            </button>
        </div>

        <form action="{{ route('purchase-returns.store') }}" method="POST" id="returnForm">
            @csrf

            <div class="row g-4">
                <!-- Selector -->
                <div class="col-md-4">
                    <div class="glass-panel p-4 mb-4">
                        <label class="form-label text-gray-400 small fw-bold">اختر الفاتورة الأصلية</label>
                        <select name="invoice_id" class="form-select form-select-dark focus-ring-orange"
                            onchange="window.location.href='{{ route('purchase-returns.create') }}?invoice_id=' + this.value">
                            <option value="">-- بحث برقم الفاتورة --</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}" {{ request('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                    {{ $invoice->invoice_number }} - {{ $invoice->supplier->name }}
                                    ({{ $invoice->invoice_date->format('Y-m-d') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedInvoice)
                        <div class="glass-panel p-4">
                            <h6 class="text-heading fw-bold mb-3 border-bottom border-secondary border-opacity-10-5 pb-2">بيانات المرجوع</h6>

                            <div class="mb-3">
                                <label class="form-label text-gray-400 x-small fw-bold">{{ __('Return Date') }}</label>
                                <input type="date" name="return_date" class="form-control form-control-dark focus-ring-orange"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-gray-400 x-small fw-bold">ملاحظات / سبب الإرجاع</label>
                                <textarea name="notes" class="form-control form-control-dark focus-ring-orange"
                                    rows="3"></textarea>
                            </div>

                            <div
                                class="alert bg-orange-500 bg-opacity-10 border-orange-500 border-opacity-20 text-orange-300 small">
                                <i class="bi bi-info-circle me-1"></i> سيتم خصم قيمة المرتجع من رصيد المورد.
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Items Table -->
                <div class="col-md-8">
                    @if($selectedInvoice)
                        <div class="glass-panel p-4 h-100">
                            <h5 class="text-heading fw-bold mb-4"><i class="bi bi-basket me-2"></i>أصناف الفاتورة (حدد الكمية
                                المرتجعة)</h5>

                            <div class="table-responsive">
                                <table class="table table-dark-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Product') }}</th>
                                            <th class="text-center">الكمية المشراة</th>
                                            <th class="text-center">{{ __('Unit Price') }}</th>
                                            <th class="text-center" style="width: 150px;">كمية المرتجع</th>
                                            <th class="text-end">قيمة المرتجع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedInvoice->lines as $index => $line)
                                            <tr>
                                                <td>
                                                    <h6 class="text-heading mb-0">{{ $line->description }}</h6>
                                                    <input type="hidden" name="items[{{ $index }}][product_id]"
                                                        value="{{ $line->product_id }}">
                                                    <input type="hidden" name="items[{{ $index }}][price]"
                                                        value="{{ $line->unit_price }}" class="return-price">
                                                </td>
                                                <td class="text-center text-gray-400">{{ $line->quantity }}</td>
                                                <td class="text-center text-gray-400">{{ number_format($line->unit_price, 2) }}</td>
                                                <td>
                                                    <input type="number" step="0.01" name="items[{{ $index }}][quantity]"
                                                        class="form-control form-control-dark text-center return-qty"
                                                        max="{{ $line->quantity }}" value="0" oninput="calcTotal()">
                                                </td>
                                                <td class="text-end fw-bold text-orange-300 total-cell">0.00</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end text-gray-400">{{ __('Total') }}</td>
                                            <td class="text-end text-body fw-bold fs-5" id="grandTotal">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @else
                        <div
                            class="h-100 d-flex flex-column align-items-center justify-content-center opacity-50 border border-dashed border-gray-700 rounded-4">
                            <i class="bi bi-arrow-left-square fs-1 text-gray-500 mb-3"></i>
                            <h5 class="text-gray-400">الرجاء اختيار فاتورة لعرض أصنافها</h5>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <script>
        function calcTotal() {
            let grand = 0;
            document.querySelectorAll('tbody tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.return-qty').value) || 0;
                const price = parseFloat(row.querySelector('.return-price').value) || 0;
                const total = qty * price;

                row.querySelector('.total-cell').innerText = total.toFixed(2);
                grand += total;
            });
            document.getElementById('grandTotal').innerText = grand.toFixed(2);
        }
    </script>

    <style>
        .btn-action-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border: none;
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 10px;
        }

        .form-control-dark,
        .form-select-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--btn-glass-border); !important;
            color: var(--text-primary); !important;
        }

        .focus-ring-orange:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1) !important;
        }

        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-secondary);
        }
    </style>
@endsection