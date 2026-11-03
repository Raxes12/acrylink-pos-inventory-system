<?php

namespace App\Support;

final class StockAdjustmentOptions
{
    public const TYPES = [
        'stock_in' => 'Stock Increase (+)',
        'stock_out' => 'Stock Decrease (-)',
    ];

    public const REASONS = [
        'stock_in' => [
            'inventory_count_correction' => 'Inventory Count Correction',
            'found_stock' => 'Found Stock',
            'returned_to_inventory' => 'Returned to Inventory',
            'data_entry_correction' => 'Data Entry Correction',
            'other' => 'Other',
        ],
        'stock_out' => [
            'damaged' => 'Damaged',
            'lost_missing' => 'Lost / Missing',
            'inventory_count_correction' => 'Inventory Count Correction',
            'internal_use' => 'Internal Use',
            'defective' => 'Defective',
            'data_entry_correction' => 'Data Entry Correction',
            'other' => 'Other',
        ],
    ];

    public static function reasonsFor(?string $type): array
    {
        return self::REASONS[$type] ?? [];
    }

    public static function typeLabel(?string $type): string
    {
        return self::TYPES[$type] ?? str((string) $type)->replace('_', ' ')->title()->toString();
    }

    public static function reasonLabel(?string $type, ?string $reason): ?string
    {
        if (! filled($reason)) {
            return null;
        }

        return self::reasonsFor($type)[$reason]
            ?? str($reason)->replace('_', ' ')->title()->toString();
    }

    public static function remarksRequired(?string $reason): bool
    {
        return in_array($reason, ['other', 'lost_missing'], true);
    }

    public static function stockStatus(int $quantity, int $minimum, int $critical): string
    {
        return $quantity <= 0
            ? 'Out of Stock'
            : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));
    }
}
