<?php

namespace Modules\Inventory\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\JournalService;

/**
 * InventoryService - Core service for inventory operations
 * 
 * Handles:
 * - Stock movements (in/out)
 * - FIFO and Average costing
 * - Stock adjustments
 * - Transfers between warehouses
 */
class InventoryService
{
    public function __construct(
        protected JournalService $journalService
    ) {
    }

    /**
     * Add stock to inventory (purchase, adjustment in, etc.)
     */
    public function addStock(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        float $unitCost,
        MovementType $type,
        ?string $reference = null,
        ?string $notes = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        bool $createJournal = true
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $unitCost, $type, $reference, $notes, $sourceType, $sourceId, $createJournal) {
            $totalCost = $quantity * $unitCost;

            // Create stock movement record
            $movement = StockMovement::create([
                'movement_date' => now(),
                'type' => $type,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'remaining_quantity' => $quantity, // For FIFO tracking
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // Update product stock with explicit row lock for concurrency safety
            $stockRecord = ProductStock::getOrCreate($product->id, $warehouse->id);
            $stock = ProductStock::where('id', $stockRecord->id)->lockForUpdate()->first();

            $stock->updateFromMovement($quantity, $totalCost);

            // Create journal entry if product has inventory account
            if ($createJournal && $product->inventory_account_id && $product->purchase_account_id) {
                $this->createInventoryJournalEntry($movement, $product, 'add');
            }

            return $movement;
        });
    }

    /**
     * Remove stock from inventory (sale, adjustment out, etc.)
     * Uses FIFO or Average costing based on config
     */
    public function removeStock(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        MovementType $type,
        ?string $reference = null,
        ?string $notes = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        bool $createJournal = true
    ): StockMovement {
        $costingMethod = config('erp.inventory.costing_method', 'fifo');

        return DB::transaction(function () use ($product, $warehouse, $quantity, $type, $reference, $notes, $sourceType, $sourceId, $costingMethod, $createJournal) {
            // Get stock record with explicit row lock to prevent race conditions (Audit Finding #6)
            $stockRecord = ProductStock::getOrCreate($product->id, $warehouse->id);
            $stock = ProductStock::where('id', $stockRecord->id)->lockForUpdate()->first();

            // Check available quantity
            if (!config('erp.inventory.allow_negative_stock', false)) {
                if ($stock->available_quantity < $quantity) {
                    $available = (float) ($stock->available_quantity ?? 0);
                    throw new \RuntimeException(
                        "Insufficient stock. Available: {$available}, Requested: {$quantity}"
                    );
                }
            }

            // Calculate cost based on costing method
            if ($costingMethod === 'fifo') {
                $costData = $this->calculateFifoCost($product->id, $warehouse->id, $quantity);
            } else {
                $costData = $this->calculateAverageCost($stock, $quantity);
            }

            // Create stock movement record
            $movement = StockMovement::create([
                'movement_date' => now(),
                'type' => $type,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => -$quantity, // Negative for outward
                'unit_cost' => $costData['unit_cost'],
                'total_cost' => -$costData['total_cost'], // Negative for outward
                'remaining_quantity' => 0, // Outward movements don't have remaining
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // Update product stock
            $stock->updateFromMovement(-$quantity, -$costData['total_cost']);

            // Create journal entry if product has inventory account
            // Truth: Inventory reduction always requires a COGS/Inventory journal
            if ($createJournal && $product->inventory_account_id) {
                $this->createInventoryJournalEntry($movement, $product, 'remove');
            }

            return $movement;
        });
    }

    /**
     * Transfer stock between warehouses
     */
    public function transfer(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        float $quantity,
        ?string $reference = null,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($product, $fromWarehouse, $toWarehouse, $quantity, $reference, $notes) {
            // Get the cost from source warehouse
            $fromStock = ProductStock::getOrCreate($product->id, $fromWarehouse->id);
            $unitCost = $fromStock->average_cost;

            // Create outward movement from source
            $outMovement = $this->removeStock(
                $product,
                $fromWarehouse,
                $quantity,
                MovementType::TRANSFER_OUT,
                $reference,
                $notes
            );

            // Create inward movement to destination
            $inMovement = $this->addStock(
                $product,
                $toWarehouse,
                $quantity,
                $unitCost,
                MovementType::TRANSFER_IN,
                $reference,
                $notes
            );

            // Link the movements
            $outMovement->update(['to_warehouse_id' => $toWarehouse->id]);
            $inMovement->update(['reference' => $outMovement->movement_number]);

            return [
                'out' => $outMovement,
                'in' => $inMovement,
            ];
        });
    }

    /**
     * Adjust stock quantity
     */
    public function adjust(
        Product $product,
        Warehouse $warehouse,
        float $newQuantity,
        ?float $newUnitCost = null,
        ?string $reason = null
    ): ?StockMovement {
        $stock = ProductStock::getOrCreate($product->id, $warehouse->id);
        $currentQuantity = $stock->quantity;
        $difference = $newQuantity - $currentQuantity;

        if (abs($difference) < 0.0001) {
            return null; // No adjustment needed
        }

        $unitCost = $newUnitCost ?? $stock->average_cost;

        if ($difference > 0) {
            return $this->addStock(
                $product,
                $warehouse,
                $difference,
                $unitCost,
                MovementType::ADJUSTMENT_IN,
                null,
                $reason
            );
        } else {
            return $this->removeStock(
                $product,
                $warehouse,
                abs($difference),
                MovementType::ADJUSTMENT_OUT,
                null,
                $reason
            );
        }
    }

    /**
     * Get stock value for a product across all warehouses
     */
    public function getProductStockValue(Product $product): array
    {
        $stocks = ProductStock::where('product_id', $product->id)->get();

        return [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'total_quantity' => $stocks->sum('quantity'),
            'total_value' => $stocks->sum('total_cost'),
            'warehouses' => $stocks->map(fn($s) => [
                'warehouse_id' => $s->warehouse_id,
                'quantity' => $s->quantity,
                'available' => $s->available_quantity,
                'average_cost' => $s->average_cost,
                'total_value' => $s->total_cost,
            ])->toArray(),
        ];
    }

    /**
     * Calculate cost using FIFO method
     */
    protected function calculateFifoCost(int $productId, int $warehouseId, float $quantity): array
    {
        $remaining = $quantity;
        $totalCost = 0;

        // Get oldest movements with remaining stock
        $movements = StockMovement::forProduct($productId)
            ->inWarehouse($warehouseId)
            ->inward()
            ->withRemainingStock()
            ->orderBy('movement_date')
            ->orderBy('id')
            ->get();

        foreach ($movements as $movement) {
            if ($remaining <= 0)
                break;

            $consumed = $movement->consume($remaining);
            $totalCost += $consumed * $movement->unit_cost;
            $remaining -= $consumed;
        }

        $unitCost = $quantity > 0 ? $totalCost / $quantity : 0;

        return [
            'unit_cost' => round($unitCost, 4),
            'total_cost' => round($totalCost, 2),
        ];
    }

    /**
     * Calculate cost using Weighted Average method
     */
    protected function calculateAverageCost(ProductStock $stock, float $quantity): array
    {
        $unitCost = $stock->average_cost;
        $totalCost = $quantity * $unitCost;

        return [
            'unit_cost' => round($unitCost, 4),
            'total_cost' => round($totalCost, 2),
        ];
    }

    /**
     * Create journal entry for inventory movement
     */
    protected function createInventoryJournalEntry(
        StockMovement $movement,
        Product $product,
        string $direction
    ): void {
        $totalCost = abs($movement->total_cost);
        $movementType = $movement->type;

        // Inventory Account is always one side
        $inventoryAccountId = $product->inventory_account_id;

        // Determine the offset account based on movement type
        $offsetAccountCode = match ($movementType) {
            MovementType::INITIAL => \App\Models\Setting::getValue('acc_opening_balance', '3101'),
            MovementType::ADJUSTMENT_IN, MovementType::ADJUSTMENT_OUT => \App\Models\Setting::getValue('acc_inventory_adj', '5201'),
            MovementType::RETURN_IN, MovementType::SALE => \App\Models\Setting::getValue('acc_cogs', '5101'),
            MovementType::PURCHASE => \App\Models\Setting::getValue('acc_purchase_suspense', '2101'),
            default => \App\Models\Setting::getValue('acc_inventory_other', '5202'),
        };

        $offsetAccount = Account::where('code', $offsetAccountCode)->first();
        if (!$offsetAccount) {
            // Fallback to product's purchase account if specific setting fails
            $offsetAccountId = $product->purchase_account_id;
        } else {
            $offsetAccountId = $offsetAccount->id;
        }

        if ($direction === 'add') {
            // DR Inventory, CR Offset (e.g., ADJ_IN, INITIAL)
            $lines = [
                ['account_id' => $inventoryAccountId, 'debit' => $totalCost, 'credit' => 0],
                ['account_id' => $offsetAccountId, 'debit' => 0, 'credit' => $totalCost],
            ];
        } else {
            // DR Offset (e.g., ADJ_OUT, SALE), CR Inventory
            $lines = [
                ['account_id' => $offsetAccountId, 'debit' => $totalCost, 'credit' => 0],
                ['account_id' => $inventoryAccountId, 'debit' => 0, 'credit' => $totalCost],
            ];
        }

        $entry = $this->journalService->create([
            'entry_date' => $movement->movement_date,
            'reference' => $movement->movement_number,
            'description' => "{$movement->type->label()} - {$product->name}",
            'source_type' => StockMovement::class,
            'source_id' => $movement->id,
        ], $lines);

        // Auto-post inventory journals - MUST succeed for data integrity
        // If posting fails, the entire transaction should rollback to prevent
        // stock movement without corresponding accounting entry
        $this->journalService->post($entry);

        $movement->update(['journal_entry_id' => $entry->id]);
    }
}
