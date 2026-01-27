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

        // Set updated_by on update
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
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
