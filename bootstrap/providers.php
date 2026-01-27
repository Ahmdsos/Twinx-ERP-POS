<?php

return [
    App\Providers\AppServiceProvider::class,

    // Twinx ERP Modules
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Accounting\Providers\AccountingServiceProvider::class,
    Modules\Inventory\Providers\InventoryServiceProvider::class,
    Modules\Purchasing\Providers\PurchasingServiceProvider::class,
    Modules\Sales\Providers\SalesServiceProvider::class,
];
