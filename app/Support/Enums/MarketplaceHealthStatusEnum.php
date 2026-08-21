<?php

namespace App\Support\Enums;

enum MarketplaceHealthStatusEnum: string
{
    case Ok = 'ok';
    case Warn = 'warn';
    case Err = 'err';

    public function label(): string
    {
        return match ($this) {
            self::Ok => 'Ổn định',
            self::Warn => 'Cảnh báo',
            self::Err => 'Lỗi',
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
