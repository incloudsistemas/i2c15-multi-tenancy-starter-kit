<?php

namespace App\Filament\I2cAdmin\Resources\System\TenantAccountResource\Pages;

use App\Filament\I2cAdmin\Resources\System\TenantAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantAccounts extends ListRecords
{
    protected static string $resource = TenantAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
