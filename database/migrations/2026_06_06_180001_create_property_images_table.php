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
        Schema::create('property_images', function (Blueprint $table) {
            $table->id('property_image_id');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->string('property_image_path');
            $table->string('property_image_name');
            $table->unsignedBigInteger('property_image_size');
            $table->unsignedSmallInteger('property_image_sort_order')->default(0);
            $table->timestamps();

            $table->index(['property_id', 'property_image_sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_images');
    }
};
