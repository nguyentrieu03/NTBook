<?php

namespace App\Models;

use App\Support\Enums\ListingMappingStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListingItem extends Model
{
    protected $fillable = [
        'listing_id',
        'seller_sku',
        'variation_label',
        'price',
        'quantity',
        'product_variant_id',
        'mapping_status',
        'mapped_at',
        'mapped_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'mapping_status' => ListingMappingStatusEnum::class,
            'mapped_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function mappedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mapped_by');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeUnmapped(Builder $query): Builder
    {
        return $query->where('mapping_status', ListingMappingStatusEnum::Unmapped);
    }

    public function scopeMapped(Builder $query): Builder
    {
        return $query->where('mapping_status', ListingMappingStatusEnum::Mapped);
    }

    public function scopeIgnored(Builder $query): Builder
    {
        return $query->where('mapping_status', ListingMappingStatusEnum::Ignored);
    }

    public function isMapped(): bool
    {
        return $this->mapping_status?->isMapped() ?? false;
    }
}
