<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_registration_types', function (Blueprint $table) {
            $table->id('user_registration_type_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_registration_type_category');
            $table->string('user_registration_type_value');
            $table->timestamps();

            $table->unique(
                ['user_id', 'user_registration_type_category', 'user_registration_type_value'],
                'user_registration_types_unique',
            );
            $table->index('user_registration_type_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_registration_types');
    }
};
