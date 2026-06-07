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
        Schema::create('property_reviews', function (Blueprint $table) {
            $table->id('property_review_id');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->string('review_status');
            $table->text('review_comments')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index('reviewed_by');
            $table->index('review_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_reviews');
    }
};
