<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('lives')->default(7)->after('role');
            $table->timestamp('lives_reset_at')->nullable()->after('lives');
            $table->timestamp('premium_until')->nullable()->after('lives_reset_at');
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('premium_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['stripe_checkout_session_id']);
            $table->dropColumn(['lives', 'lives_reset_at', 'premium_until', 'stripe_checkout_session_id']);
        });
    }
};