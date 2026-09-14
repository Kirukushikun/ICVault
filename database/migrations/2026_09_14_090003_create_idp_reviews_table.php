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
        Schema::create('idp_reviews', function (Blueprint $table) {
            $table->id();
            $table->date('review_date');
            $table->text('progress')->nullable();
            $table->text('challenge')->nullable();
            $table->text('adjustment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idp_reviews');
    }
};
