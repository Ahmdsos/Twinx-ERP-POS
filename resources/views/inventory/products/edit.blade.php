@extends('layouts.app')

@section('title', 'تعديل منتج: ' . $product->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Header -->
                <div class="mb-5 d-flex align-items-center gap-3">
                    <a href="{{ route('products.index') }}"
                        class="btn btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <div>
                        <h3 class="fw-bold text-white mb-0 tracking-wide">تعديل المنتج</h3>
                        <p class="text-gray-400 mb-0 small">تحديث بيانات <span
                                class="text-purple-400 fw-bold">{{ $product->name }}</span> ({{ $product->sku }})</p>
                    </div>
                </div>

                @include('inventory.products.form')
            </div>
        </div>
    </div>
@endsection