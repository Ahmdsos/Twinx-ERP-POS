@extends('layouts.app')

@section('title', 'قيد يومية جديد')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('journal-entries.store') }}" method="POST" id="journalForm">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-white mb-1">قيد يومية جديد (Manual Journal)</h4>
                        <div class="text-white-50 small">تسجيل القيود اليدوية والتسويات</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('journal-entries.index') }}" class="btn btn-glass-outline">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-lg">
                            <i class="bi bi-save me-2"></i> حفظ القيد
                        </button>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <!-- Header Info -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label text-gray-300">تاريخ القيد <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" value="{{ date('Y-m-d') }}"
                                class="form-control bg-transparent text-white border-secondary" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-gray-300">رقم مرجعي</label>
                            <input type="text" name="reference"
                                class="form-control bg-transparent text-white border-secondary" placeholder="Manual Ref">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-300">الوصف / البيان <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="description"
                                class="form-control bg-transparent text-white border-secondary" required>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 mb-4">

                    <!-- Journal Lines -->
                    <div class="table-responsive">
                        <table class="table align-middle text-white mb-0 custom-table" id="linesTable">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width: 40%">الحساب</th>
                                    <th style="width: 25%">الوصف (اختياري)</th>
                                    <th class="text-end" style="width: 15%">مدين (Debit)</th>
                                    <th class="text-end" style="width: 15%">دائن (Credit)</th>
                                    <th class="text-center" style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="linesContainer">
                                <!-- Rows will be added here by JS -->
                            </tbody>
                            <tfoot>
                                <tr class="bg-white bg-opacity-5 font-monospace fw-bold">
                                    <td colspan="2" class="ps-3 text-end py-3">الإجمالي</td>
                                    <td class="text-end py-3 text-info" id="totalDebit">0.00</td>
                                    <td class="text-end py-3 text-info" id="totalCredit">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-center py-2 border-0">
                                        <span id="balanceStatus" class="badge bg-success rounded-pill px-3">متزن</span>
                                        <div id="diffAmount" class="text-danger small mt-1 d-none">الفرق: 0.00</div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-glass-outline btn-sm" onclick="addLine()">
                            <i class="bi bi-plus-lg me-1"></i> إضافة سطر
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Template for JS -->
    <template id="lineTemplate">
        <tr class="line-row">
            <td class="ps-3">
                <select name="lines[INDEX][account_id]"
                    class="form-select bg-transparent text-white border-secondary form-select-sm" required>
                    <option value="">اختر الحساب...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="lines[INDEX][description]"
                    class="form-control bg-transparent text-white border-secondary form-control-sm">
            </td>
            <td>
                <input type="number" step="0.01" name="lines[INDEX][debit]"
                    class="form-control bg-transparent text-white border-secondary form-control-sm text-end debit-input"
                    value="0.00" oninput="calcTotals()">
            </td>
            <td>
                <input type="number" step="0.01" name="lines[INDEX][credit]"
                    class="form-control bg-transparent text-white border-secondary form-control-sm text-end credit-input"
                    value="0.00" oninput="calcTotals()">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger opacity-50 hover-opacity-100"
                    onclick="removeLine(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    <style>
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
        }

        .btn-glass-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .custom-table thead th {
            background-color: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(30, 41, 59, 0.9);
            border-color: #3b82f6;
            box-shadow: none;
            color: white;
        }

        option {
            background-color: #1e293b;
            color: white;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>

    <script>
        let lineIndex = 0;

        function addLine() {
            const template = document.getElementById('lineTemplate');
            const container = document.getElementById('linesContainer');
            const clone = template.content.cloneNode(true);

            // Replace INDEX placeholder
            clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
                el.name = el.name.replace('INDEX', lineIndex);
            });

            container.appendChild(clone);
            lineIndex++;
        }

        function removeLine(btn) {
            btn.closest('tr').remove();
            calcTotals();
        }

        function calcTotals() {
            let totalDebit = 0;
            let totalCredit = 0;

            document.querySelectorAll('.debit-input').forEach(inp => totalDebit += parseFloat(inp.value) || 0);
            document.querySelectorAll('.credit-input').forEach(inp => totalCredit += parseFloat(inp.value) || 0);

            document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
            document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);

            const diff = Math.abs(totalDebit - totalCredit);
            const statusBadge = document.getElementById('balanceStatus');
            const diffEl = document.getElementById('diffAmount');

            if (diff < 0.01 && totalDebit > 0) {
                statusBadge.className = 'badge bg-success rounded-pill px-3';
                statusBadge.textContent = 'متزن';
                diffEl.classList.add('d-none');
            } else {
                statusBadge.className = 'badge bg-danger rounded-pill px-3';
                statusBadge.textContent = 'غير متزن';
                diffEl.textContent = 'الفرق: ' + diff.toFixed(2);
                diffEl.classList.remove('d-none');
            }
        }

        // Add initial lines
        document.addEventListener('DOMContentLoaded', () => {
            addLine();
            addLine();
        });
    </script>
@endsection