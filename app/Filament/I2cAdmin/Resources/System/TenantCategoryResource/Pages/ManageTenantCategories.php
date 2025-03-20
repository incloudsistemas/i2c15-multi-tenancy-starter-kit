<?php

namespace App\Filament\I2cAdmin\Resources\System\TenantCategoryResource\Pages;

use App\Filament\I2cAdmin\Resources\System\TenantCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTenantCategories extends ManageRecords
{
    protected static string $resource = TenantCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
