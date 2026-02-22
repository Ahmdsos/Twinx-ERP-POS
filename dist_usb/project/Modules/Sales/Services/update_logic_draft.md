# SalesService::updateInvoice Logic

```php
public function updateInvoice(SalesInvoice $invoice, array $data): SalesInvoice
{
    return DB::transaction(function () use ($invoice, $data) {
        // 1. Reverse Accounting
        if ($invoice->journal_entry_id) {
            $this->journalService->reverse($invoice->journalEntry);
        }

        // 2. Handle Inventory/COGS (If Direct Invoice/POS)
        // Differentiate between DO-linked and Direct Invoices
        if (!$invoice->delivery_order_id) {
            // Reverse existing COGS/Inventory movements
            // ... Logic to find and reverse stock movements linked to this invoice
        }

        // 3. Update Header
        $invoice->update([
            'customer_id' => $data['customer_id'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            // ...
        ]);
        
        // 4. Update Lines (Sync)
        // ... (Similar to SalesOrder sync, but also handling Stock Adjustment for Direct Invoices)

        // 5. Create New Journal Entry
        $this->createInvoiceJournalEntry($invoice);
        
        return $invoice->fresh();
    });
}
```
