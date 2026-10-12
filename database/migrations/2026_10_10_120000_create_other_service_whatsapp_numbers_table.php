<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_service_whatsapp_numbers', function (Blueprint $table) {
            $table->id('other_service_whatsapp_number_id');
            $table->unsignedBigInteger('other_service_id');
            $table->string('other_service_whatsapp_number', 20);
            $table->boolean('other_service_whatsapp_number_is_primary')->default(false);
            $table->unsignedInteger('other_service_whatsapp_number_sort_order')->default(0);
            $table->timestamp('other_service_whatsapp_number_created_at')->useCurrent();
            $table->timestamp('other_service_whatsapp_number_updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('other_service_id', 'os_whatsapp_service_fk')
                ->references('other_service_id')
                ->on('other_services')
                ->cascadeOnDelete();

            $table->unique(
                ['other_service_id', 'other_service_whatsapp_number'],
                'os_whatsapp_service_number_uniq',
            );
            $table->index('other_service_id', 'os_whatsapp_service_idx');
            $table->index('other_service_whatsapp_number_is_primary', 'os_whatsapp_primary_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_service_whatsapp_numbers');
    }
};
