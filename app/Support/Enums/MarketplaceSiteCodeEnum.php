<?php

namespace App\Support\Enums;

enum MarketplaceSiteCodeEnum: string
{
    case Us = 'EBAY_US';
    case Gb = 'EBAY_GB';
    case Au = 'EBAY_AU';
    case De = 'EBAY_DE';

    public function label(): string
    {
        return match ($this) {
            self::Us => 'eBay US',
            self::Gb => 'eBay UK',
            self::Au => 'eBay AU',
            self::De => 'eBay DE',
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
