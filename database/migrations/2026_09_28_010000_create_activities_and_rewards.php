<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('title', 120);
            $table->text('prompt');
            $table->string('hint')->nullable();
            $table->string('answer', 255);
            $table->json('content')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['subject_id', 'active', 'position']);
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('category', 40);
            $table->string('asset', 100);
            $table->string('description', 180);
            $table->unsignedInteger('unlock_xp')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('reward_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->unique(['user_id', 'reward_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_xp')->default(0)->after('lives');
            $table->foreignId('equipped_reward_id')->nullable()->after('total_xp')->constrained('rewards')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_xp', 'equipped_reward_id']);
        });
        Schema::dropIfExists('reward_user');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('activities');
    }
};