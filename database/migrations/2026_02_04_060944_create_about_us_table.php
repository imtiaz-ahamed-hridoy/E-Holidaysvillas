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
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();

            // Hero Section
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();

            // Intro Section
            $table->text('intro_mission')->nullable();
            $table->text('intro_vision')->nullable();
            $table->text('intro_story')->nullable();
            $table->string('intro_image')->nullable();

            // Travel Section
            $table->string('travel_title')->nullable();
            $table->text('travel_text')->nullable();
            $table->json('travel_images')->nullable(); // For the collage images

            // Why Choose Us
            $table->json('why_choose_us')->nullable(); // Array of title/desc/icon

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
