<?php

namespace App\Models\System;

use App\Enums\DefaultStatusEnum;
use App\Observers\System\TenantCategoryObserver;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantCategory extends Model
{
    use HasFactory, Sluggable, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'order',
        'featured',
        'status'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'status'   => DefaultStatusEnum::class,
    ];

    public function tenantAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            related: TenantAccount::class,
            table: 'tenant_account_tenant_category',
            foreignPivotKey: 'category_id',
            relatedPivotKey: 'tenant_account_id'
        );
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(related: Self::class, foreignKey: 'category_id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(related: Self::class, foreignKey: 'category_id');
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
     * EVENT LISTENERS.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TenantCategoryObserver::class);
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
}
