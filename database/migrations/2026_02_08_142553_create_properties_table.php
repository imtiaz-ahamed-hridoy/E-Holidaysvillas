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
        Schema::create('properties', function (Blueprint $table) {
            $table->id('property_id');

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('property_status', ['inactive', 'active', 'pending', 'sold', 'withdrawn', 'others'])->nullable();

            $table->enum('property_main_type', ['sale', 'rent', 'others'])->nullable();

            $table->foreignId('property_type_id')->nullable()->constrained('property_types', 'property_type_id')->cascadeOnDelete();

            $table->foreignId('unit_type_id')->nullable()->constrained('unit_types', 'unit_type_id')->cascadeOnDelete();

            $table->foreignId('property_category_id')->nullable()->constrained('property_categories', 'property_category_id')->cascadeOnDelete();

            $table->foreignId('currency_id')->nullable()->constrained('currencies', 'currency_id')->cascadeOnDelete();

            $table->foreignId('discount_id')->nullable()->constrained('discounts', 'discount_id')->cascadeOnDelete();

            $table->foreignId('addon_id')->nullable()->constrained('addons', 'addon_id')->cascadeOnDelete();
            $table->string('title', 100);

            $table->text('description');


            $table->enum('stars', ["1 Star", "2 Stars", "3 Stars", "4 Stars", "5 Stars"]);


            $table->date('available_from');

            $table->tinyInteger('no_bedrooms');

            $table->tinyInteger('no_bathrooms');

            $table->tinyInteger('no_floors');

            $table->tinyInteger('no_guests');

            $table->tinyInteger('min_no_of_stay');

            $table->boolean('parking');

            $table->boolean('car_essential')->default(false);

            $table->decimal('total_square_feet', 10, 2);

            $table->boolean('allow_bookings');

            $table->string('property_meta_title')->nullable();

            $table->string('property_meta_description')->nullable();

            $table->string('property_meta_keywords')->nullable();

            $table->enum('status', ['active', 'inactive']);

            $table->unsignedBigInteger('legacy_listing_id')->nullable()->index();

            $table->unsignedBigInteger('legacy_location_id')->nullable()->index();

            $table->timestamps();

            $table->index('status', 'idx_properties_status');
            $table->index('allow_bookings', 'idx_properties_allow_bookings');
            $table->index('no_guests', 'idx_properties_no_guests');
            $table->index('created_at', 'idx_properties_created_at');
            $table->index(
                ['status', 'allow_bookings', 'property_type_id', 'no_guests', 'created_at'],
                'idx_properties_search_core'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
