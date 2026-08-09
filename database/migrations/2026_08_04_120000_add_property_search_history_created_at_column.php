<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production currently has property_search_histories without
 * property_search_history_created_at. Add that column only.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_search_histories')) {
            return;
        }

        if (Schema::hasColumn('property_search_histories', 'property_search_history_created_at')) {
            return;
        }

        Schema::table('property_search_histories', function (Blueprint $table): void {
            $table->timestamp('property_search_history_created_at')->nullable();
        });

        if (Schema::hasColumn('property_search_histories', 'created_at')) {
            DB::table('property_search_histories')
                ->whereNull('property_search_history_created_at')
                ->update(['property_search_history_created_at' => DB::raw('`created_at`')]);
        }

        DB::table('property_search_histories')
            ->whereNull('property_search_history_created_at')
            ->update(['property_search_history_created_at' => now()]);

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE `property_search_histories`
                 MODIFY `property_search_history_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('property_search_histories')) {
            return;
        }

        if (! Schema::hasColumn('property_search_histories', 'property_search_history_created_at')) {
            return;
        }

        Schema::table('property_search_histories', function (Blueprint $table): void {
            $table->dropColumn('property_search_history_created_at');
        });
    }
};
