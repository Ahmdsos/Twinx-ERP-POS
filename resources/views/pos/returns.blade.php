@extends('layouts.app')

@section('title', 'مرتجعات المبيعات - POS')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Search Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h2 class="h4 mb-1 fw-bold">مرتجع مبيعات جديد</h2>
                                <p class="text-muted small mb-0">قم بإدخال رقم الفاتورة للبدء في عملية المرتجع</p>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('pos.returns') }}" method="GET" class="d-flex gap-2">
                                    <input type="text" name="invoice" class="form-control form-control-lg border-2"
                                        placeholder="رقم الفاتورة (مثال: POS-2024...)" value="{{ $invoiceNumber }}">
                                    <button type="submit" class="btn btn-primary btn-lg px-4">
                                        <i class="bi bi-search me-2"></i> بحث
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if($invoice)
                    <div class="row g-4">
                        <!-- Invoice Details -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="card-title mb-0 fw-bold"><i
                                            class="bi bi-info-circle me-2 text-primary"></i>تفاصيل الفاتورة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="text-muted small d-block">رقم الفاتورة</label>
                                        <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small d-block">العميل</label>
                                        <span class="fw-bold">{{ $invoice->customer->name ?? 'عميل عام' }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small d-block">تاريخ الفاتورة</label>
                                        <span class="fw-bold">{{ $invoice->invoice_date->format('Y-m-d H:i') }}</span>
                                    </div>
                                    <hr>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">المجموع الفرعي:</span>
                                        <span class="fw-bold">{{ number_format($invoice->subtotal, 2) }}</span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">الخصم:</span>
                                        <span class="text-danger fw-bold">-
                                            {{ number_format($invoice->discount_amount, 2) }}</span>
                                    </div>
                                    <div class="mb-0 d-flex justify-content-between h5 fw-bold text-primary">
                                        <span>الإجمالي:</span>
                                        <span>{{ number_format($invoice->total, 2) }} ج.م</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">سبب المرتجع</label>
                                        <textarea id="returnReason" class="form-control border-2" rows="3"
                                            placeholder="أدخل سبب الإرجاع..."></textarea>
                                    </div>
                                    <button onclick="processReturn()" id="btnSubmitReturn"
                                        class="btn btn-danger btn-lg w-100 py-3 fw-bold disabled">
                                        <i class="bi bi-arrow-return-left me-2"></i> تأكيد المرتجع
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Items Selection -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h5 class="card-title mb-0 fw-bold"><i
                                            class="bi bi-list-check me-2 text-success"></i>المنتجات المتاحة للإرجاع</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-4">المنتج</th>
                                                    <th class="text-center">الكمية المباعة</th>
                                                    <th class="text-center">السعر</th>
                                                    <th class="text-center" style="width: 150px;">كمية المرتجع</th>
                                                    <th class="text-center">الإجمالي</th>
                                                    <th class="pe-4 text-center">إرجاع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoice->lines as $line)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="fw-bold">{{ $line->product->name }}</div>
                                                            <div class="text-muted small">SKU: {{ $line->product->sku }}</div>
                                                        </td>
                                                        <td class="text-center">{{ $line->quantity }}</td>
                                                        <td class="text-center">{{ number_format($line->unit_price, 2) }}</td>
                                                        <td class="text-center">
                                                            <input type="number"
                                                                class="form-control form-control-sm text-center return-qty"
                                                                max="{{ $line->quantity }}" min="0" value="0"
                                                                data-line-id="{{ $line->id }}" data-price="{{ $line->unit_price }}"
                                                                onchange="updateReturnTotal()">
                                                        </td>
                                                        <td class="text-center return-line-total">0.00</td>
                                                        <td class="pe-4 text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input item-check" type="checkbox"
                                                                    onchange="updateReturnTotal()">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light fw-bold">
                                                <tr>
                                                    <td colspan="4" class="text-end ps-4 py-3">إجمالي قيمة المرتجع المقدرة:</td>
                                                    <td class="text-center text-danger h5 mb-0 py-3" id="overallReturnTotal">
                                                        0.00 ج.م</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($invoiceNumber)
                    <div class="alert alert-warning border-0 shadow-sm p-4 text-center">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                        <h4 class="fw-bold">فاتورة غير موجودة</h4>
                        <p class="mb-0">عذراً، لم نتمكن من العثور على فاتورة بالرقم: <strong>{{ $invoiceNumber }}</strong></p>
                    </div>
                @else
                    <div class="text-center py-5">
                        <img src="https://img.icons8.com/illustrations/external-interface-flaticons-lineal-color-flat-icons/256/external-returns-logistics-flaticons-lineal-color-flat-icons.png"
                            class="mb-4 opacity-50" style="max-width: 200px;">
                        <h5 class="text-muted">الرجاء البحث عن فاتورة للبدء في المرتجع</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function updateReturnTotal() {
            let total = 0;
            let anySelected = false;

            document.querySelectorAll('tbody tr').forEach(row => {
                const check = row.querySelector('.item-check');
                const qtyInput = row.querySelector('.return-qty');
                const lineTotalEl = row.querySelector('.return-line-total');
                const price = parseFloat(qtyInput.dataset.price);
                const qty = parseFloat(qtyInput.value) || 0;

                if (check.checked && qty > 0) {
                    const lineTotal = price * qty;
                    lineTotalEl.textContent = lineTotal.toFixed(2);
                    total += lineTotal;
                    anySelected = true;
                } else {
                    lineTotalEl.textContent = '0.00';
                }
            });

            document.getElementById('overallReturnTotal').textContent = total.toFixed(2) + ' ج.م';
            const btn = document.getElementById('btnSubmitReturn');
            if (anySelected) {
                btn.classList.remove('disabled');
            } else {
                btn.classList.add('disabled');
            }
        }

        function processReturn() {
            const items = [];
            document.querySelectorAll('tbody tr').forEach(row => {
                const check = row.querySelector('.item-check');
                const qtyInput = row.querySelector('.return-qty');
                if (check.checked && (parseFloat(qtyInput.value) || 0) > 0) {
                    items.push({
                        line_id: qtyInput.dataset.lineId,
                        quantity: parseFloat(qtyInput.value)
                    });
                }
            });

            if (items.length === 0) return;

            if (!confirm('هل أنت متأكد من تنفيذ عملية المرتجع؟')) return;

            const data = {
                invoice_id: '{{ $invoice->id ?? "" }}',
                items: items,
                reason: document.getElementById('returnReason').value
            };

            fetch('/pos/sales-return', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert('تم تنفيذ المرتجع بنجاح. رقم القسيمة: ' + res.return_number);
                        window.location.href = '{{ route("pos.index") }}';
                    } else {
                        alert('خطأ: ' + res.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('حدث خطأ أثناء معالجة الطلب');
                });
        }
    </script>
@endsection