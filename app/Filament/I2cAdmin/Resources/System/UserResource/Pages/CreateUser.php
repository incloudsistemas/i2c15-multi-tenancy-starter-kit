<?php

namespace App\Filament\I2cAdmin\Resources\System\UserResource\Pages;

use App\Filament\I2cAdmin\Resources\System\UserResource;
use App\Models\System\TenantAccount;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make($data['password']);

        unset($data['password_confirmation']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->updateOwnTenants();
        $this->syncTenantAccounts();
        $this->createAddress();
    }

    protected function updateOwnTenants(): void
    {
        if (!empty($this->data['tenant_accounts'])) {
            TenantAccount::whereIn('id', $this->data['tenant_accounts'])
                ->update(['user_id' => $this->record->id]);
        }
    }

    protected function syncTenantAccounts(): void
    {
        $tenantAccountIds = $this->data['tenant_accounts'];

        if ($this->record->hasAnyRole(['Superadministrador', 'Administrador'])) {
            $tenantAccountIds = TenantAccount::pluck('id')
                ->toArray();
        }

        $this->record->tenantAccounts()
            ->sync($tenantAccountIds);
    }

    protected function createAddress(): void
    {
        $this->data['address']['is_main'] = true;

        $this->record->address()
            ->create($this->data['address']);
    }
}
