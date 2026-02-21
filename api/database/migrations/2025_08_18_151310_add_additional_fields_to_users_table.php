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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->after('email');
            $table->string('type', 50)->nullable()->after('cpf');
            $table->string('position', 100)->nullable()->after('type');
            $table->string('phone', 20)->nullable()->after('position');
            $table->index('cpf');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['cpf']);
            $table->dropIndex(['type']);
            $table->dropColumn(['cpf', 'type', 'position', 'phone']);
        });
    }
};
