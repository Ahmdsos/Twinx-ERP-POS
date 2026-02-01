@extends('layouts.app')

@section('title', 'طباعة ملصق - ' . $product->name)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-upc-scan me-2"></i>
                            طباعة ملصق الباركود
                        </h5>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right"></i> رجوع للمنتج
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">معلومات المنتج</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>الاسم:</td>
                                        <td><strong>{{ $product->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>SKU:</td>
                                        <td>{{ $product->sku }}</td>
                                    </tr>
                                    <tr>
                                        <td>الباركود:</td>
                                        <td>{{ $label['barcode'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>السعر:</td>
                                        <td class="text-primary fw-bold">{{ $label['price'] }} ج.م</td>
                                    </tr>
                                </table>

                                <hr>

                                <h6 class="text-muted">خيارات الطباعة</h6>
                                <form action="{{ route('barcode.print', $product) }}" method="get" class="mb-3">
                                    <div class="mb-3">
                                        <label class="form-label">عدد النسخ:</label>
                                        <input type="number" name="copies" value="{{ $copies }}" min="1" max="100"
                                            class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">حجم الملصق:</label>
                                        <select name="size" class="form-select">
                                            <option value="small" {{ $labelSize == 'small' ? 'selected' : '' }}>صغير (40mm ×
                                                25mm)</option>
                                            <option value="standard" {{ $labelSize == 'standard' ? 'selected' : '' }}>عادي
                                                (50mm × 30mm)</option>
                                            <option value="large" {{ $labelSize == 'large' ? 'selected' : '' }}>كبير (60mm ×
                                                40mm)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-secondary">تحديث</button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">معاينة الملصق</h6>
                                <div class="border rounded p-4 bg-light text-center">
                                    <div class="label-preview"
                                        style="display: inline-block; background: #fff; border: 2px dashed #ccc; padding: 15px; text-align: center;">
                                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">
                                            {{ $product->name }}</div>
                                        <div class="my-2">{!! $label['barcode_svg'] !!}</div>
                                        <div style="font-size: 14px; font-weight: bold; color: #2563eb;">
                                            {{ $label['price'] }} ج.م</div>
                                        <div style="font-size: 9px; color: #666;">{{ $product->sku }}</div>
                                    </div>
                                </div>

                                <div class="mt-4 d-grid gap-2">
                                    <a href="{{ route('barcode.print', [$product, 'copies' => $copies]) }}" target="_blank"
                                        class="btn btn-primary btn-lg">
                                        <i class="bi bi-printer me-2"></i>
                                        طباعة {{ $copies }} {{ $copies == 1 ? 'ملصق' : 'ملصقات' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection