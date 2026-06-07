<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('properties', 'property_assigned_to')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->foreignId('property_assigned_to')
                    ->nullable()
                    ->after('property_reviewed_by')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->index('property_assigned_to');
            });
        }

        $this->makePartialLeadColumnsNullable();
        $this->updatePropertyStatusDefault('lead');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['property_assigned_to']);
            $table->dropIndex(['property_assigned_to']);
            $table->dropColumn('property_assigned_to');
        });

        $this->updatePropertyStatusDefault('draft');
    }

    private function makePartialLeadColumnsNullable(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_code')->nullable()->change();
            $table->string('property_title')->nullable()->change();
            $table->string('property_type')->nullable()->change();
            $table->string('property_listing_type')->nullable()->change();
            $table->decimal('property_price', 15, 2)->nullable()->change();
            $table->text('property_address')->nullable()->change();
            $table->string('property_city')->nullable()->change();
            $table->string('property_state')->nullable()->change();
            $table->string('property_pincode', 10)->nullable()->change();
            $table->string('property_owner_name')->nullable()->change();
            $table->string('property_owner_phone', 20)->nullable()->change();
        });
    }

    private function updatePropertyStatusDefault(string $default): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE properties MODIFY property_status VARCHAR(255) NOT NULL DEFAULT '{$default}'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE properties ALTER COLUMN property_status SET DEFAULT '{$default}'");

            return;
        }

        if ($driver === 'sqlite') {
            // SQLite cannot alter column defaults in place; fresh installs use the updated create migration.
        }
    }
};
