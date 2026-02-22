<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryJsonService;
use Illuminate\Support\Facades\File;

class ExportInventoryJson extends Command
{
    protected $signature = 'twinx:inventory-export {path? : Path to save the JSON file}';
    protected $description = 'Export all inventory data (Categories, Brands, Units, Warehouses, Products) to a single JSON file';

    public function handle(InventoryJsonService $service)
    {
        $path = $this->argument('path') ?: base_path('twinx_inventory_export.json');

        $this->info("Exporting Inventory Truth to JSON...");

        $data = $service->getData();

        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Successfully exported to: {$path}");
        return 0;
    }
}
