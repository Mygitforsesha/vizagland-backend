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
            $table->dropForeign(['property_created_by']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('property_created_by')->nullable()->change();
            $table->string('property_created_by_type')->nullable()->after('property_status');
            $table->unsignedBigInteger('property_created_by_id')->nullable()->after('property_created_by_type');

            $table->foreign('property_created_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->index('property_created_by_type');
            $table->index('property_created_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['property_created_by_type']);
            $table->dropIndex(['property_created_by_id']);
            $table->dropColumn(['property_created_by_type', 'property_created_by_id']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['property_created_by']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('property_created_by')->nullable(false)->change();

            $table->foreign('property_created_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
