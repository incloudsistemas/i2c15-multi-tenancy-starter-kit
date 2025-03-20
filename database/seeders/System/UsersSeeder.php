<?php

namespace Database\Seeders\System;

use App\Models\System\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        // user superadmin
        $superadmin = User::create([
            'name'              => 'InCloud.sistemas',
            'email'             => 'contato@incloudsistemas.com.br',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
        ]);

        $superadmin->assignRole('Superadministrador');

        // Attach to tenants
        // $tenantAccountIds = TenantAccount::pluck('id')
        //     ->toArray();

        // $superadmin->tenantAccounts()
        //     ->attach($tenantAccountIds);

        // Delay of 1 seconds
        sleep(1);

        User::factory(10)
            ->create()
            ->each(function ($user) {
                $user->assignRole('Administrador');
            });
    }

    protected function truncateTable()
    {
        $this->command->info('Truncating Users table');
        Schema::disableForeignKeyConstraints();

        DB::table('users')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
