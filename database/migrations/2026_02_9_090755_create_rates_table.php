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
        Schema::create('rates', function (Blueprint $table) {
            $table->id('rate_id');

            $table->foreignId('property_id')
                ->constrained('properties', 'property_id')
                ->cascadeOnDelete();

            $table->foreignId('unit_type_id')
                ->nullable()
                ->constrained('unit_types', 'unit_type_id')
                ->nullOnDelete();

            // Date range
            $table->date('start_date');
            $table->date('end_date');

            // Pricing
            $table->decimal('nightly_rate', 10, 2);
            $table->decimal('nightly_rate_weekend', 10, 2)->nullable();

            // Stay rules
            $table->unsignedInteger('min_stay')->default(1);
            $table->enum('changeover_day', ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])->nullable();

            // Extra guest pricing
            $table->unsignedInteger('persons')->default(1);
            $table->decimal('extra_person_price_night', 10, 2)->default(0);

            // Status flags
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('is_special_offer', ['active', 'inactive'])->default('inactive');
            $table->enum('is_rate_overwrite', ['active', 'inactive'])->default('inactive');

            $table->unsignedBigInteger('legacy_rate_id')->nullable()->index();

            $table->timestamps();

            // Indexes for performance
            $table->index(['property_id', 'unit_type_id']);
            $table->index(['start_date', 'end_date']);
            $table->index('status');
            $table->index('nightly_rate', 'idx_rates_nightly_rate');
            $table->index(
                ['property_id', 'status', 'start_date', 'end_date', 'nightly_rate'],
                'idx_rates_property_status_dates_price'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
