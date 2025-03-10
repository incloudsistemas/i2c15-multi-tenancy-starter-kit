<?php

namespace App\Observers\System;

use App\Models\System\TenantPlan;

class TenantPlanObserver
{
    /**
     * Handle the TenantPlan "created" event.
     */
    public function created(TenantPlan $tenantPlan): void
    {
        //
    }

    /**
     * Handle the TenantPlan "updated" event.
     */
    public function updated(TenantPlan $tenantPlan): void
    {
        //
    }

    /**
     * Handle the TenantPlan "deleted" event.
     */
    public function deleted(TenantPlan $tenantPlan): void
    {
        $tenantPlan->slug = $tenantPlan->slug . '//deleted_' . md5(uniqid());

        $tenantPlan->save();
    }

    /**
     * Handle the TenantPlan "restored" event.
     */
    public function restored(TenantPlan $tenantPlan): void
    {
        //
    }

    /**
     * Handle the TenantPlan "force deleted" event.
     */
    public function forceDeleted(TenantPlan $tenantPlan): void
    {
        //
    }
}
