<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            
            $table->string('phone', 15)->unique();
            $table->string('email')->nullable()->unique();
            $table->enum('role', ['rider','driver','admin'])->default('rider');
            $table->string('avatar_url')->nullable();
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->boolean('is_banned')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->index('phone');
            $table->index('role');
        });

        // 2. driver_profiles
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('vehicle_type', ['bike','auto','car','suv','truck'])->default('bike');
            $table->string('vehicle_model', 80)->nullable();
            $table->string('vehicle_number', 20)->nullable();
            $table->string('vehicle_color', 30)->nullable();
            $table->enum('status', ['pending','approved','rejected','suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_online')->default(false);
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            $table->unsignedInteger('total_trips')->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->timestamps();
        });

        // 3. driver_documents
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('doc_type', ['citizenship','driving_license','vehicle_registration','vehicle_photo']);
            $table->string('doc_path');
            $table->enum('status', ['pending','verified','rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->unique(['user_id','doc_type']);
        });

        // 4. rides
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pickup_address');
            $table->decimal('pickup_lat', 10, 8)->nullable();
            $table->decimal('pickup_lng', 11, 8)->nullable();
            $table->string('destination_address');
            $table->decimal('destination_lat', 10, 8)->nullable();
            $table->decimal('destination_lng', 11, 8)->nullable();
            $table->enum('vehicle_category', ['bike','auto','car','suv','truck'])->default('bike');
            $table->decimal('offered_fare', 10, 2);
            $table->decimal('agreed_fare', 10, 2)->nullable();
            $table->enum('payment_method', ['cash','wallet'])->default('cash');
            $table->string('promo_code', 30)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->enum('status', ['pending','accepted','driver_arrived','in_progress','completed','cancelled'])->default('pending');
            $table->enum('cancelled_by', ['rider','driver','admin'])->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['rider_id','status']);
            $table->index(['driver_id','status']);
            $table->index('status');
        });

        // 5. ride_bids
        Schema::create('ride_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('bid_amount', 10, 2);
            $table->string('message', 300)->nullable();
            $table->enum('status', ['pending','accepted','rejected','withdrawn'])->default('pending');
            $table->timestamps();
            $table->unique(['ride_id','driver_id']);
        });

        // 6. chat_messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
            $table->index(['ride_id','id']);
        });

        // 7. ratings
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ratee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ride_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->enum('rater_type', ['rider','driver'])->default('rider');
            $table->timestamps();
            $table->unique(['rater_id','ride_id']);
        });

        // 8. sos_alerts
        Schema::create('sos_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ride_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->enum('status', ['active','resolved'])->default('active');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 9. wallet_transactions
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit','debit']);
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        // 10. trusted_contacts
        Schema::create('trusted_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('phone', 15);
            $table->string('relationship', 50)->nullable();
            $table->timestamps();
        });

        // 11. saved_locations
        Schema::create('saved_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 30)->default('home');
            $table->string('address');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();
        });

        // 12. app_notifications
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('body');
            $table->enum('type', ['system','sos','ride','support','broadcast'])->default('system');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id','is_read']);
        });

        // 13. promo_codes
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->enum('discount_type', ['percentage','fixed'])->default('percentage');
            $table->decimal('discount_value', 8, 2);
            $table->unsignedInteger('max_uses')->default(100);
            $table->unsignedInteger('uses_count')->default(0);
            $table->decimal('min_fare', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 14. support_tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 150);
            $table->text('message');
            $table->enum('priority', ['normal','high','urgent'])->default('normal');
            $table->enum('status', ['open','in_progress','resolved','closed'])->default('open');
            $table->text('admin_reply')->nullable();
            $table->timestamps();
        });

        // 15. complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complainant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('accused_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ride_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->enum('status', ['open','investigating','resolved'])->default('open');
            $table->timestamps();
        });

        // 16. delivery_orders
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pickup_address');
            $table->string('delivery_address');
            $table->enum('item_type', ['document','parcel','freight'])->default('parcel');
            $table->string('item_description', 200)->nullable();
            $table->decimal('weight_kg', 8, 2)->default(0);
            $table->string('recipient_name', 80);
            $table->string('recipient_phone', 15);
            $table->decimal('fare', 10, 2)->default(0);
            $table->enum('payment_method', ['cash','wallet'])->default('cash');
            $table->enum('status', ['pending','assigned','picked_up','delivered','cancelled'])->default('pending');
            $table->timestamps();
        });

        // 17. password_reset_tokens (Laravel default)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        $tables = [
            'delivery_orders','complaints','support_tickets','promo_codes',
            'app_notifications','saved_locations','trusted_contacts','wallet_transactions',
            'sos_alerts','ratings','chat_messages','ride_bids','rides',
            'driver_documents','driver_profiles','users','password_reset_tokens',
        ];
        foreach ($tables as $table) Schema::dropIfExists($table);
    }
};
