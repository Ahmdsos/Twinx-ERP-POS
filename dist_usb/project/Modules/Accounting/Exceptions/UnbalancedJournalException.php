<?php

namespace Modules\Accounting\Exceptions;

use Exception;

/**
 * UnbalancedJournalException - Thrown when journal entry doesn't balance
 */
class UnbalancedJournalException extends Exception
{
    protected float $totalDebit;
    protected float $totalCredit;

    public function __construct(float $totalDebit, float $totalCredit)
    {
        $this->totalDebit = $totalDebit;
        $this->totalCredit = $totalCredit;

        $difference = abs($totalDebit - $totalCredit);

        parent::__construct(
            "Journal entry is not balanced. " .
            "Total Debit: {$totalDebit}, Total Credit: {$totalCredit}, " .
            "Difference: {$difference}"
        );
    }

    public function getTotalDebit(): float
    {
        return $this->totalDebit;
    }

    public function getTotalCredit(): float
    {
        return $this->totalCredit;
    }

    public function getDifference(): float
    {
        return abs($this->totalDebit - $this->totalCredit);
    }
}
