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
        // Bare schema only — pulled ahead of Phase 3 (Quiz Session module) because
        // attempts.quiz_session_id needs this table to exist first (FK dependency
        // order per the Development Plan §3). Livewire wiring/quota logic is Phase 3.
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedInteger('quota');
            $table->unsignedInteger('completed_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_sessions');
    }
};
