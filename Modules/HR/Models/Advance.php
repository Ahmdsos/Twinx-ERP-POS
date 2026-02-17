<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Modules\Core\Traits\HasAuditTrail;

class Advance extends Model
{
    use HasFactory, SoftDeletes, HasAuditTrail;

    protected $table = 'hr_advances';

    protected $fillable = [
        'employee_id',
        'amount',
        'request_date',
        'repayment_month',
        'repayment_year',
        'status',
        'notes',
        'approved_by',
        'paid_by',
        'approved_at',
        'paid_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeDue($query, $month, $year)
    {
        return $query->where('status', 'paid') // Only paid advances are deductible
            ->where('repayment_month', $month)
            ->where('repayment_year', $year);
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
