<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_contact_numbers', function (Blueprint $table): void {
            $table->id('property_contact_number_id');
            $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
            $table->string('property_contact_number_registration_type')->nullable();
            $table->string('property_contact_number_phone_number', 20)->nullable();
            $table->timestamps();

            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_contact_numbers');
    }
};
