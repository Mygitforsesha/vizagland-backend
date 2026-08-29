<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_search_histories') && ! Schema::hasColumn('property_search_histories', 'property_search_history_mobile_number')) {
            Schema::table('property_search_histories', function (Blueprint $table): void {
                $table->string('property_search_history_mobile_number', 20)->nullable()->after('property_search_history_ip_address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_search_histories') && Schema::hasColumn('property_search_histories', 'property_search_history_mobile_number')) {
            Schema::table('property_search_histories', function (Blueprint $table): void {
                $table->dropColumn('property_search_history_mobile_number');
            });
        }
    }
};
