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
        Schema::create('property_duplicate_matches', function (Blueprint $table) {
            $table->id('property_duplicate_match_id');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->foreignId('matched_property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->decimal('match_percentage', 5, 2);
            $table->string('match_status');
            $table->timestamps();

            $table->unique(['property_id', 'matched_property_id'], 'property_dup_match_pair_unique');
            $table->index('match_status');
            $table->index('match_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_duplicate_matches');
    }
};
