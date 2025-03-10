<?php

namespace App\Models\System;

use App\Enums\DefaultStatusEnum;
use App\Enums\TenantAccountRoleEnum;
use App\Models\Polymorphics\Address;
use App\Observers\System\TenantAccountObserver;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TenantAccount extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, SoftDeletes;

    protected $fillable = [
        'plan_id',
        'user_id',
        'role',
        'name',
        'slug',
        'cnpj',
        'domain',
        'emails',
        'phones',
        'complement',
        'social_media',
        'opening_hours',
        'theme',
        'status',
        'settings',
        'custom',
    ];

    protected $casts = [
        'role'          => TenantAccountRoleEnum::class,
        'emails'        => 'array',
        'phones'        => 'array',
        'social_media'  => 'array',
        'opening_hours' => 'array',
        'theme'         => 'array',
        'status'        => DefaultStatusEnum::class,
        'settings'      => 'array',
        'custom'        => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            related: TenantCategory::class,
            table: 'tenant_account_tenant_category',
            foreignPivotKey: 'tenant_account_id',
            relatedPivotKey: 'category_id'
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(related: TenantPlan::class, foreignKey: 'plan_id');
    }

    public function address(): MorphOne
    {
        return $this->morphOne(related: Address::class, name: 'addressable');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
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
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(TenantAccountObserver::class);
    }

    /**
     * SCOPES.
     *
     */

    public function scopeByRoles(Builder $query, array $roles): Builder
    {
        return $query->whereIn('role', $roles);
    }

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

    public function getFeaturedImageAttribute(): ?Media
    {
        $featuredImage = $this->getFirstMedia('avatar');

        if (!$featuredImage) {
            $featuredImage = $this->getFirstMedia('images');
        }

        return $featuredImage ?? null;
    }
}
