<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained('world_states')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('world_countries')->cascadeOnDelete();
            $table->string('name', 150);
            $table->index('state_id');
            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_cities');
    }
};
