<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * ActivityLog Model
 * Tracks all user actions for audit purposes
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'subject_type',
        'subject_id',
        'subject_name',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject model
     * H-07 FIX: Always return MorphTo relationship - it handles null automatically
     */
    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    /**
     * Scope for filtering by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by subject
     */
    public function scopeForSubject($query, string $type, int $id)
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Get formatted action for display
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            'viewed' => 'عرض',
            'logged_in' => 'تسجيل دخول',
            'logged_out' => 'تسجيل خروج',
            'approved' => 'موافقة',
            'cancelled' => 'إلغاء',
            'posted' => 'ترحيل',
            default => $this->action,
        };
    }

    /**
     * Get action badge color
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'logged_in' => 'primary',
            'logged_out' => 'secondary',
            'approved' => 'success',
            'cancelled' => 'warning',
            'posted' => 'success',
            default => 'secondary',
        };
    }
}
