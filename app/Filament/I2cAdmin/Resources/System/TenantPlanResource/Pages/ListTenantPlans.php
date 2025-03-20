<?php

namespace App\Filament\I2cAdmin\Resources\System\TenantPlanResource\Pages;

use App\Filament\I2cAdmin\Resources\System\TenantPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantPlans extends ListRecords
{
    protected static string $resource = TenantPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
