<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('properties') || ! Schema::hasColumn('properties', 'property_reference_id')) {
            return;
        }

        $this->dropReferenceIdUniqueIndexes();
    }

    public function down(): void
    {
        if (! Schema::hasTable('properties') || ! Schema::hasColumn('properties', 'property_reference_id')) {
            return;
        }

        try {
            Schema::table('properties', function (Blueprint $table): void {
                $table->unique('property_reference_id');
            });
        } catch (\Throwable) {
            // Cannot restore unique index if duplicate reference IDs exist.
        }
    }

    private function dropReferenceIdUniqueIndexes(): void
    {
        $indexNames = [
            'properties_property_code_unique',
            'properties_property_reference_id_unique',
            'property_code_unique',
            'property_reference_id_unique',
        ];

        foreach ($indexNames as $indexName) {
            try {
                Schema::table('properties', function (Blueprint $table) use ($indexName): void {
                    $table->dropUnique($indexName);
                });
            } catch (\Throwable) {
                // Index may not exist under this name.
            }
        }

        try {
            Schema::table('properties', function (Blueprint $table): void {
                $table->dropUnique(['property_reference_id']);
            });
        } catch (\Throwable) {
            // Index may already be removed.
        }
    }
};
