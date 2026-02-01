<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Purchasing\Models\PurchaseInvoice; // Hypothetical relationship
use Modules\Purchasing\Models\Supplier;

class PurchaseReturn extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'return_number',
        'supplier_id',
        'purchase_invoice_id',
        'return_date',
        'status', // draft, approved, cancelled
        'total_amount',
        'subtotal',
        'tax_amount',
        'notes',
        'journal_entry_id'
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function getDocumentPrefix(): string
    {
        return 'PRT';
    }

    public function getDocumentNumberField(): string
    {
        return 'return_number';
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseReturnLine::class);
    }
}
