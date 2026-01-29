<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * ActivityLogService
 * Central service for logging all user activities
 */
class ActivityLogService
{
    /**
     * Log an activity
     */
    public static function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        $user = Auth::user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'subject_name' => $subject ? self::getSubjectName($subject) : null,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    /**
     * Log model creation
     */
    public static function logCreated(Model $model, ?string $description = null): ActivityLog
    {
        return self::log(
            action: 'created',
            subject: $model,
            description: $description ?? "تم إنشاء " . self::getSubjectName($model),
            newValues: $model->getAttributes()
        );
    }

    /**
     * Log model update
     */
    public static function logUpdated(Model $model, ?string $description = null): ActivityLog
    {
        $dirty = $model->getDirty();
        $original = collect($model->getOriginal())->only(array_keys($dirty))->toArray();

        return self::log(
            action: 'updated',
            subject: $model,
            description: $description ?? "تم تعديل " . self::getSubjectName($model),
            oldValues: $original,
            newValues: $dirty
        );
    }

    /**
     * Log model deletion
     */
    public static function logDeleted(Model $model, ?string $description = null): ActivityLog
    {
        return self::log(
            action: 'deleted',
            subject: $model,
            description: $description ?? "تم حذف " . self::getSubjectName($model),
            oldValues: $model->getAttributes()
        );
    }

    /**
     * Log user login
     */
    public static function logLogin(): ActivityLog
    {
        return self::log(
            action: 'logged_in',
            description: 'تسجيل دخول للنظام'
        );
    }

    /**
     * Log user logout
     */
    public static function logLogout(): ActivityLog
    {
        return self::log(
            action: 'logged_out',
            description: 'تسجيل خروج من النظام'
        );
    }

    /**
     * Log custom action
     */
    public static function logAction(
        string $action,
        ?Model $subject = null,
        ?string $description = null
    ): ActivityLog {
        return self::log(
            action: $action,
            subject: $subject,
            description: $description
        );
    }

    /**
     * Get human-readable subject name
     */
    protected static function getSubjectName(Model $model): string
    {
        // Try common name attributes
        if (isset($model->name)) {
            $type = class_basename($model);
            return "{$type}: {$model->name}";
        }

        if (isset($model->code)) {
            $type = class_basename($model);
            return "{$type}: {$model->code}";
        }

        if (isset($model->entry_number)) {
            return "Journal Entry: {$model->entry_number}";
        }

        if (isset($model->invoice_number)) {
            return "Invoice: {$model->invoice_number}";
        }

        if (isset($model->order_number)) {
            return "Order: {$model->order_number}";
        }

        return class_basename($model) . " #{$model->id}";
    }
}
