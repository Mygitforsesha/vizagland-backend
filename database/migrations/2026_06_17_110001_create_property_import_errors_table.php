<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_import_errors', function (Blueprint $table) {
            $table->id('property_import_error_id');
            $table->foreignId('property_import_job_id')
                ->constrained('property_import_jobs', 'property_import_job_id')
                ->cascadeOnDelete();
            $table->unsignedInteger('property_import_row_number');
            $table->text('property_import_error_message');
            $table->timestamp('property_import_error_created_at')->useCurrent();

            $table->index('property_import_job_id');
            $table->index('property_import_row_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_import_errors');
    }
};
