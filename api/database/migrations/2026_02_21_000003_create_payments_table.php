<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->string('external_id')->nullable();
            $table->string('gateway');
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
