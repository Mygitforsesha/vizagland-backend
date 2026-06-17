<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_import_jobs', function (Blueprint $table) {
            $table->id('property_import_job_id');
            $table->string('property_import_file_name');
            $table->string('property_import_file_path');
            $table->unsignedInteger('property_import_total_rows')->default(0);
            $table->unsignedInteger('property_import_success_rows')->default(0);
            $table->unsignedInteger('property_import_failed_rows')->default(0);
            $table->string('property_import_status')->default('pending');
            $table->foreignId('property_import_created_by_user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();
            $table->timestamp('property_import_started_at')->nullable();
            $table->timestamp('property_import_completed_at')->nullable();
            $table->timestamps();

            $table->index('property_import_status');
            $table->index('property_import_created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_import_jobs');
    }
};
