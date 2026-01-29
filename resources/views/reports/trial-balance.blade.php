@extends('layouts.app')

@section('title', 'ميزان المراجعة')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">ميزان المراجعة</h1>
                <p class="text-muted mb-0">Trial Balance</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>
                    طباعة
                </button>
            </div>
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

        <!-- Report -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">ميزان المراجعة حتى {{ $asOfDate->format('Y-m-d') }}</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>كود الحساب</th>
                            <th>اسم الحساب</th>
                            <th>النوع</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trialBalance as $account)
                            <tr>
                                <td><code>{{ $account['code'] }}</code></td>
                                <td>
                                    <a href="{{ route('accounts.show', $account['id']) }}" class="text-decoration-none">
                                        {{ $account['name'] }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $account['type_label'] }}</span>
                                </td>
                                <td class="text-end">
                                    @if($account['debit'] > 0)
                                        <span class="text-success">{{ number_format($account['debit'], 2) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($account['credit'] > 0)
                                        <span class="text-danger">{{ number_format($account['credit'], 2) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    لا توجد بيانات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">الإجمالي:</td>
                            <td class="text-end">{{ number_format($totals['total_debit'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total_credit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">الفرق:</td>
                            <td colspan="2" class="text-center">
                                @if(abs($totals['difference']) < 0.01)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>متوازن</span>
                                @else
                                    <span class="badge bg-danger">{{ number_format($totals['difference'], 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
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

                .container-fluid {
                    padding: 0 !important;
                }
            }
        </style>
    @endpush
@endsection