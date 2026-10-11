<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->enum('status', [
                'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded',
            ])->default('pending')->index();

            // physical-only, digital-only, or a cart mixing both.
            $table->enum('fulfillment_type', ['physical', 'digital', 'mixed'])->default('physical');

            $table->unsignedInteger('subtotal'); // paise
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('shipping_amount')->default(0);
            $table->unsignedInteger('total');

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();

            $table->string('contact_email');
            $table->string('contact_phone')->nullable();

            $table->enum('payment_method', ['razorpay', 'cod'])->default('razorpay');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();

            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
