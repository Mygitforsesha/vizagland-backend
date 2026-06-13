<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_profiles')) {
            return;
        }

        if (Schema::hasColumn('user_profiles', 'user_landmark')) {
            DB::statement('ALTER TABLE user_profiles CHANGE user_landmark user_nearby_location VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('user_profiles', 'user_custom_landmark')) {
            DB::statement('ALTER TABLE user_profiles CHANGE user_custom_landmark user_custom_nearby_location VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('user_profiles', 'user_gvmc_zone_ward')) {
            DB::statement('ALTER TABLE user_profiles CHANGE user_gvmc_zone_ward user_gvmc_zone_ward_number VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('user_profiles', 'user_terms_accepted_at')) {
            Schema::table('user_profiles', function ($table) {
                $table->dropColumn('user_terms_accepted_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_profiles')) {
            return;
        }

        if (Schema::hasColumn('user_profiles', 'user_nearby_location')) {
            DB::statement('ALTER TABLE user_profiles CHANGE user_nearby_location user_landmark VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('user_profiles', 'user_custom_nearby_location')) {
            DB::statement('ALTER TABLE user_profiles CHANGE user_custom_nearby_location user_custom_landmark VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('user_profiles', 'user_gvmc_zone_ward_number')) {
            DB::statement('ALTER TABLE user_profiles CHANGE user_gvmc_zone_ward_number user_gvmc_zone_ward VARCHAR(255) NULL');
        }

        if (! Schema::hasColumn('user_profiles', 'user_terms_accepted_at')) {
            Schema::table('user_profiles', function ($table) {
                $table->timestamp('user_terms_accepted_at')->nullable();
            });
        }
    }
};
