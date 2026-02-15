@extends('layouts.app')

@section('title', 'تعديل بيانات المورد')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-light btn-sm rounded-circle shadow-sm"
                    style="width: 32px; height: 32px;"><i class="bi bi-arrow-right"></i></a>
                <div>
                    <h2 class="fw-bold text-heading mb-0">تعديل: {{ $supplier->name }}</h2>
                    <span
                        class="badge bg-slate-800 text-cyan-400 border border-secondary border-opacity-10-10 font-monospace">{{ $supplier->code }}</span>
                </div>
            </div>
            <button type="submit" form="editForm"
                class="btn btn-action-cyan fw-bold shadow-lg d-flex align-items-center gap-2">
                <i class="bi bi-check-lg"></i> حفظ التعديلات
            </button>
        </div>

        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            @include('purchasing.suppliers.form')
        </form>
    </div>

    <style>
        .btn-action-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-action-cyan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(6, 182, 212, 0.4);
        }
    </style>
@endsection