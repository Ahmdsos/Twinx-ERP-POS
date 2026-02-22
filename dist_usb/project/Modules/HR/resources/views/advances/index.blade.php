@extends('layouts.app')

@section('title', 'سلف العاملين')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-heading">
                <i class="bi bi-cash-coin me-2 text-primary"></i> سلف العاملين (Advances)
            </h4>
            <a href="{{ route('hr.advances.create') }}" class="btn btn-primary fw-bold">
                <i class="bi bi-plus-lg me-1"></i> طلب سلفة جديدة
            </a>
        </div>

        <!-- Filters -->
        <div class="card glass-card border-0 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('hr.advances.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">الموظف</label>
                        <select name="employee_id" class="form-select bg-transparent text-body">
                            <option value="">كافة الموظفين</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->first_name }} {{ $emp->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">الحالة</label>
                        <select name="status" class="form-select bg-transparent text-body">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق (Pending)
                            </option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد (Approved)
                            </option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوع (Paid)</option>
                            <option value="deducted" {{ request('status') == 'deducted' ? 'selected' : '' }}>مخصوم (Deducted)
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card glass-card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-height: 200px;">
                        <thead class="bg-surface bg-opacity-10 text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">{{ __('Employee') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Repayment Month') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($advances as $advance)
                                <tr>
                                    <td class="ps-4 fw-bold text-body">
                                        {{ $advance->employee->first_name }} {{ $advance->employee->last_name }}
                                    </td>
                                    <td class="fw-bold text-primary">{{ number_format($advance->amount, 2) }}</td>
                                    <td class="text-muted">{{ $advance->request_date->format('Y-m-d') }}</td>
                                    <td class="text-body">{{ $advance->repayment_month }}/{{ $advance->repayment_year }}</td>
                                    <td>
                                        @if($advance->status == 'pending')
                                            <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                        @elseif($advance->status == 'approved')
                                            <span class="badge bg-info text-dark">{{ __('Approved') }}</span>
                                        @elseif($advance->status == 'paid')
                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                        @elseif($advance->status == 'deducted')
                                            <span class="badge bg-secondary">{{ __('Deducted') }}</span>
                                        @elseif($advance->status == 'rejected')
                                            <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($advance->status == 'pending')
                                                <form action="{{ route('hr.advances.approve', $advance->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-square btn-success text-white"
                                                        data-bs-toggle="tooltip" title="{{ __('Approve') }}">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($advance->status == 'approved')
                                                <form action="{{ route('hr.advances.pay', $advance->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-square btn-primary text-white"
                                                        data-bs-toggle="tooltip" title="{{ __('Pay') }}">
                                                        <i class="bi bi-cash"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('hr.advances.show', $advance->id) }}"
                                                class="btn btn-sm btn-square btn-secondary bg-transparent text-muted border-dashed"
                                                data-bs-toggle="tooltip" title="{{ __('Details') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">{{ __('No records found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4">
            {{ $advances->links() }}
        </div>
    </div>
@endsection