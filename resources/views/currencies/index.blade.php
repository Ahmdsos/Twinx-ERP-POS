@extends('layouts.app')

@section('title', 'إدارة العملات')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">إدارة العملات</h1>
                <p class="text-muted mb-0">Multi-Currency Management</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
                <i class="bi bi-plus-circle me-1"></i>
                إضافة عملة
            </button>
        </div>

        <!-- Currencies Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الرمز</th>
                            <th class="text-center">سعر الصرف</th>
                            <th class="text-center">خانات عشرية</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                            <tr>
                                <td><code>{{ $currency->code }}</code></td>
                                <td>
                                    {{ $currency->name }}
                                    @if($currency->is_default)
                                        <span class="badge bg-primary ms-1">افتراضي</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary">{{ $currency->symbol }}</span></td>
                                <td class="text-center">{{ number_format($currency->exchange_rate, 6) }}</td>
                                <td class="text-center">{{ $currency->decimal_places }}</td>
                                <td class="text-center">
                                    @if($currency->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary"
                                            onclick="editCurrency({{ json_encode($currency) }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if(!$currency->is_default)
                                            <form action="{{ route('currencies.set-default', $currency) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="تعيين كافتراضي">
                                                    <i class="bi bi-star"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('currencies.destroy', $currency) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد عملات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Currency Converter -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>محول العملات</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" id="convertAmount" class="form-control" value="100" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">من عملة</label>
                        <select id="fromCurrency" class="form-select">
                            @foreach($currencies as $c)
                                <option value="{{ $c->id }}" {{ $c->is_default ? 'selected' : '' }}>{{ $c->code }} -
                                    {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى عملة</label>
                        <select id="toCurrency" class="form-select">
                            @foreach($currencies as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="convertCurrency()">
                            <i class="bi bi-arrow-left-right me-1"></i>
                            تحويل
                        </button>
                    </div>
                </div>
                <div id="convertResult" class="alert alert-info mt-3 d-none"></div>
            </div>
        </div>
    </div>

    <!-- Add Currency Modal -->
    <div class="modal fade" id="addCurrencyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('currencies.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة عملة جديدة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">كود العملة (3 أحرف)</label>
                            <input type="text" name="code" class="form-control" required maxlength="3" placeholder="USD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">اسم العملة</label>
                            <input type="text" name="name" class="form-control" required placeholder="دولار أمريكي">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الرمز</label>
                            <input type="text" name="symbol" class="form-control" required placeholder="$">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">سعر الصرف (مقابل العملة الافتراضية)</label>
                            <input type="number" name="exchange_rate" class="form-control" required step="0.000001"
                                value="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">خانات عشرية</label>
                            <input type="number" name="decimal_places" class="form-control" required value="2" min="0"
                                max="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Currency Modal -->
    <div class="modal fade" id="editCurrencyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCurrencyForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">تعديل العملة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اسم العملة</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الرمز</label>
                            <input type="text" name="symbol" id="editSymbol" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">سعر الصرف</label>
                            <input type="number" name="exchange_rate" id="editRate" class="form-control" required
                                step="0.000001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">خانات عشرية</label>
                            <input type="number" name="decimal_places" id="editDecimals" class="form-control" required
                                min="0" max="6">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
                            <label class="form-check-label" for="editActive">نشط</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editCurrency(currency) {
            document.getElementById('editCurrencyForm').action = `/currencies/${currency.id}`;
            document.getElementById('editName').value = currency.name;
            document.getElementById('editSymbol').value = currency.symbol;
            document.getElementById('editRate').value = currency.exchange_rate;
            document.getElementById('editDecimals').value = currency.decimal_places;
            document.getElementById('editActive').checked = currency.is_active;
            new bootstrap.Modal(document.getElementById('editCurrencyModal')).show();
        }

        function convertCurrency() {
            const amount = document.getElementById('convertAmount').value;
            const from = document.getElementById('fromCurrency').value;
            const to = document.getElementById('toCurrency').value;

            fetch('{{ route("currencies.convert") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ amount, from_currency_id: from, to_currency_id: to })
            })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('convertResult').classList.remove('d-none');
                    document.getElementById('convertResult').innerHTML = `${data.original} = <strong>${data.converted}</strong>`;
                });
        }
    </script>
@endpush