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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id('currency_id');
            $table->timestamps();
            $table->string('title', 50);
            $table->string('currency_code', 10);
            $table->float('currency_rate')->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->enum('is_default', ['yes', 'no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
