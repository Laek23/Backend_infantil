<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_progress', function (Blueprint $table) {
            $table->id();
            $table->string('player_id');
            $table->string('subject');
            $table->unsignedTinyInteger('completed')->default(0);
            $table->timestamps();
            $table->unique(['player_id', 'subject']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_progress');
    }
};