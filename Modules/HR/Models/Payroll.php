<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Core\Contracts\AccountableContract;
use Carbon\Carbon;

class Payroll extends Model implements AccountableContract
{
    use HasFactory, HasDocumentNumber, HasAuditTrail;

    protected $table = 'hr_payrolls';

    protected $fillable = [
        'payroll_number',
        'month',
        'year',
        'process_date',
        'total_basic',
        'total_allowances',
        'total_deductions',
        'net_salary',
        'status',
        'processed_by',
        'journal_entry_id',
    ];

    /**
     * Legacy support for total_net.
     */
    public function getTotalNetAttribute()
    {
        return $this->net_salary;
    }

    /**
     * Get the document prefix for auto-numbering
     */
    public function getDocumentPrefix(): string
    {
        return 'PAY';
    }

    /**
     * Get the field name for document number
     */
    public function getDocumentNumberField(): string
    {
        return 'payroll_number';
    }

    protected $casts = [
        'process_date' => 'date',
        'total_basic' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'journal_entry_id' => 'integer',
    ];

    /**
     * Get the payroll items (lines) for this payroll period.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'payroll_id');
    }

    /**
     * Get the user who processed this payroll.
     */
    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // --- AccountableContract Implementation ---

    public function getJournalLines(): array
    {
        $expAccountCode = \App\Models\Setting::getValue('acc_salaries_exp');
        $payableAccountCode = \App\Models\Setting::getValue('acc_salaries_payable');

        $expAccount = \Modules\Accounting\Models\Account::where('code', $expAccountCode)->first();
        $payableAccount = \Modules\Accounting\Models\Account::where('code', $payableAccountCode)->first();

        if (!$expAccount || !$payableAccount) {
            throw new \Exception("إعدادات حسابات الرواتب غير مكتملة.");
        }

        $basicTotal = $this->total_basic;
        $allowancesTotal = $this->total_allowances;
        $deductionsTotal = $this->total_deductions;
        $netTotal = $this->net_salary;

        $lines = [];

        // 1. Debit Expense: Basic Salary
        $lines[] = [
            'account_id' => $expAccount->id,
            'debit' => $basicTotal,
            'credit' => 0,
            'description' => "رواتب أساسية شهر {$this->month}-{$this->year}",
        ];

        // 2. Debit Expense: Allowances (if any)
        if ($allowancesTotal > 0) {
            $lines[] = [
                'account_id' => $expAccount->id,
                'debit' => $allowancesTotal,
                'credit' => 0,
                'description' => "بدلات وحوافز شهر {$this->month}-{$this->year}",
            ];
        }

        // 3. Credit Expense: Deductions (Reduction of expense or separate revenue? Usually reduction of expense)
        if ($deductionsTotal > 0) {
            $lines[] = [
                'account_id' => $expAccount->id,
                'debit' => 0,
                'credit' => $deductionsTotal,
                'description' => "خصومات وجزاءات شهر {$this->month}-{$this->year}",
            ];
        }

        // 4. Credit Payable: Net Salary
        $lines[] = [
            'account_id' => $payableAccount->id,
            'debit' => 0,
            'credit' => $netTotal,
            'description' => "صافي رواتب مستحقة شهر {$this->month}-{$this->year}",
        ];

        return $lines;
    }

    public function getJournalReference(): string
    {
        return $this->payroll_number;
    }

    public function getJournalDescription(): string
    {
        return "تسوية رواتب شهر {$this->month}-{$this->year}";
    }

    public function getJournalDate(): Carbon
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
    }
}
