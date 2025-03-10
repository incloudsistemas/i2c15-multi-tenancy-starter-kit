<?php

namespace App\Observers\System;

use App\Models\System\TenantAccount;

class TenantAccountObserver
{
    /**
     * Handle the TenantAccount "created" event.
     */
    public function created(TenantAccount $tenantAccount): void
    {
        //
    }

    /**
     * Handle the TenantAccount "updated" event.
     */
    public function updated(TenantAccount $tenantAccount): void
    {
        //
    }

    /**
     * Handle the TenantAccount "deleted" event.
     */
    public function deleted(TenantAccount $tenantAccount): void
    {
        $tenantAccount->slug = $tenantAccount->slug . '//deleted_' . md5(uniqid());
        $tenantAccount->cnpj = $tenantAccount->cnpj . '//deleted_' . md5(uniqid());
        $tenantAccount->domain = $tenantAccount->domain . '//deleted_' . md5(uniqid());

        $tenantAccount->save();
    }

    /**
     * Handle the TenantAccount "restored" event.
     */
    public function restored(TenantAccount $tenantAccount): void
    {
        //
    }

    /**
     * Handle the TenantAccount "force deleted" event.
     */
    public function forceDeleted(TenantAccount $tenantAccount): void
    {
        //
    }
}
