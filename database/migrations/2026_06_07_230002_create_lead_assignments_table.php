<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_assignments', function (Blueprint $table) {
            $table->id('lead_assignment_id');
            $table->foreignId('lead_id')->constrained('leads', 'lead_id')->cascadeOnDelete();
            $table->foreignId('lead_assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('lead_assigned_by')->constrained('users')->restrictOnDelete();
            $table->text('lead_assignment_remarks')->nullable();
            $table->timestamps();

            $table->index('lead_id');
            $table->index('lead_assigned_to');
            $table->index('lead_assigned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_assignments');
    }
};
