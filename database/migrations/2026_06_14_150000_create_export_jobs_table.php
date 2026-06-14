<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id('export_job_id');
            $table->foreignId('export_job_user_id')
                ->constrained('users', 'user_id')
                ->restrictOnDelete();
            $table->string('export_job_type');
            $table->string('export_job_format');
            $table->string('export_job_status')->default('pending');
            $table->string('export_job_file_name')->nullable();
            $table->string('export_job_file_path')->nullable();
            $table->unsignedBigInteger('export_job_file_size')->nullable();
            $table->json('export_job_filters')->nullable();
            $table->text('export_job_error_message')->nullable();
            $table->timestamp('export_job_created_at')->useCurrent();
            $table->timestamp('export_job_completed_at')->nullable();

            $table->index('export_job_user_id');
            $table->index('export_job_type');
            $table->index('export_job_status');
            $table->index('export_job_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_jobs');
    }
};
