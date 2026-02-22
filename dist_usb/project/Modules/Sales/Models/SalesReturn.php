<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Sales\Enums\SalesReturnStatus;
use Modules\Inventory\Models\Warehouse;

class SalesReturn extends Model
{
    use SoftDeletes, HasDocumentNumber;

    protected $fillable = [
        'return_number',
        'sales_invoice_id',
        'customer_id',
        'warehouse_id',
        'shift_id', // Added
        'return_date',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'reason',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'status' => SalesReturnStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Trait Methods
    public function getDocumentPrefix(): string
    {
        return 'SR-';
    }

    public function getDocumentNumberField(): string
    {
        return 'return_number';
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(\Modules\Sales\Models\PosShift::class, 'shift_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesReturnLine::class);
    }
}
