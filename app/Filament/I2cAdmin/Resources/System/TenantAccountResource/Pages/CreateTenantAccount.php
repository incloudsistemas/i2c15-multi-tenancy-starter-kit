<?php

namespace App\Filament\I2cAdmin\Resources\System\TenantAccountResource\Pages;

use App\Filament\I2cAdmin\Resources\System\TenantAccountResource;
use App\Models\System\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class CreateTenantAccount extends CreateRecord
{
    protected static string $resource = TenantAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->createAndAttachUser();
        $this->createAddress();
    }

    protected function createAndAttachUser(): void
    {
        if ($this->data['user']['password']) {
            $this->data['user']['password'] = Hash::make($this->data['user']['password']);
        }

        $user = User::firstOrCreate(
            ['email' => $this->data['user']['email']],
            $this->data['user']
        );

        // 2 - Cliente
        if (!$user->roles()->where('id', 2)->exists()) {
            $user->roles()
                ->attach(2);
        }

        $adminUsers = User::whereHas('roles', function (Builder $query): Builder {
            return $query->whereIn('id', [1, 3]); // 1 - Superadmin, 3 - Admin
        })
            ->pluck('id')
            ->toArray();

        $users = array_unique(array_merge($adminUsers, [$user->id]));

        $this->record->users()
            ->attach($users);
    }

    protected function createAddress(): void
    {
        $this->data['address']['is_main'] = true;

        $this->record->address()
            ->create($this->data['address']);
    }
}
