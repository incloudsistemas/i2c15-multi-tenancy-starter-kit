<?php

namespace Database\Seeders\System;

use App\Models\System\TenantAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTable();

        TenantAccount::factory(30)->create();
    }

    protected function truncateTable()
    {
        $this->command->info('Truncating Tenant Accounts table');
        Schema::disableForeignKeyConstraints();

        DB::table('tenant_accounts')
            ->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
