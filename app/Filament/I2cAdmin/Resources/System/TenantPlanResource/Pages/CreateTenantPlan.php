<?php

namespace App\Filament\I2cAdmin\Resources\System\TenantPlanResource\Pages;

use App\Filament\I2cAdmin\Resources\System\TenantPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantPlan extends CreateRecord
{
    protected static string $resource = TenantPlanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
