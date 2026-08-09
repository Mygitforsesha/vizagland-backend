<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_search_histories')) {
            return;
        }

        Schema::create('property_search_histories', function (Blueprint $table): void {
            $table->id('property_search_history_id');
            $table->foreignId('property_search_history_user_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->string('property_search_history_keyword')->nullable();
            $table->json('property_search_history_filters')->nullable();
            $table->unsignedInteger('property_search_history_results_count')->default(0);
            $table->string('property_search_history_ip_address', 45)->nullable();
            $table->timestamp('property_search_history_created_at')->useCurrent();

            $table->index('property_search_history_created_at');
            $table->index('property_search_history_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_search_histories');
    }
};
