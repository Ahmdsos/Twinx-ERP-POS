<?php

namespace App\Traits;

use App\Services\ActivityLogService;

/**
 * LogsActivity Trait
 * Add to any model to automatically log CRUD operations
 */
trait LogsActivity
{
    /**
     * Boot the trait
     */
    protected static function bootLogsActivity(): void
    {
        // Log when model is created
        static::created(function ($model) {
            if (self::shouldLogActivity()) {
                ActivityLogService::logCreated($model);
            }
        });

        // Log when model is updated
        static::updated(function ($model) {
            if (self::shouldLogActivity() && $model->wasChanged()) {
                ActivityLogService::logUpdated($model);
            }
        });

        // Log when model is deleted
        static::deleted(function ($model) {
            if (self::shouldLogActivity()) {
                ActivityLogService::logDeleted($model);
            }
        });
    }

    /**
     * Check if we should log activity (avoid logging during seeding, etc.)
     */
    protected static function shouldLogActivity(): bool
    {
        // Don't log during console commands (seeding, migrations)
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return false;
        }

        return true;
    }

    /**
     * Log a custom action for this model
     */
    public function logActivity(string $action, ?string $description = null): void
    {
        ActivityLogService::logAction($action, $this, $description);
    }
}
