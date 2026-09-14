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
        Schema::create('idp_activities', function (Blueprint $table) {
            // Not auto-incrementing: the id is the activity's stable index
            // into the seed data (0-29), which is also how imported
            // attachment folders are matched — a "Reset" updates rows in
            // place by this id instead of recreating them, so it never
            // orphans a source or attachment.
            $table->unsignedTinyInteger('id')->primary();
            $table->string('area');
            $table->text('objective');
            $table->text('description');
            $table->string('type', 2);
            $table->string('status');
            $table->date('target_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idp_activities');
    }
};
