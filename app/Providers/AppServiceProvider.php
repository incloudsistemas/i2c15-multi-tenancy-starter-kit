<?php

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch->panels([
                'admin',
                'tenant',
            ])
                ->modalHeading('Painéis')
                // ->simple()
                ->modalWidth('sm')
                ->labels([
                    'admin'  => __('Painel Administrador'),
                    'tenant' => __('Painel Cliente'),
                ])
                ->icons([
                    'admin'  => 'heroicon-m-cog-6-tooth',
                    'tenant' => 'heroicon-s-user',
                ], $asImage = false)
                ->iconSize(32)
                ->visible(
                    fn(): bool =>
                    auth()->user()->hasRole('Superadministrador')
                );
        });

        // Morph map for polymorphic relations.
        Relation::morphMap([
            'users'             => 'App\Models\System\User',
            'tenant_plans'      => 'App\Models\System\TenantPlan',
            'tenant_accounts'   => 'App\Models\System\TenantAccount',
            'tenant_categories' => 'App\Models\System\TenantCategory',
            'permissions'       => 'App\Models\System\Permission',
            'roles'             => 'App\Models\System\Role',

            'addresses' => 'App\Models\Polymorphics\Address',
        ]);
    }
}
