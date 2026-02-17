<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\HR\Enums\EmployeeStatus;

class Employee extends Model
{
    use HasFactory, SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $table = 'hr_employees';

    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'nationality',
        'marital_status',
        'email',
        'phone',
        'position',
        'department',
        'date_of_joining',
        'basic_salary',
        'bank_name',
        'bank_account_number',
        'iban',
        'social_security_number',
        'contract_type',
        'status',
        'id_number',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'created_by',
        'updated_by',
    ];

    // H-16 FIX: Deprecated - use EmployeeStatus enum instead
    /** @deprecated Use EmployeeStatus::ACTIVE instead */
    const STATUS_ACTIVE = 'active';
    /** @deprecated Use EmployeeStatus::INACTIVE instead */
    const STATUS_INACTIVE = 'inactive';
    /** @deprecated Use EmployeeStatus::ON_LEAVE instead */
    const STATUS_ON_LEAVE = 'on_leave';
    /** @deprecated Use EmployeeStatus::TERMINATED instead */
    const STATUS_TERMINATED = 'terminated';

    public static function getStatusLabels(): array
    {
        return [
            EmployeeStatus::ACTIVE->value => EmployeeStatus::ACTIVE->label(),
            EmployeeStatus::INACTIVE->value => EmployeeStatus::INACTIVE->label(),
            EmployeeStatus::ON_LEAVE->value => EmployeeStatus::ON_LEAVE->label(),
            EmployeeStatus::TERMINATED->value => EmployeeStatus::TERMINATED->label(),
        ];
    }

    public static function getStatusColors(): array
    {
        return [
            EmployeeStatus::ACTIVE->value => EmployeeStatus::ACTIVE->color(),
            EmployeeStatus::INACTIVE->value => EmployeeStatus::INACTIVE->color(),
            EmployeeStatus::ON_LEAVE->value => EmployeeStatus::ON_LEAVE->color(),
            EmployeeStatus::TERMINATED->value => EmployeeStatus::TERMINATED->color(),
        ];
    }

    protected $casts = [
        'date_of_joining' => 'date',
        'birth_date' => 'date',
        'basic_salary' => 'decimal:2',
        'status' => EmployeeStatus::class, // H-16: Enum casting
    ];

    /**
     * Get the document prefix for auto-numbering
     */
    public function getDocumentPrefix(): string
    {
        return 'EMP';
    }

    /**
     * Get the field name for document number
     */
    public function getDocumentNumberField(): string
    {
        return 'employee_code';
    }

    /**
     * Get the user account associated with the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this employee record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attendance records for the employee.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    /**
     * Get the payroll items for the employee.
     */
    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'employee_id');
    }

    /**
     * Get the documents for the employee.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'employee_id');
    }

    /**
     * Get the leave records for the employee.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'employee_id');
    }

    /**
     * Get the delivery driver profile if the employee is a driver.
     */
    public function deliveryDriver(): HasOne
    {
        return $this->hasOne(DeliveryDriver::class, 'employee_id');
    }

    /**
     * Get the advances for the employee.
     */
    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class, 'employee_id');
    }

    /**
     * Get the full name of the employee.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    /**
     * Get the effective status of the employee (e.g. checks for active leaves).
     */
    public function getCurrentStatusAttribute(): EmployeeStatus
    {
        // Check for active leave
        $isOnLeave = false;

        if ($this->relationLoaded('leaves')) {
            $isOnLeave = $this->leaves->contains(function ($leave) {
                return $leave->status === 'approved' &&
                    now()->between(\Carbon\Carbon::parse($leave->start_date), \Carbon\Carbon::parse($leave->end_date));
            });
        } else {
            $isOnLeave = $this->leaves()
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->exists();
        }

        if ($isOnLeave) {
            return EmployeeStatus::ON_LEAVE;
        }

        return $this->status;
    }
}
