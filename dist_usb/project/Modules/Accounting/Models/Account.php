<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Accounting\Enums\AccountType;
use Modules\Core\Traits\HasAuditTrail;

/**
 * Account Model - Chart of Accounts
 * 
 * Represents a single account in the chart of accounts.
 * Supports hierarchical structure (parent-child relationships).
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property AccountType $type
 * @property int|null $parent_id
 * @property string|null $description
 * @property bool $is_header
 * @property bool $is_active
 * @property bool $is_system
 * @property float $balance
 */
class Account extends Model
{
    use HasFactory, SoftDeletes, HasAuditTrail;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'type',
        'parent_id',
        'description',
        'is_header',
        'is_active',
        'is_system',
        'balance',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::deleting(function ($account) {
            // 1. Block System Accounts
            if ($account->is_system) {
                throw new \RuntimeException("Cannot delete system account: [{$account->code}] {$account->name}");
            }

            // 2. Block accounts used in Settings (SSOT Protection)
            $isUsedInSettings = \App\Models\Setting::where('group', 'accounting')
                ->where('value', $account->code)
                ->exists();

            if ($isUsedInSettings) {
                throw new \RuntimeException("Cannot delete account [{$account->code}]: It is currently linked in system settings.");
            }

            // 3. Block accounts with children (Hierarchy Protection)
            if ($account->children()->exists()) {
                throw new \RuntimeException("Cannot delete account [{$account->code}]: It has sub-accounts.");
            }

            // 4. Block accounts with transactions (Ledger Protection)
            if ($account->journalLines()->exists()) {
                throw new \RuntimeException("Cannot delete account [{$account->code}]: It has transaction history.");
            }
        });
    }

    protected $casts = [
        'type' => AccountType::class,
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get all child accounts (direct children only)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get all descendants recursively
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parents up to root)
     */
    public function ancestors(): BelongsTo
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Get journal entry lines for this account
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /**
     * Scope to filter only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter only postable accounts (non-header)
     */
    public function scopePostable($query)
    {
        return $query->where('is_header', false);
    }

    /**
     * Scope to filter by account type
     */
    public function scopeOfType($query, AccountType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get root accounts (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if this is a debit-normal account
     */
    public function isDebitNormal(): bool
    {
        return $this->type->debitIncreases();
    }

    /**
     * Check if account can be posted to
     */
    public function isPostable(): bool
    {
        return !$this->is_header && $this->is_active;
    }

    /**
     * Get the full hierarchical path (e.g., "Assets > Current Assets > Cash")
     */
    public function getPathAttribute(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    /**
     * Get the depth level in hierarchy (0 = root)
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Get display name (English - Arabic)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name_ar) {
            return "{$this->name} - {$this->name_ar}";
        }
        return $this->name;
    }

    /**
     * Update the account balance
     */
    public function updateBalance(float $debitSum, float $creditSum): void
    {
        if ($this->isDebitNormal()) {
            $this->balance = $debitSum - $creditSum;
        } else {
            $this->balance = $creditSum - $debitSum;
        }
        $this->save();
    }
}
