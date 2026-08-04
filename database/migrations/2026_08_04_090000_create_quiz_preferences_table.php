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
        Schema::create('quiz_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('default_mode')->default('shuffle');
            $table->unsignedInteger('daily_quota')->default(8);
            $table->boolean('auto_reveal')->default(true);
            $table->boolean('shuffle_order')->default(true);
            $table->boolean('show_difficulty')->default(true);
            $table->boolean('timed_mode')->default(false);
            $table->boolean('streak_reminder')->default(true);
            $table->boolean('weekly_summary')->default(true);
            $table->boolean('new_questions_notif')->default(false);
            // Streak is derived from QuizSession history, not stored directly —
            // "Reset Streak" works by moving this cutoff to today so no session
            // on or before it counts toward the running streak anymore.
            $table->timestamp('streak_broken_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_preferences');
    }
};
