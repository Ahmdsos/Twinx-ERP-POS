<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\PosShift;

/**
 * SecurityAuditLog - Comprehensive security event tracking
 * 
 * Tracks: failed PINs, cart deletions, void transactions, shift signatures
 */
class SecurityAuditLog extends Model
{
    use HasFactory;

    // Event types
    const EVENT_FAILED_PIN = 'failed_pin';
    const EVENT_CART_DELETE = 'cart_delete';
    const EVENT_VOID_TRANSACTION = 'void_transaction';
    const EVENT_SHIFT_SIGN = 'shift_sign';
    const EVENT_PRICE_OVERRIDE = 'price_override';
    const EVENT_REFUND = 'refund';
    const EVENT_DRAWER_OPEN = 'drawer_open';
    const EVENT_LOGIN_FAILED = 'login_failed';

    // Severity levels
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_CRITICAL = 'critical';

    protected $fillable = [
        'user_id',
        'shift_id',
        'event_type',
        'event_category',
        'severity',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'requires_review',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'requires_review' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(PosShift::class, 'shift_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== STATIC LOGGING METHODS ====================

    /**
     * Log a failed PIN attempt
     */
    public static function logFailedPin(?int $userId = null, string $context = 'unknown'): self
    {
        return static::log(
            self::EVENT_FAILED_PIN,
            "محاولة إدخال PIN فاشلة - {$context}",
            ['context' => $context],
            self::SEVERITY_WARNING,
            true
        );
    }

    /**
     * Log a cart item deletion
     */
    public static function logCartDelete(int $productId, string $productName, float $quantity, float $price, ?string $reason = null): self
    {
        return static::log(
            self::EVENT_CART_DELETE,
            "حذف منتج من السلة: {$productName}",
            [
                'product_id' => $productId,
                'product_name' => $productName,
                'quantity' => $quantity,
                'price' => $price,
                'reason' => $reason,
            ],
            self::SEVERITY_INFO
        );
    }

    /**
     * Log a void transaction
     */
    public static function logVoidTransaction(int $invoiceId, string $invoiceNumber, float $amount, ?int $managerId = null, ?string $reason = null): self
    {
        return static::log(
            self::EVENT_VOID_TRANSACTION,
            "إلغاء فاتورة رقم: {$invoiceNumber}",
            [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'manager_id' => $managerId,
                'reason' => $reason,
            ],
            self::SEVERITY_CRITICAL,
            true
        );
    }

    /**
     * Log a shift digital signature
     */
    public static function logShiftSignature(int $shiftId, float $expectedCash, float $actualCash, string $signatureHash): self
    {
        $difference = $actualCash - $expectedCash;
        $severity = abs($difference) > 10 ? self::SEVERITY_WARNING : self::SEVERITY_INFO;

        return static::log(
            self::EVENT_SHIFT_SIGN,
            "توقيع رقمي لإغلاق الوردية #{$shiftId}",
            [
                'shift_id' => $shiftId,
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'difference' => $difference,
                'signature_hash' => $signatureHash,
            ],
            $severity,
            abs($difference) > 50 // Require review if difference > 50
        );
    }

    /**
     * Log a refund
     */
    public static function logRefund(int $invoiceId, float $amount, ?int $managerId = null, ?string $reason = null): self
    {
        return static::log(
            self::EVENT_REFUND,
            "استرجاع بقيمة: {$amount}",
            [
                'invoice_id' => $invoiceId,
                'amount' => $amount,
                'manager_id' => $managerId,
                'reason' => $reason,
            ],
            self::SEVERITY_WARNING,
            $amount > 500 // Require review if amount > 500
        );
    }

    /**
     * Core logging method
     */
    public static function log(
        string $eventType,
        string $description,
        array $metadata = [],
        string $severity = self::SEVERITY_INFO,
        bool $requiresReview = false
    ): self {
        // Get active shift if available
        $shiftId = null;
        if (auth()->check()) {
            $activeShift = PosShift::where('user_id', auth()->id())
                ->whereNull('ended_at')
                ->first();
            $shiftId = $activeShift?->id;
        }

        return static::create([
            'user_id' => auth()->id(),
            'shift_id' => $shiftId,
            'event_type' => $eventType,
            'event_category' => static::getCategoryForType($eventType),
            'severity' => $severity,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'requires_review' => $requiresReview,
        ]);
    }

    /**
     * Get category for event type
     */
    protected static function getCategoryForType(string $type): string
    {
        return match ($type) {
            self::EVENT_FAILED_PIN, self::EVENT_LOGIN_FAILED => 'security',
            self::EVENT_VOID_TRANSACTION, self::EVENT_REFUND => 'transaction',
            default => 'audit',
        };
    }

    // ==================== SCOPES ====================

    public function scopePendingReview($query)
    {
        return $query->where('requires_review', true)->whereNull('reviewed_at');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    public function scopeForShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    // ==================== METHODS ====================

    /**
     * Mark as reviewed
     */
    public function markReviewed(?int $reviewerId = null): self
    {
        $this->update([
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
        return $this;
    }
}
