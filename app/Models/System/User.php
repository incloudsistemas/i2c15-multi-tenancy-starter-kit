<?php

namespace App\Models\System;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\DateCast;
use App\Enums\ProfileInfos\EducationalLevelEnum;
use App\Enums\ProfileInfos\GenderEnum;
use App\Enums\ProfileInfos\MaritalStatusEnum;
use App\Enums\ProfileInfos\UserStatusEnum;
use App\Models\Polymorphics\Address;
use App\Observers\System\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants, HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'additional_emails',
        'phones',
        'cpf',
        'rg',
        'gender',
        'birth_date',
        'marital_status',
        'educational_level',
        'nationality',
        'citizenship',
        'complement',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'additional_emails' => 'array',
            'phones'            => 'array',
            'gender'            => GenderEnum::class,
            'birth_date'        => DateCast::class,
            'marital_status'    => MaritalStatusEnum::class,
            'educational_level' => EducationalLevelEnum::class,
            'status'            => UserStatusEnum::class,
        ];
    }

    public function address(): MorphOne
    {
        return $this->morphOne(related: Address::class, name: 'addressable');
    }

    public function tenantAccounts(): BelongsToMany
    {
        return $this->belongsToMany(related: TenantAccount::class);
    }

    public function ownTenants(): HasMany
    {
        return $this->hasMany(related: TenantAccount::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->tenantAccounts;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenantAccounts()
            ->whereKey($tenant)
            ->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ((int) $this->status->value === 0) {
            // auth()->logout();
            return false;
        }

        if ($panel->getId() === 'i2c-admin') {
            return auth()->user()->hasAnyRole(['Superadministrador']);
        }

        return true;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();
    }

    /**
     * EVENT LISTENER.
     *
     */

    protected static function boot()
    {
        parent::boot();
        self::observe(UserObserver::class);
    }

    /**
     * SCOPES.
     *
     */

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
