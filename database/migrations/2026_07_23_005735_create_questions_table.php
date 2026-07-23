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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            // No ->constrained() yet — import_batches doesn't exist until Phase 4,
            // which adds the FK constraint via a follow-up migration.
            $table->unsignedBigInteger('import_batch_id')->nullable();
            $table->string('difficulty');
            $table->string('type');
            $table->text('prompt');
            $table->json('options_json')->nullable();
            $table->text('answer');
            $table->text('explanation')->nullable();
            $table->string('mastery_state')->default('new');
            $table->unsignedInteger('mastery_streak')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
