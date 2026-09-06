<?php

namespace App\Support\Enums;

enum MarketplacePlatformEnum: string
{
    case Ebay = 'ebay';

    public function label(): string
    {
        return match ($this) {
            self::Ebay => 'eBay',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $e) => ['value' => $e->value, 'label' => $e->label()],
            self::cases()
        );
    }
}
