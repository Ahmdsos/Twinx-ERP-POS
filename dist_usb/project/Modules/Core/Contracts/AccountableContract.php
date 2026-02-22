<?php

namespace Modules\Core\Contracts;

use Carbon\Carbon;

/**
 * AccountableContract - Interface for entities that create accounting entries
 * 
 * Any module that needs to create journal entries (Sales, Purchases, Inventory)
 * should implement this interface on their respective models.
 * 
 * Example Usage:
 * - SalesInvoice implements AccountableContract
 * - PurchaseInvoice implements AccountableContract
 * - StockMovement implements AccountableContract
 */
interface AccountableContract
{
    /**
     * Get the journal entry lines for this transaction
     * 
     * Returns an array of lines, each containing:
     * - account_id: The account to debit/credit
     * - debit: Amount to debit (or 0)
     * - credit: Amount to credit (or 0)
     * - description: Optional line description
     * 
     * @return array<int, array{account_id: int, debit: float, credit: float, description?: string}>
     */
    public function getJournalLines(): array;

    /**
     * Get the reference number for the journal entry
     * (e.g., Invoice number, PO number)
     * 
     * @return string
     */
    public function getJournalReference(): string;

    /**
     * Get the description/narration for the journal entry
     * 
     * @return string
     */
    public function getJournalDescription(): string;

    /**
     * Get the date for the journal entry
     * 
     * @return Carbon
     */
    public function getJournalDate(): Carbon;

    /**
     * Get the primary key value for the model
     * 
     * @return mixed
     */
    public function getKey();
}
