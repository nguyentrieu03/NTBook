<?php

namespace App\Support\Enums;

enum ProductStatusEnum: string 
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string 
    {
        return match ($this) {
            self::Draft => 'Bản nháp',
            self::Active => 'Đang bán',
            self::Archived => 'Đã lưu trữ',
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