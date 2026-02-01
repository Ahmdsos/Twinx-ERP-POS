<?php

namespace Modules\Core\Traits;

/**
 * HasAuditTrail Trait - Tracks who created/updated records
 * 
 * This trait automatically sets created_by and updated_by fields
 * based on the currently authenticated user.
 * 
 * Requirements:
 * - Your migration must have created_by and updated_by columns
 * - User must be authenticated for tracking to work
 */
trait HasAuditTrail
{
    /**
     * Boot the trait
     */
    protected static function bootHasAuditTrail(): void
    {
        // Set created_by on creation
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });

        // Log creation
        static::created(function ($model) {
            self::logActivity($model, 'created');
        });

        // Set updated_by on update
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // Log updates
        static::updated(function ($model) {
            self::logActivity($model, 'updated');
        });

        // Log deletions
        static::deleted(function ($model) {
            self::logActivity($model, 'deleted');
        });
    }

    /**
     * Log activity to database
     */
    /**
     * Log activity to database
     */
    public static function logActivity($model, $action, $customDescription = null)
    {
        try {
            // Skip logging if disabled or not running in app (e.g. seeders)
            if (!auth()->check() && !app()->runningInConsole()) {
                return;
            }

            $user = auth()->user();
            // If the model itself is a User (e.g. login event), use it as the actor if auth user is missing
            if (!$user && $model instanceof \App\Models\User) {
                $user = $model;
            }

            $userId = $user ? $user->id : null;
            $userName = $user ? $user->name : (app()->runningInConsole() ? 'System/Console' : 'System');

            $oldValues = null;
            $newValues = null;
            $description = $customDescription ?? ("تم " . self::getActionName($action) . " " . class_basename($model));

            if ($action === 'updated') {
                // Get changed attributes
                $changes = $model->getChanges();
                $original = $model->getOriginal();

                // Remove timestamp fields from diff
                unset($changes['updated_at'], $changes['updated_by']);

                if (empty($changes)) {
                    return; // No meaningful changes
                }

                $newValues = $changes;
                $oldValues = array_intersect_key($original, $changes);

                $description .= " (تحديث " . count($changes) . " حقول)";
            } elseif ($action === 'created') {
                $newValues = $model->getAttributes();
                unset($newValues['created_at'], $newValues['updated_at'], $newValues['id']);
            } elseif ($action === 'deleted') {
                $oldValues = $model->getAttributes();
            }

            // Create Log
            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'user_name' => $userName,
                'subject_type' => get_class($model),
                'subject_id' => $model->id,
                'subject_name' => self::getSubjectName($model),
                'action' => $action,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);

        } catch (\Exception $e) {
            // Fail silently to not block the action
            \Illuminate\Support\Facades\Log::error('Activity Log Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper to get action Arabic name
     */
    protected static function getActionName($action)
    {
        return match ($action) {
            'created' => 'إضافة',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
            default => $action
        };
    }

    /**
     * Try to get a readable name for the subject
     */
    protected static function getSubjectName($model)
    {
        if (!empty($model->name))
            return $model->name;
        if (!empty($model->title))
            return $model->title;
        if (!empty($model->code))
            return $model->code . ' (Code)';
        if (!empty($model->invoice_number))
            return $model->invoice_number;

        return class_basename($model) . ' #' . $model->id;
    }

    /**
     * Get the user who created this record
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
