<?php

namespace App\Models;

use App\Support\Enums\ListingStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $fillable = [
        'marketplace_account_id',
        'external_item_id',
        'title',
        'status',
        'listing_url',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ListingStatusEnum::class,
            'synced_at' => 'datetime',
        ];
    }

    public function marketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ListingItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ListingStatusEnum::Active);
    }
}
