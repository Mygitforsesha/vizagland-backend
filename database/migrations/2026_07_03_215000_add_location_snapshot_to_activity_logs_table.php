<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        $hasLatitude = Schema::hasColumn('activity_logs', 'activity_log_latitude');
        $hasLongitude = Schema::hasColumn('activity_logs', 'activity_log_longitude');
        $hasRoad = Schema::hasColumn('activity_logs', 'activity_log_road');
        $hasColony = Schema::hasColumn('activity_logs', 'activity_log_colony');
        $hasSuburb = Schema::hasColumn('activity_logs', 'activity_log_suburb');
        $hasVillage = Schema::hasColumn('activity_logs', 'activity_log_village');
        $hasMandal = Schema::hasColumn('activity_logs', 'activity_log_mandal');
        $hasDistrict = Schema::hasColumn('activity_logs', 'activity_log_district');
        $hasState = Schema::hasColumn('activity_logs', 'activity_log_state');
        $hasPincode = Schema::hasColumn('activity_logs', 'activity_log_pincode');
        $hasCountry = Schema::hasColumn('activity_logs', 'activity_log_country');

        Schema::table('activity_logs', function (Blueprint $table) use (
            $hasLatitude,
            $hasLongitude,
            $hasRoad,
            $hasColony,
            $hasSuburb,
            $hasVillage,
            $hasMandal,
            $hasDistrict,
            $hasState,
            $hasPincode,
            $hasCountry,
        ): void {
            if (! $hasLatitude) {
                $table->decimal('activity_log_latitude', 10, 7)->nullable()->after('activity_log_user_agent');
            }

            if (! $hasLongitude) {
                $table->decimal('activity_log_longitude', 10, 7)->nullable()->after('activity_log_latitude');
            }

            if (! $hasRoad) {
                $table->string('activity_log_road')->nullable()->after('activity_log_longitude');
            }

            if (! $hasColony) {
                $table->string('activity_log_colony')->nullable()->after('activity_log_road');
            }

            if (! $hasSuburb) {
                $table->string('activity_log_suburb')->nullable()->after('activity_log_colony');
            }

            if (! $hasVillage) {
                $table->string('activity_log_village')->nullable()->after('activity_log_suburb');
            }

            if (! $hasMandal) {
                $table->string('activity_log_mandal')->nullable()->after('activity_log_village');
            }

            if (! $hasDistrict) {
                $table->string('activity_log_district')->nullable()->after('activity_log_mandal');
            }

            if (! $hasState) {
                $table->string('activity_log_state')->nullable()->after('activity_log_district');
            }

            if (! $hasPincode) {
                $table->string('activity_log_pincode', 20)->nullable()->after('activity_log_state');
            }

            if (! $hasCountry) {
                $table->string('activity_log_country')->nullable()->after('activity_log_pincode');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('activity_logs', 'activity_log_latitude') ? 'activity_log_latitude' : null,
            Schema::hasColumn('activity_logs', 'activity_log_longitude') ? 'activity_log_longitude' : null,
            Schema::hasColumn('activity_logs', 'activity_log_road') ? 'activity_log_road' : null,
            Schema::hasColumn('activity_logs', 'activity_log_colony') ? 'activity_log_colony' : null,
            Schema::hasColumn('activity_logs', 'activity_log_suburb') ? 'activity_log_suburb' : null,
            Schema::hasColumn('activity_logs', 'activity_log_village') ? 'activity_log_village' : null,
            Schema::hasColumn('activity_logs', 'activity_log_mandal') ? 'activity_log_mandal' : null,
            Schema::hasColumn('activity_logs', 'activity_log_district') ? 'activity_log_district' : null,
            Schema::hasColumn('activity_logs', 'activity_log_state') ? 'activity_log_state' : null,
            Schema::hasColumn('activity_logs', 'activity_log_pincode') ? 'activity_log_pincode' : null,
            Schema::hasColumn('activity_logs', 'activity_log_country') ? 'activity_log_country' : null,
        ]));

        if ($columns !== []) {
            Schema::table('activity_logs', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
