<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->char('iso2', 2)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_countries');
    }
};
