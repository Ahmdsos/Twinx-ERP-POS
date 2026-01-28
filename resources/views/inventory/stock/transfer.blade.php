@extends('layouts.app')

@section('title', 'تحويل مخزون - Twinx ERP')
@section('page-title', 'تحويل المخزون بين المستودعات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.index') }}">حركات المخزون</a></li>
    <li class="breadcrumb-item active">تحويل مخزون</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>تحويل المخزون بين المستودعات</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stock.transfer.process') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Product Selection -->
                            <div class="col-12">
                                <label class="form-label">المنتج <span class="text-danger">*</span></label>
                                <select class="form-select @error('product_id') is-invalid @enderror" name="product_id"
                                    id="product_id" required>
                                    <option value="">اختر المنتج...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->sku }} - {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- From Warehouse -->
                            <div class="col-md-5">
                                <label class="form-label">من مستودع <span class="text-danger">*</span></label>
                                <select class="form-select @error('from_warehouse_id') is-invalid @enderror"
                                    name="from_warehouse_id" id="from_warehouse_id" required>
                                    <option value="">اختر المستودع...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" id="from-stock"></small>
                            </div>

                            <!-- Arrow -->
                            <div class="col-md-2 d-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-right fs-1 text-primary"></i>
                            </div>

                            <!-- To Warehouse -->
                            <div class="col-md-5">
                                <label class="form-label">إلى مستودع <span class="text-danger">*</span></label>
                                <select class="form-select @error('to_warehouse_id') is-invalid @enderror"
                                    name="to_warehouse_id" id="to_warehouse_id" required>
                                    <option value="">اختر المستودع...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" id="to-stock"></small>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                    name="quantity" id="quantity" value="{{ old('quantity') }}" step="0.01" min="0.01"
                                    required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Reference -->
                            <div class="col-md-6">
                                <label class="form-label">المرجع</label>
                                <input type="text" class="form-control @error('reference') is-invalid @enderror"
                                    name="reference" value="{{ old('reference') }}" placeholder="رقم أمر التحويل">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label">ملاحظات</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" name="notes"
                                    rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-check-lg me-1"></i>تأكيد التحويل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('product_id').addEventListener('change', updateStocks);
        document.getElementById('from_warehouse_id').addEventListener('change', updateFromStock);
        document.getElementById('to_warehouse_id').addEventListener('change', updateToStock);

        function updateStocks() {
            updateFromStock();
            updateToStock();
        }

        function updateFromStock() {
            const productId = document.getElementById('product_id').value;
            const warehouseId = document.getElementById('from_warehouse_id').value;

            if (productId && warehouseId) {
                fetch(`/stock/get-stock?product_id=${productId}&warehouse_id=${warehouseId}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('from-stock').innerHTML =
                            `المتاح: <strong class="text-success">${data.available.toFixed(2)}</strong>`;
                    });
            } else {
                document.getElementById('from-stock').innerHTML = '';
            }
        }

        function updateToStock() {
            const productId = document.getElementById('product_id').value;
            const warehouseId = document.getElementById('to_warehouse_id').value;

            if (productId && warehouseId) {
                fetch(`/stock/get-stock?product_id=${productId}&warehouse_id=${warehouseId}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('to-stock').innerHTML =
                            `الرصيد الحالي: <strong>${data.quantity.toFixed(2)}</strong>`;
                    });
            } else {
                document.getElementById('to-stock').innerHTML = '';
            }
        }
    </script>
@endpush