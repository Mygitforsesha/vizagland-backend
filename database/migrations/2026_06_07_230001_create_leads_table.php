<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id('lead_id');
            $table->string('lead_name');
            $table->string('lead_email')->nullable();
            $table->string('lead_phone', 20);
            $table->text('lead_message')->nullable();
            $table->string('lead_source');
            $table->string('lead_status');
            $table->foreignId('lead_property_id')->nullable()->constrained('properties', 'property_id')->nullOnDelete();
            $table->foreignId('lead_created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('lead_source');
            $table->index('lead_status');
            $table->index('lead_property_id');
            $table->index('lead_created_by');
            $table->index('lead_assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
