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
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id('mail_template_id');
            $table->timestamps();
            $table->string('sender_email', 255);
            $table->string('sender_name', 255);
            $table->string('subject', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('alias', 80);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->string('bcc_copy', 50);
            $table->enum('is_bulk_mail', ['active', 'inactive'])->default('inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
