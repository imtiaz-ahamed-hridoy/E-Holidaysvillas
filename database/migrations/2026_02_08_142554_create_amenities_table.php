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
        Schema::create('amenities', function (Blueprint $table) {
            $table->id('amenity_id');
            $table->string('title', 100);
            $table->foreignId('property_id')->nullable()->constrained('properties', 'property_id')->nullOnDelete();
            $table->text('icon')->nullable();
            $table->enum('icon_type', ['image', 'code'])->default('image');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
