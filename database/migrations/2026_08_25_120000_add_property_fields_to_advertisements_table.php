<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table): void {
                if (! Schema::hasColumn('advertisements', 'advertisement_types')) {
                    $table->json('advertisement_types')->nullable()->after('advertisement_category');
                }
                if (! Schema::hasColumn('advertisements', 'property_category')) {
                    $table->string('property_category')->nullable()->after('advertisement_redirect_url');
                }
                if (! Schema::hasColumn('advertisements', 'property_location')) {
                    $table->json('property_location')->nullable()->after('property_category');
                }
                if (! Schema::hasColumn('advertisements', 'property_details')) {
                    $table->json('property_details')->nullable()->after('property_location');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table): void {
                $columnsToDrop = [];
                if (Schema::hasColumn('advertisements', 'advertisement_types')) {
                    $columnsToDrop[] = 'advertisement_types';
                }
                if (Schema::hasColumn('advertisements', 'property_category')) {
                    $columnsToDrop[] = 'property_category';
                }
                if (Schema::hasColumn('advertisements', 'property_location')) {
                    $columnsToDrop[] = 'property_location';
                }
                if (Schema::hasColumn('advertisements', 'property_details')) {
                    $columnsToDrop[] = 'property_details';
                }
                if (! empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
