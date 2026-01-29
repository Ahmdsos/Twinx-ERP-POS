<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * CacheService
 * Centralized caching service for dashboard and reports
 */
class CacheService
{
    // Cache TTL in seconds
    const TTL_SHORT = 300;     // 5 minutes
    const TTL_MEDIUM = 1800;   // 30 minutes  
    const TTL_LONG = 3600;     // 1 hour

    // Cache keys
    const KEY_DASHBOARD = 'dashboard_stats';
    const KEY_ACCOUNTS_TREE = 'accounts_tree';
    const KEY_PRODUCTS_COUNT = 'products_count';
    const KEY_CUSTOMERS_COUNT = 'customers_count';
    const KEY_SUPPLIERS_COUNT = 'suppliers_count';
    const KEY_LOW_STOCK = 'low_stock_products';

    /**
     * Get or set cached dashboard data
     */
    public static function getDashboard(callable $callback): array
    {
        return Cache::remember(self::KEY_DASHBOARD, self::TTL_SHORT, $callback);
    }

    /**
     * Get or set cached accounts tree
     */
    public static function getAccountsTree(callable $callback): array
    {
        return Cache::remember(self::KEY_ACCOUNTS_TREE, self::TTL_LONG, $callback);
    }

    /**
     * Get or set cached products count
     */
    public static function getProductsCount(callable $callback): int
    {
        return Cache::remember(self::KEY_PRODUCTS_COUNT, self::TTL_MEDIUM, $callback);
    }

    /**
     * Get or set cached low stock products
     */
    public static function getLowStockProducts(callable $callback): array
    {
        return Cache::remember(self::KEY_LOW_STOCK, self::TTL_SHORT, $callback);
    }

    /**
     * Clear dashboard cache
     */
    public static function clearDashboard(): void
    {
        Cache::forget(self::KEY_DASHBOARD);
    }

    /**
     * Clear accounts tree cache
     */
    public static function clearAccountsTree(): void
    {
        Cache::forget(self::KEY_ACCOUNTS_TREE);
    }

    /**
     * Clear all inventory-related caches
     */
    public static function clearInventory(): void
    {
        Cache::forget(self::KEY_PRODUCTS_COUNT);
        Cache::forget(self::KEY_LOW_STOCK);
        Cache::forget(self::KEY_DASHBOARD);
    }

    /**
     * Clear all ERP caches
     */
    public static function clearAll(): void
    {
        Cache::forget(self::KEY_DASHBOARD);
        Cache::forget(self::KEY_ACCOUNTS_TREE);
        Cache::forget(self::KEY_PRODUCTS_COUNT);
        Cache::forget(self::KEY_CUSTOMERS_COUNT);
        Cache::forget(self::KEY_SUPPLIERS_COUNT);
        Cache::forget(self::KEY_LOW_STOCK);
    }

    /**
     * Remember with automatic tag-based invalidation
     */
    public static function rememberForModel(string $model, string $key, int $ttl, callable $callback)
    {
        $fullKey = strtolower(class_basename($model)) . '_' . $key;
        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Clear cache for a specific model
     */
    public static function forgetForModel(string $model): void
    {
        $prefix = strtolower(class_basename($model)) . '_';
        // Note: This requires cache tags for full functionality
        // For now, we clear known keys
        self::clearAll();
    }
}
