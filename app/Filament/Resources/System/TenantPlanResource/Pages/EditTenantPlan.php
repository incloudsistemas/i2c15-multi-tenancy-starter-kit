<?php

namespace App\Filament\Resources\System\TenantPlanResource\Pages;

use App\Filament\Resources\System\TenantPlanResource;
use App\Models\System\TenantPlan;
use App\Services\System\TenantPlanService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantPlan extends EditRecord
{
    protected static string $resource = TenantPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(
                    fn(TenantPlanService $service, Actions\DeleteAction $action, TenantPlan $record) =>
                    $service->preventDeleteIf(action: $action, tenantPlan: $record)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
