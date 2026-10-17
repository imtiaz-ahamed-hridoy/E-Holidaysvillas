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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id('inquiry_id');
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('email', 255);
            $table->string('phone', 50);
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->integer('no_of_guests')->nullable();
            $table->text('message');
            $table->text('reply')->nullable();
            $table->enum('is_replied', ['yes', 'no']);
            $table->timestamp('replied_at')->nullable();
            $table->enum('status', ['pending', 'replied', 'spam'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
