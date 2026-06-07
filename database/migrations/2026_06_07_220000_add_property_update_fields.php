<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('property_area', 12, 2)->nullable()->after('property_area_sqft');
            $table->string('property_area_unit')->nullable()->after('property_area');
            $table->string('property_lp_number')->nullable()->after('property_pincode');
            $table->unsignedSmallInteger('property_year')->nullable()->after('property_lp_number');
            $table->string('property_plot_number')->nullable()->after('property_year');
            $table->string('property_ownership_type')->nullable()->after('property_plot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'property_area',
                'property_area_unit',
                'property_lp_number',
                'property_year',
                'property_plot_number',
                'property_ownership_type',
            ]);
        });
    }
};
