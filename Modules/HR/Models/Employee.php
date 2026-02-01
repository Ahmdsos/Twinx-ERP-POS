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

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_TERMINATED = 'terminated';

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_ACTIVE => 'نشط',
            self::STATUS_INACTIVE => 'غير نشط',
            self::STATUS_ON_LEAVE => 'في إجازة',
            self::STATUS_TERMINATED => 'تم إنهاء الخدمة',
        ];
    }

    public static function getStatusColors(): array
    {
        return [
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_ON_LEAVE => 'warning text-dark',
            self::STATUS_TERMINATED => 'danger',
        ];
    }

    protected $casts = [
        'date_of_joining' => 'date',
        'birth_date' => 'date',
        'basic_salary' => 'decimal:2',
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
     * Get the full name of the employee.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
