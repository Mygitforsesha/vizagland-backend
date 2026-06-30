<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_locations', function (Blueprint $table) {
            $table->id('master_location_id');
            $table->string('master_location_village');
            $table->string('master_location_nearby_location')->nullable();
            $table->string('master_location_additional_nearby_location')->nullable();
            $table->string('master_location_district')->nullable();
            $table->string('master_location_mandal')->nullable();
            $table->string('master_location_panchayati')->nullable();
            $table->string('master_location_gvmc_zone')->nullable();
            $table->string('master_location_gvmc_ward')->nullable();
            $table->string('master_location_vmrda')->nullable();
            $table->string('master_location_registration_office')->nullable();
            $table->string('master_location_authority')->nullable();
            $table->timestamp('master_location_created_at')->useCurrent();
            $table->timestamp('master_location_updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('master_location_village');
            $table->index('master_location_nearby_location');
            $table->index('master_location_additional_nearby_location');
            $table->index('master_location_district');
            $table->index('master_location_mandal');
            $table->index('master_location_panchayati');
            $table->index('master_location_gvmc_zone');
            $table->index('master_location_gvmc_ward');
            $table->index('master_location_vmrda');
            $table->index('master_location_registration_office');
            $table->index('master_location_authority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_locations');
    }
};
