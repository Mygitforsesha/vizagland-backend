<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->decimal('user_latitude', 10, 7)->nullable()->after('user_gender');
            $table->decimal('user_longitude', 10, 7)->nullable()->after('user_latitude');
            $table->string('user_road')->nullable()->after('user_longitude');
            $table->string('user_colony')->nullable()->after('user_road');
            $table->string('user_suburb')->nullable()->after('user_colony');
            $table->string('user_gvmc_vmrda')->nullable()->after('user_registration_area');
            $table->string('user_state')->nullable()->after('user_gvmc_vmrda');
            $table->string('user_pincode', 20)->nullable()->after('user_state');
            $table->string('user_country')->nullable()->after('user_pincode');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'user_latitude',
                'user_longitude',
                'user_road',
                'user_colony',
                'user_suburb',
                'user_gvmc_vmrda',
                'user_state',
                'user_pincode',
                'user_country',
            ]);
        });
    }
};
