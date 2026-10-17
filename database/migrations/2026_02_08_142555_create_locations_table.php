<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Locations table: hierarchical taxonomy for Country -> Region -> City.
     * Properties reference this table via location_id FK.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id('location_id');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->string('country');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('landmark')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('lat', 50)->nullable();
            $table->string('lng', 50)->nullable();
            $table->integer('zoom')->default(12);
            $table->char('country_code', 2)->nullable();
            $table->unsignedBigInteger('legacy_location_id')->nullable()->index();
            $table->timestamps();

            $table->index('city', 'idx_locations_city');
            $table->index('region', 'idx_locations_region');
            $table->index('area', 'idx_locations_area');
            $table->index('country', 'idx_locations_country');
            $table->index(['city', 'region'], 'idx_locations_city_region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
