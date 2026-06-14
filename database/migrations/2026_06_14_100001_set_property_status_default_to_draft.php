<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        DB::table('properties')
            ->where('property_status', 'lead')
            ->update(['property_status' => 'draft']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE properties MODIFY property_status VARCHAR(255) NOT NULL DEFAULT 'draft'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE properties ALTER COLUMN property_status SET DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE properties MODIFY property_status VARCHAR(255) NOT NULL DEFAULT 'lead'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE properties ALTER COLUMN property_status SET DEFAULT 'lead'");
        }
    }
};
