@extends('layouts.app')

@section('title', 'الميزانية العمومية')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">الميزانية العمومية</h1>
                <p class="text-muted mb-0">Balance Sheet</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>
                طباعة
            </button>
        </div>

        <!-- Date Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">حتى تاريخ</label>
                        <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            عرض
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Assets -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-building me-2"></i>الأصول</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>الحساب</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $account)
                                    @if($account['balance'] != 0)
                                        <tr>
                                            <td>
                                                <code class="me-2">{{ $account['code'] }}</code>
                                                <a href="{{ route('accounts.show', $account['id']) }}" class="text-decoration-none">
                                                    {{ $account['name'] }}
                                                </a>
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($account['balance'], 2) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">لا توجد أصول</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-primary">
                                <tr class="fw-bold">
                                    <td>إجمالي الأصول</td>
                                    <td class="text-end">{{ number_format($totalAssets, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Liabilities & Equity -->
            <div class="col-lg-6 mb-4">
                <!-- Liabilities -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>الالتزامات</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>الحساب</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($liabilities as $account)
                                    @if($account['balance'] != 0)
                                        <tr>
                                            <td>
                                                <code class="me-2">{{ $account['code'] }}</code>
                                                {{ $account['name'] }}
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($account['balance'], 2) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">لا توجد التزامات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-warning">
                                <tr class="fw-bold">
                                    <td>إجمالي الالتزامات</td>
                                    <td class="text-end">{{ number_format($totalLiabilities, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Equity -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-bank me-2"></i>حقوق الملكية</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>الحساب</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equity as $account)
                                    @if($account['balance'] != 0)
                                        <tr>
                                            <td>
                                                <code class="me-2">{{ $account['code'] }}</code>
                                                {{ $account['name'] }}
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($account['balance'], 2) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">لا يوجد حقوق ملكية</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-info">
                                <tr class="fw-bold">
                                    <td>إجمالي حقوق الملكية</td>
                                    <td class="text-end">{{ number_format($totalEquity, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Check -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 bg-primary bg-opacity-10 rounded">
                            <h6 class="text-muted mb-1">الأصول</h6>
                            <h3 class="mb-0 text-primary">{{ number_format($totalAssets, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <h3 class="text-muted">=</h3>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded">
                            <h6 class="text-muted mb-1">الالتزامات</h6>
                            <h3 class="mb-0 text-warning">{{ number_format($totalLiabilities, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <h3 class="text-muted">+</h3>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-info bg-opacity-10 rounded">
                            <h6 class="text-muted mb-1">حقوق الملكية</h6>
                            <h3 class="mb-0 text-info">{{ number_format($totalEquity, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    @php $difference = $totalAssets - ($totalLiabilities + $totalEquity); @endphp
                    @if(abs($difference) < 0.01)
                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>الميزانية متوازنة</span>
                    @else
                        <span class="badge bg-danger fs-6">فرق: {{ number_format($difference, 2) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {

                .btn,
                form,
                .sidebar,
                .navbar {
                    display: none !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endpush
@endsection