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
        Schema::create('property_types', function (Blueprint $table) {
            $table->id('property_type_id');
            $table->string('title')->comment('Localized titles');
            $table->string('slug')->comment('Localized slugs');
            $table->string('marker_file')->nullable();
            $table->string('marker')->nullable();
            $table->unsignedBigInteger('legacy_listing_type_id')->nullable()->index();
            $table->enum('status', ['active', 'inactive']);
            $table->timestamps();

            $table->index('status', 'idx_property_types_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};
