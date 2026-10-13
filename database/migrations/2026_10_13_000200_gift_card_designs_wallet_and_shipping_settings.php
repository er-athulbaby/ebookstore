<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-managed gift card designs ("Happy Birthday ₹500" etc.).
        Schema::create('gift_card_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->unsignedInteger('value'); // paise
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Who the gift card is for travels cart item -> order item -> voucher.
        foreach (['cart_items', 'order_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('gift_card_design_id')->nullable()->after('gift_card_value')->constrained()->nullOnDelete();
                $table->json('gift_card_meta')->nullable()->after('gift_card_design_id');
            });
        }

        Schema::table('gift_vouchers', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('recipient_email');
            $table->foreignId('gift_card_design_id')->nullable()->after('value')->constrained()->nullOnDelete();
            $table->foreignId('purchased_by_user_id')->nullable()->after('gift_card_design_id')->constrained('users')->nullOnDelete();
        });

        // 'free' = nothing left to pay after coupon/wallet, placed without the payment gateway.
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('razorpay','cod','wallet','free') NOT NULL DEFAULT 'razorpay'");

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Start with the four amounts the store already sold, as plain designs.
        $now = now();
        foreach ([250, 500, 1000, 2000] as $i => $rupees) {
            DB::table('gift_card_designs')->insert([
                'name' => 'Gift Card', 'value' => $rupees * 100, 'is_active' => true,
                'sort_order' => $i, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('razorpay','cod','wallet') NOT NULL DEFAULT 'razorpay'");
        Schema::table('gift_vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchased_by_user_id');
            $table->dropConstrainedForeignId('gift_card_design_id');
            $table->dropColumn('sender_name');
        });
        foreach (['cart_items', 'order_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('gift_card_design_id');
                $table->dropColumn('gift_card_meta');
            });
        }
        Schema::dropIfExists('gift_card_designs');
    }
};
