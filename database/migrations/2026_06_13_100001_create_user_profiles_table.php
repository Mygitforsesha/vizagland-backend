<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id('user_profile_id');
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->date('user_dob')->nullable();
            $table->string('user_gender')->nullable();
            $table->string('user_village')->nullable();
            $table->string('user_nearby_location')->nullable();
            $table->string('user_custom_nearby_location')->nullable();
            $table->string('user_district')->nullable();
            $table->string('user_mandal')->nullable();
            $table->string('user_panchayati')->nullable();
            $table->string('user_gvmc_zone_ward_number')->nullable();
            $table->string('user_vmrda')->nullable();
            $table->string('user_registration_area')->nullable();
            $table->string('user_authority')->nullable();
            $table->timestamps();

            $table->index('user_district');
            $table->index('user_village');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
