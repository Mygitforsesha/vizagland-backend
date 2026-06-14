<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('notification_user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();
            $table->string('notification_type');
            $table->string('notification_title');
            $table->text('notification_message');
            $table->boolean('notification_is_read')->default(false);
            $table->timestamp('notification_read_at')->nullable();
            $table->timestamp('notification_deleted_at')->nullable();
            $table->timestamp('notification_created_at')->useCurrent();

            $table->index('notification_user_id');
            $table->index('notification_type');
            $table->index('notification_is_read');
            $table->index('notification_created_at');
            $table->index('notification_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
