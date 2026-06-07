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
        Schema::create('property_documents', function (Blueprint $table) {
            $table->id('property_document_id');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->string('property_document_name');
            $table->string('property_document_type');
            $table->string('property_document_path');
            $table->unsignedBigInteger('property_document_size');
            $table->timestamps();

            $table->index('property_id');
            $table->index('property_document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_documents');
    }
};
