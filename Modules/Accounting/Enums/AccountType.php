<?php

namespace Modules\Accounting\Enums;

/**
 * AccountType Enum - Defines the 5 fundamental account types
 * 
 * In Double-Entry Accounting:
 * - Assets = Liabilities + Equity + (Revenue - Expenses)
 * - Debit increases: Assets, Expenses
 * - Credit increases: Liabilities, Equity, Revenue
 */
enum AccountType: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::ASSET => 'Asset',
            self::LIABILITY => 'Liability',
            self::EQUITY => 'Equity',
            self::REVENUE => 'Revenue',
            self::EXPENSE => 'Expense',
        };
    }

    /**
     * Get the normal balance side for this account type
     * Returns 'debit' or 'credit'
     */
    public function normalBalance(): string
    {
        return match ($this) {
            self::ASSET, self::EXPENSE => 'debit',
            self::LIABILITY, self::EQUITY, self::REVENUE => 'credit',
        };
    }

    /**
     * Check if debit increases this account type
     */
    public function debitIncreases(): bool
    {
        return $this->normalBalance() === 'debit';
    }

    /**
     * Get all values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for select dropdowns
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}
