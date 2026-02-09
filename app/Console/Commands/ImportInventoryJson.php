<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryJsonService;
use Illuminate\Support\Facades\File;

class ImportInventoryJson extends Command
{
    protected $signature = 'twinx:inventory-import {path? : Path to the JSON file}';
    protected $description = 'Import inventory data from a single JSON file (Upsert logic)';

    public function handle(InventoryJsonService $service)
    {
        $path = $this->argument('path') ?: base_path('twinx_inventory_export.json');

        if (!File::exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Importing Inventory Truth from JSON...");
        $json = File::get($path);
        $data = json_decode($json, true);

        if (!$data) {
            $this->error("Invalid JSON format.");
            return 1;
        }

        $service->importData($data);

        $this->info("Successfully imported inventory from JSON.");
        return 0;
    }
}
