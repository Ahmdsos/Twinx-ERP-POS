<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'hr_attendance';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'status' => \Modules\HR\Enums\AttendanceStatus::class,
    ];

    // Constants for backward compatibility and easier access
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_HOLIDAY = 'holiday';

    public static function getStatusLabels(): array
    {
        return array_column(\Modules\HR\Enums\AttendanceStatus::cases(), 'value', 'value');
        // Or better, map it if needed, but Enum has label() method now.
    }


    /**
     * Get the duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $in = Carbon::parse($this->clock_in);
        $out = Carbon::parse($this->clock_out);

        return $in->diffInMinutes($out);
    }

    /**
     * Get formatted duration (Arabic).
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->clock_in)
            return '---';
        if (!$this->clock_out)
            return 'قيد العمل';

        $totalMinutes = $this->duration_minutes;
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return "{$hours} ساعة و {$minutes} دقيقة";
    }

    /**
     * Get the employee associated with the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
