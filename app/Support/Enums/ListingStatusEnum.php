<?php

namespace App\Support\Enums;

enum ListingStatusEnum: string
{
    case Active = 'active';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Đang chạy',
            self::Ended => 'Đã kết thúc',
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
