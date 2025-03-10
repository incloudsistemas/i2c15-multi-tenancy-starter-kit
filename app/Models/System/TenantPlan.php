<?php

namespace App\Models\System;

use App\Casts\FloatCast;
use App\Enums\DefaultStatusEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantPlan extends Model
{
    use HasFactory, Sluggable, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'complement',
        'monthly_price',
        'monthly_price_notes',
        'annual_price',
        'annual_price_notes',
        'best_benefit_cost',
        'order',
        'status',
        'features',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price'     => FloatCast::class,
            'annual_price'      => FloatCast::class,
            'best_benefit_cost' => 'boolean',
            'status'            => DefaultStatusEnum::class,
            'features'          => 'array',
            'settings'          => 'array',
        ];
    }

    public function tenantAccounts(): HasMany
    {
        return $this->hasMany(related: TenantAccount::class);
    }

    public function sluggable(): array
    {
        if (!empty($this->slug)) {
            return [];
        }

        return [
            'slug' => [
                'source'   => 'name',
                'onUpdate' => true,
            ],
        ];
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */

    public function getDisplayMonthlyPriceAttribute(): ?string
    {
        return $this->monthly_price
            ? number_format($this->monthly_price, 2, ',', '.')
            : null;
    }

    public function getDisplayAnnualPriceAttribute(): ?string
    {
        return $this->annual_price
            ? number_format($this->annual_price, 2, ',', '.')
            : null;
    }

    public function getDisplayAnnualDiscountAttribute(): ?string
    {
        if (!$this->monthly_price || !$this->annual_price) {
            return null;
        }

        $discount = ($this->monthly_price * 12) - $this->annual_price;

        return number_format($discount, 2, ',', '.');
    }

    public function getDisplayAnnualDiscountMarginAttribute(): ?string
    {
        if (!$this->monthly_price || !$this->annual_price) {
            return null;
        }

        $totalMonthly = $this->monthly_price * 12;

        $discount = $totalMonthly - $this->annual_price;
        $discountMargin = ($discount / $totalMonthly) * 100;
        $discountMargin = round(floatval($discountMargin), precision: 2);

        return number_format($discountMargin, 2, ',', '.') . '%';
    }
}
