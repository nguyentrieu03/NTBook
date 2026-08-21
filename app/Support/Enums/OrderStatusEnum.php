<?php

namespace App\Support\Enums;

enum OrderStatusEnum: string
{
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Unmapped = 'unmapped';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Đã thanh toán',
            self::Shipped => 'Đã gửi hàng',
            self::Delivered => 'Đã giao',
            self::Cancelled => 'Đã huỷ',
            self::Unmapped => 'Chưa map SKU',
        };
    }

    public function isUnmapped(): bool
    {
        return $this === self::Unmapped;
    }

    public static function options(): array
    {
        return array_map(
            fn (self $e) => ['value' => $e->value, 'label' => $e->label()],
            self::cases()
        );
    }
}
