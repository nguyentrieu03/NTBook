<?php

namespace App\Models;

use App\Support\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'marketplace_account_id',
        'external_order_id',
        'buyer_username',
        'status',
        'ship_to_name',
        'ship_to_city',
        'ship_to_state',
        'ship_to_country',
        'ship_to_postal_code',
        'shipping_fee',
        'currency',
        'subtotal',
        'total',
        'ordered_at',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'shipping_fee' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'ordered_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function marketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeUnmapped(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::Unmapped);
    }

    public function hasUnmappedItems(): bool
    {
        return $this->items()->whereNull('product_variant_id')->exists();
    }
}
