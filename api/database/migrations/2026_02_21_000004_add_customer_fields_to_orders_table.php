<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('gateway');
            $table->string('customer_name')->nullable()->after('payment_method');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_cpf_cnpj', 20)->nullable()->after('customer_email');
            $table->string('customer_phone', 20)->nullable()->after('customer_cpf_cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'customer_name',
                'customer_email',
                'customer_cpf_cnpj',
                'customer_phone',
            ]);
        });
    }
};
