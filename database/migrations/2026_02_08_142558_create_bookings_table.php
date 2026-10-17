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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->string('booking_code', 30)->unique();
            $table->enum('booking_source', ['admin_dashboard', 'website'])->default('admin_dashboard');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name', 100);
            $table->string('guest_email', 255);
            $table->string('guest_phone', 30);
            $table->string('guest_address', 255)->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('number_adults')->default(1);
            $table->unsignedSmallInteger('number_children')->default(0);
            $table->unsignedSmallInteger('number_nights')->default(1);
            $table->decimal('admin_base_price_per_night', 12, 2)->default(0.00);
            $table->decimal('rental_price', 12, 2)->default(0.00);
            $table->decimal('addon_total', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('coupon_discount', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->foreignId('addon_id')->nullable()->constrained('addons', 'addon_id')->nullOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained('discounts', 'discount_id')->nullOnDelete();
            $table->enum('payment_method', ['manual_cash', 'bank_transfer', 'stripe', 'paypal', 'cheque', 'others'])->default('manual_cash');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid');
            $table->enum('booking_status', ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->boolean('confirmation_email_sent')->default(false);
            $table->timestamp('confirmation_email_sent_at')->nullable();
            $table->text('guest_message')->nullable();
            $table->text('internal_notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('booking_status', 'idx_bookings_booking_status');
            $table->index('status', 'idx_bookings_status');
            $table->index('check_in', 'idx_bookings_check_in');
            $table->index('check_out', 'idx_bookings_check_out');
            $table->index(
                ['property_id', 'status', 'booking_status', 'check_in', 'check_out'],
                'idx_bookings_property_status_dates'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
