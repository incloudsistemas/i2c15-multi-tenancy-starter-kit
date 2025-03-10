<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_account_user', function (Blueprint $table) {
            // Conta de cliente
            $table->foreignId('tenant_account_id');
            $table->foreign('tenant_account_id')
                ->references('id')
                ->on('tenant_accounts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Usuário
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // Não permite usuários repetidos por conta.
            $table->unique(['tenant_account_id', 'user_id'], 'tenant_account_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tenant_account_user');
    }
};
