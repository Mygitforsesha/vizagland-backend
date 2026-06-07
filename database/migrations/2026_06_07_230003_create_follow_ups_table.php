<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id('follow_up_id');
            $table->string('follow_up_type');
            $table->text('follow_up_notes')->nullable();
            $table->timestamp('follow_up_scheduled_at');
            $table->timestamp('follow_up_completed_at')->nullable();
            $table->string('follow_up_status');
            $table->foreignId('follow_up_property_id')->nullable()->constrained('properties', 'property_id')->nullOnDelete();
            $table->foreignId('follow_up_lead_id')->nullable()->constrained('leads', 'lead_id')->nullOnDelete();
            $table->foreignId('follow_up_created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('follow_up_assigned_to')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('follow_up_type');
            $table->index('follow_up_status');
            $table->index('follow_up_scheduled_at');
            $table->index('follow_up_property_id');
            $table->index('follow_up_lead_id');
            $table->index('follow_up_assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
