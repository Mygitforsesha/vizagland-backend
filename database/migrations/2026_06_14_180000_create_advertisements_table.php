<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id('advertisement_id');
            $table->string('advertisement_title');
            $table->text('advertisement_description')->nullable();
            $table->string('advertisement_type');
            $table->string('advertisement_image_path');
            $table->string('advertisement_redirect_url')->nullable();
            $table->unsignedInteger('advertisement_display_order')->default(0);
            $table->date('advertisement_start_date')->nullable();
            $table->date('advertisement_end_date')->nullable();
            $table->boolean('advertisement_is_active')->default(true);
            $table->unsignedBigInteger('advertisement_created_by_user_id')->nullable();
            $table->timestamp('advertisement_created_at')->useCurrent();
            $table->timestamp('advertisement_updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('advertisement_type');
            $table->index('advertisement_is_active');
            $table->index('advertisement_display_order');
            $table->index('advertisement_start_date');
            $table->index('advertisement_end_date');

            $table->foreign('advertisement_created_by_user_id', 'advertisements_created_by_user_fk')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
