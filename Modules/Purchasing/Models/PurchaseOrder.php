<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Purchasing\Enums\PurchaseOrderStatus;
use Modules\Inventory\Models\Warehouse;
use App\Models\User;

/**
 * PurchaseOrder Model - PO Header
 */
class PurchaseOrder extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'warehouse_id',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'currency',
        'exchange_rate',
        'reference',
        'notes',
        'terms',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'status' => PurchaseOrderStatus::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        $config = config('erp.numbering.purchase_order', ['prefix' => 'PO']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'po_number';
    }

    // ========================================
    // Relationships
    // ========================================

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
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function grns(): HasMany
    {
        return $this->hasMany(Grn::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeStatus($query, PurchaseOrderStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::DRAFT,
            PurchaseOrderStatus::PENDING,
        ]);
    }

    public function scopeAwaitingReceipt($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::APPROVED,
            PurchaseOrderStatus::SENT,
            PurchaseOrderStatus::PARTIAL,
        ]);
    }

    // ========================================
    // Business Methods
    // ========================================

    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function canReceive(): bool
    {
        return $this->status->canReceive();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function approve(?int $userId = null): bool
    {
        if (!in_array($this->status, [PurchaseOrderStatus::DRAFT, PurchaseOrderStatus::PENDING])) {
            return false;
        }

        $this->update([
            'status' => PurchaseOrderStatus::APPROVED,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);

        return true;
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->lines()->sum('line_total');
        $taxAmount = $this->lines()->sum('tax_amount');
        $discountAmount = $this->lines()->sum('discount_amount');

        $this->update([
            'subtotal' => $subtotal + $discountAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);
    }

    public function getReceivedPercentage(): float
    {
        $totalQty = $this->lines()->sum('quantity');
        $receivedQty = $this->lines()->sum('received_quantity');

        return $totalQty > 0 ? round(($receivedQty / $totalQty) * 100, 2) : 0;
    }

    public function updateReceiptStatus(): void
    {
        $percentage = $this->getReceivedPercentage();

        if ($percentage >= 100) {
            $this->update(['status' => PurchaseOrderStatus::RECEIVED]);
        } elseif ($percentage > 0) {
            $this->update(['status' => PurchaseOrderStatus::PARTIAL]);
        }
    }
}
