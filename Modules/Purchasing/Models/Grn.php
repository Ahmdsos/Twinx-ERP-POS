<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Purchasing\Enums\GrnStatus;
use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Models\JournalEntry;
use App\Models\User;

/**
 * Grn Model - Goods Received Note
 */
class Grn extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $table = 'grns';

    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'received_date',
        'status',
        'supplier_delivery_note',
        'notes',
        'journal_entry_id',
        'received_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'status' => GrnStatus::class,
    ];

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        $config = config('erp.numbering.grn', ['prefix' => 'GRN']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'grn_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(GrnLine::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeCompleted($query)
    {
        return $query->where('status', GrnStatus::COMPLETED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', GrnStatus::DRAFT);
    }

    // ========================================
    // Business Methods
    // ========================================

    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function getTotalValue(): float
    {
        return $this->lines()->sum('line_total');
    }

    public function complete(): bool
    {
        if ($this->status !== GrnStatus::DRAFT) {
            return false;
        }

        $this->update(['status' => GrnStatus::COMPLETED]);
        return true;
    }
}
