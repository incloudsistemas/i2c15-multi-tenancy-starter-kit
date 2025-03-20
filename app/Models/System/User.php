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
use App\Services\System\RoleService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
            return auth()->user()->hasAnyRole(['Superadministrador', 'Administrador']);
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

    public function scopeByAuthUserRoles(Builder $query, User $user): Builder
    {
        $rolesToAvoid = RoleService::getArrayOfRolesToAvoidByAuthUserRoles(user: $user);

        return $query->whereHas('roles', function (Builder $query) use ($rolesToAvoid): Builder {
            return $query->whereNotIn('id', $rolesToAvoid);
        });
    }

    public function scopeWhereHasRolesAvoidingClients(Builder $query): Builder
    {
        $rolesToAvoid = [2]; // 2 - Cliente

        return $query->whereHas('roles', function (Builder $query) use ($rolesToAvoid): Builder {
            return $query->whereNotIn('id', $rolesToAvoid);
        });
    }

    public function scopeByStatuses(Builder $query, array $statuses = [1]): Builder
    {
        return $query->whereHasRolesAvoidingClients()
            ->whereIn('status', $statuses);
    }

    /**
     * MUTATORS.
     *
     */

    /**
     * CUSTOMS.
     *
     */

    public function getDisplayAdditionalEmailsAttribute(): ?array
    {
        $additionalEmails = [];

        if (isset($this->additional_emails[0])) {
            foreach ($this->additional_emails as $email) {
                $additionalEmail = $email['email'];

                if (!empty($email['name'])) {
                    $additionalEmail .= " ({$email['name']})";
                }

                $additionalEmails[] = $additionalEmail;
            }
        }

        return !empty($additionalEmails) ? $additionalEmails : null;
    }

    public function getDisplayMainPhoneAttribute(): ?string
    {
        return $this->phones[0]['number'] ?? null;
    }

    public function getDisplayMainPhoneWithNameAttribute(): ?string
    {
        if (isset($this->phones[0]['number'])) {
            $mainPhone = $this->phones[0]['number'];
            $phoneName = $this->phones[0]['name'] ?? null;

            if (!empty($phoneName)) {
                $mainPhone .= " ({$phoneName})";
            }

            return $mainPhone;
        }

        return null;
    }

    public function getDisplayAdditionalPhonesAttribute(): ?array
    {
        $additionalPhones = [];

        if (isset($this->phones[1]['number'])) {
            foreach (array_slice($this->phones, 1) as $phone) {
                $additionalPhone = $phone['number'];

                if (!empty($phone['name'])) {
                    $additionalPhone .= " ({$phone['name']})";
                }

                $additionalPhones[] = $additionalPhone;
            }
        }

        return !empty($additionalPhones) ? $additionalPhones : null;
    }

    public function getDisplayBirthDateAttribute(): ?string
    {
        return isset($this->birth_date)
            ? ConvertEnToPtBrDate(date: $this->birth_date)
            : null;
    }

    public function getFeaturedImageAttribute(): ?Media
    {
        $featuredImage = $this->getFirstMedia('avatar');

        if (!$featuredImage) {
            $featuredImage = $this->getFirstMedia('images');
        }

        return $featuredImage ?? null;
    }

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('attachments');
    }
}
