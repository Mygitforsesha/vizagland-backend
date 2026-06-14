<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('activity_log_id');
            $table->unsignedBigInteger('activity_log_user_id')->nullable();
            $table->string('activity_log_user_name')->nullable();
            $table->string('activity_log_user_role')->nullable();
            $table->string('activity_log_type');
            $table->string('activity_log_action');
            $table->text('activity_log_description');
            $table->string('activity_log_entity_type')->nullable();
            $table->unsignedBigInteger('activity_log_entity_id')->nullable();
            $table->string('activity_log_ip_address', 45)->nullable();
            $table->text('activity_log_user_agent')->nullable();
            $table->json('activity_log_metadata')->nullable();
            $table->timestamp('activity_log_created_at')->useCurrent();

            $table->index('activity_log_type');
            $table->index('activity_log_user_id');
            $table->index('activity_log_created_at');
            $table->index('activity_log_entity_type');
            $table->index('activity_log_entity_id');

            $table->foreign('activity_log_user_id', 'activity_logs_user_fk')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
