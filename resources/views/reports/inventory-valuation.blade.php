@extends('layouts.app')

@section('title', 'تقرير تقييم المخزون - Twinx ERP')
@section('page-title', 'تقييم المخزون')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item">التقارير</li>
    <li class="breadcrumb-item active">تقييم المخزون</li>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($totals['total_items']) }}</h3>
                    <small>عدد الأصناف</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($totals['total_quantity']) }}</h3>
                    <small>إجمالي الكمية</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($totals['total_cost_value'], 2) }}</h3>
                    <small>قيمة التكلفة</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($totals['total_potential_profit'], 2) }}</h3>
                    <small>الربح المتوقع</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>تفاصيل المخزون</h5>
            <div>
                <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF()">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="inventoryTable">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-end">سعر التكلفة</th>
                            <th class="text-end">سعر البيع</th>
                            <th class="text-end">قيمة التكلفة</th>
                            <th class="text-end">قيمة البيع</th>
                            <th class="text-end">الربح المتوقع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td class="text-monospace">{{ $row->sku }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->category }}</td>
                                <td class="text-center">{{ number_format($row->stock) }} {{ $row->unit }}</td>
                                <td class="text-end">{{ number_format($row->cost_price, 2) }}</td>
                                <td class="text-end">{{ number_format($row->selling_price, 2) }}</td>
                                <td class="text-end">{{ number_format($row->cost_value, 2) }}</td>
                                <td class="text-end">{{ number_format($row->sale_value, 2) }}</td>
                                <td class="text-end text-success fw-bold">{{ number_format($row->potential_profit, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">لا يوجد مخزون</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">الإجمالي</td>
                            <td class="text-center">{{ number_format($totals['total_quantity']) }}</td>
                            <td colspan="2"></td>
                            <td class="text-end">{{ number_format($totals['total_cost_value'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total_sale_value'], 2) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['total_potential_profit'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection