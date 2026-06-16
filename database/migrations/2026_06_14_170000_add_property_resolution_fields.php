<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'property_resolution_remarks')) {
                $table->text('property_resolution_remarks')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_resolved_at')) {
                $table->timestamp('property_resolved_at')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_resolved_by_user_id')) {
                $table->unsignedBigInteger('property_resolved_by_user_id')->nullable();
                $table->foreign('property_resolved_by_user_id', 'properties_resolved_by_user_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (Schema::hasColumn('properties', 'property_resolved_by_user_id')) {
                $table->dropForeign(['property_resolved_by_user_id']);
            }

            foreach (['property_resolution_remarks', 'property_resolved_at', 'property_resolved_by_user_id'] as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
