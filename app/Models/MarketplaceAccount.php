<?php

namespace App\Models;

use App\Support\Enums\MarketplaceHealthStatusEnum;
use App\Support\Enums\MarketplacePlatformEnum;
use App\Support\Enums\MarketplaceSiteCodeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketplaceAccount extends Model
{
    protected $fillable = [
        'platform',
        'name',
        'username',
        'site_code',
        'health_status',
        'sync_enabled',
        'is_active',
        'token_expires_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => MarketplacePlatformEnum::class,
            'site_code' => MarketplaceSiteCodeEnum::class,
            'health_status' => MarketplaceHealthStatusEnum::class,
            'sync_enabled' => 'boolean',
            'is_active' => 'boolean',
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function token(): HasOne
    {
        return $this->hasOne(MarketplaceAccountToken::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'marketplace_account_user');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSyncEnabled(Builder $query): Builder
    {
        return $query->where('sync_enabled', true);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->isPast();
    }
}
