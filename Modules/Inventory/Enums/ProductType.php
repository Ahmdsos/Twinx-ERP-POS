<?php

namespace Modules\Inventory\Enums;

/**
 * ProductType Enum - Types of products
 */
enum ProductType: string
{
    case GOODS = 'goods';           // Physical products with inventory
    case SERVICE = 'service';       // Non-physical services
    case CONSUMABLE = 'consumable'; // Items consumed internally

    public function label(): string
    {
        return match ($this) {
            self::GOODS => 'Goods',
            self::SERVICE => 'Service',
            self::CONSUMABLE => 'Consumable',
        };
    }

    public function tracksInventory(): bool
    {
        return $this === self::GOODS;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
