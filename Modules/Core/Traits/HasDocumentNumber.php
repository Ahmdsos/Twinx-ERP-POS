<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Str;

/**
 * HasDocumentNumber Trait - Auto-generates document numbers
 * 
 * Use this trait on models that need auto-generated document numbers
 * like Invoices, Orders, POs, etc.
 * 
 * Usage:
 * 1. Add the trait to your model
 * 2. Define the getDocumentPrefix() method
 * 3. The number will be auto-generated on creation
 */
trait HasDocumentNumber
{
    /**
     * Boot the trait
     */
    protected static function bootHasDocumentNumber(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getDocumentNumberField()})) {
                $model->{$model->getDocumentNumberField()} = $model->generateDocumentNumber();
            }
        });
    }

    /**
     * Generate a unique document number
     * Format: PREFIX-YEAR-SEQUENCE (e.g., INV-2026-000001)
     */
    public function generateDocumentNumber(): string
    {
        $prefix = $this->getDocumentPrefix();
        $year = date('Y');

        // Get the last document number for this year
        // Use lockForUpdate() to prevent race conditions during concurrent creation
        // This ensures only one request can read/increment at a time
        $lastDocument = static::query()
            ->where($this->getDocumentNumberField(), 'like', "{$prefix}-{$year}-%")
            ->orderByDesc($this->getDocumentNumberField())
            ->lockForUpdate()
            ->first();

        if ($lastDocument) {
            // Extract the sequence number and increment
            $lastNumber = $lastDocument->{$this->getDocumentNumberField()};
            $sequence = (int) Str::afterLast($lastNumber, '-') + 1;
        } else {
            $sequence = 1;
        }

        $padding = $this->getDocumentPadding();

        return sprintf('%s-%s-%0' . $padding . 'd', $prefix, $year, $sequence);
    }

    /**
     * Get the field name for the document number
     * Override this if your field is not 'number'
     */
    public function getDocumentNumberField(): string
    {
        return 'number';
    }

    /**
     * Get the document prefix (e.g., 'INV', 'SO', 'PO')
     * Must be overridden by the model
     */
    abstract public function getDocumentPrefix(): string;

    /**
     * Get the padding for sequence number
     * Override to change from default 6 digits
     */
    public function getDocumentPadding(): int
    {
        return 6;
    }
}
