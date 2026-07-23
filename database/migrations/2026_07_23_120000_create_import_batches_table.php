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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            // Nullable + nullOnDelete: a batch still records what was submitted
            // even if its category gets removed later.
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('raw_content');
            $table->string('status')->default('uploaded');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // Deferred from the questions migration (Phase 2) since import_batches
        // didn't exist yet.
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['import_batch_id']);
        });

        Schema::dropIfExists('import_batches');
    }
};
