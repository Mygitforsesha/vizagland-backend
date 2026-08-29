<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('property_search_histories')
            && Schema::hasColumn('property_search_histories', 'id')
            && ! Schema::hasColumn('property_search_histories', 'property_search_history_id')
        ) {
            Schema::table('property_search_histories', function (Blueprint $table): void {
                $table->renameColumn('id', 'property_search_history_id');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('property_search_histories')
            && Schema::hasColumn('property_search_histories', 'property_search_history_id')
            && ! Schema::hasColumn('property_search_histories', 'id')
        ) {
            Schema::table('property_search_histories', function (Blueprint $table): void {
                $table->renameColumn('property_search_history_id', 'id');
            });
        }
    }
};
