<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'property_registration_type')) {
                $table->string('property_registration_type')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_project_name')) {
                $table->string('property_project_name')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_block_phase')) {
                $table->string('property_block_phase')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_document_no')) {
                $table->string('property_document_no')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_document_year')) {
                $table->unsignedSmallInteger('property_document_year')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_registration_office_area')) {
                $table->string('property_registration_office_area')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $columns = [
                'property_registration_type',
                'property_project_name',
                'property_block_phase',
                'property_document_no',
                'property_document_year',
                'property_registration_office_area',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
