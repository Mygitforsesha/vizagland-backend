<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_services', function (Blueprint $table) {
            $table->id('other_service_id');
            $table->string('other_service_name');
            $table->string('other_service_slug');
            $table->text('other_service_description')->nullable();
            $table->string('other_service_icon')->nullable();
            $table->unsignedInteger('other_service_sort_order')->default(0);
            $table->boolean('other_service_is_active')->default(true);
            $table->timestamp('other_service_created_at')->useCurrent();
            $table->timestamp('other_service_updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('other_service_name');
            $table->unique('other_service_slug');
            $table->index('other_service_is_active');
            $table->index('other_service_sort_order');
            $table->index(
                ['other_service_is_active', 'other_service_sort_order'],
                'other_services_active_sort_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_services');
    }
};
