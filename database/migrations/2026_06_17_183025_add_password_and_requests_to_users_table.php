<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add password column to users (nullable so existing phone-only users still work)
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
        });

        // Password reset / forgot-password requests that go to admin panel
        Schema::create('password_requests', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15);
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_requests');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};