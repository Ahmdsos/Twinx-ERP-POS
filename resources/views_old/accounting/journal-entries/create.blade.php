@extends('layouts.app')

@section('title', 'إنشاء قيد يومية')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h1 class="h3 mb-0">إنشاء قيد يومية جديد</h1>
                    <p class="text-muted mb-0">أدخل بيانات القيد المحاسبي</p>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('journal-entries.store') }}" method="POST" id="journalForm">
                @csrf

                <!-- Header Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">بيانات القيد</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">تاريخ القيد <span class="text-danger">*</span></label>
                                <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror"
                                    value="{{ old('entry_date', date('Y-m-d')) }}" required>
                                @error('entry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">المرجع</label>
                                <input type="text" name="reference" class="form-control"
                                    value="{{ old('reference') }}" placeholder="رقم المستند / الفاتورة">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الوصف</label>
                                <input type="text" name="description" class="form-control"
                                    value="{{ old('description') }}" placeholder="وصف مختصر للقيد">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lines Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">سطور القيد</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addLine()">
                            <i class="bi bi-plus-lg me-1"></i>
                            إضافة سطر
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="linesTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 35%;">الحساب <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">مدين</th>
                                        <th style="width: 20%;">دائن</th>
                                        <th style="width: 20%;">البيان</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="linesBody">
                                    <!-- Dynamic lines will be added here -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td class="text-end">الإجمالي:</td>
                                        <td class="text-end" id="totalDebit">0.00</td>
                                        <td class="text-end" id="totalCredit">0.00</td>
                                        <td colspan="2">
                                            <span id="balanceStatus" class="badge bg-warning">غير متوازن</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg me-1"></i>
                        حفظ القيد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Account data for JavaScript -->
<script>
const accounts = @json($accounts);
let lineIndex = 0;

function getAccountOptions() {
    let options = '<option value="">اختر الحساب...</option>';
    accounts.forEach(acc => {
        options += `<option value="${acc.id}">${acc.code} - ${acc.name}</option>`;
    });
    return options;
}

function addLine(accountId = '', debit = '', credit = '', description = '') {
    const row = document.createElement('tr');
    row.id = `line_${lineIndex}`;
    row.innerHTML = `
        <td>
            <select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm" required>
                ${getAccountOptions()}
            </select>
        </td>
        <td>
            <input type="number" name="lines[${lineIndex}][debit]" class="form-control form-control-sm text-end debit-input"
                step="0.01" min="0" value="${debit}" onchange="updateTotals()" placeholder="0.00">
        </td>
        <td>
            <input type="number" name="lines[${lineIndex}][credit]" class="form-control form-control-sm text-end credit-input"
                step="0.01" min="0" value="${credit}" onchange="updateTotals()" placeholder="0.00">
        </td>
        <td>
            <input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm"
                value="${description}" placeholder="ملاحظات">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(${lineIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    document.getElementById('linesBody').appendChild(row);
    
    // Set account if provided
    if (accountId) {
        row.querySelector('select').value = accountId;
    }
    
    lineIndex++;
    updateTotals();
}

function removeLine(index) {
    const row = document.getElementById(`line_${index}`);
    if (row) {
        row.remove();
        updateTotals();
    }
}

function updateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    
    document.querySelectorAll('.debit-input').forEach(input => {
        totalDebit += parseFloat(input.value) || 0;
    });
    
    document.querySelectorAll('.credit-input').forEach(input => {
        totalCredit += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
    
    const balanceStatus = document.getElementById('balanceStatus');
    const submitBtn = document.getElementById('submitBtn');
    
    if (Math.abs(totalDebit - totalCredit) < 0.01 && totalDebit > 0) {
        balanceStatus.className = 'badge bg-success';
        balanceStatus.textContent = 'متوازن ✓';
        submitBtn.disabled = false;
    } else {
        balanceStatus.className = 'badge bg-danger';
        balanceStatus.textContent = `فرق: ${Math.abs(totalDebit - totalCredit).toFixed(2)}`;
        submitBtn.disabled = true;
    }
}

// Initialize with 2 empty lines
document.addEventListener('DOMContentLoaded', function() {
    addLine();
    addLine();
});
</script>
@endsection
