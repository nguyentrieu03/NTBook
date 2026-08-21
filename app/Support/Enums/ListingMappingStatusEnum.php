<?php

namespace App\Support\Enums;

enum ListingMappingStatusEnum: string
{
    case Unmapped = 'unmapped';
    case Mapped = 'mapped';
    case Ignored = 'ignored';

    public function label(): string
    {
        return match ($this) {
            self::Unmapped => 'Chưa map',
            self::Mapped => 'Đã map SKU chuẩn',
            self::Ignored => 'Bỏ qua',
        };
    }

    public function isMapped(): bool
    {
        return $this === self::Mapped;
    }

    public static function options(): array
    {
        return array_map(
            fn (self $e) => ['value' => $e->value, 'label' => $e->label()],
            self::cases()
        );
    }
}
