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

        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'property_contact_name')) {
                $table->string('property_contact_name')->nullable()->after('property_longitude');
            }

            if (! Schema::hasColumn('properties', 'property_contact_phone')) {
                $table->string('property_contact_phone', 20)->nullable()->after('property_contact_name');
            }

            if (! Schema::hasColumn('properties', 'property_contact_type')) {
                $table->string('property_contact_type')->nullable()->after('property_contact_phone');
            }

            if (! Schema::hasColumn('properties', 'property_source')) {
                $table->string('property_source')->nullable()->after('property_contact_type');
            }
        });

        $this->migrateOwnerContactData();

        Schema::table('properties', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                Schema::hasColumn('properties', 'property_owner_name') ? 'property_owner_name' : null,
                Schema::hasColumn('properties', 'property_owner_phone') ? 'property_owner_phone' : null,
            ]);

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });

        Schema::table('properties', function (Blueprint $table) {
            if (
                Schema::hasColumn('properties', 'property_contact_type')
                && ! Schema::hasIndex('properties', 'properties_property_contact_type_index')
            ) {
                $table->index('property_contact_type');
            }

            if (
                Schema::hasColumn('properties', 'property_source')
                && ! Schema::hasIndex('properties', 'properties_property_source_index')
            ) {
                $table->index('property_source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'property_owner_name')) {
                $table->string('property_owner_name')->nullable()->after('property_longitude');
            }

            if (! Schema::hasColumn('properties', 'property_owner_phone')) {
                $table->string('property_owner_phone', 20)->nullable()->after('property_owner_name');
            }
        });

        if (Schema::hasColumn('properties', 'property_contact_name')) {
            DB::table('properties')
                ->where(function ($query) {
                    $query->whereNotNull('property_contact_name')
                        ->orWhereNotNull('property_contact_phone');
                })
                ->update([
                    'property_owner_name' => DB::raw('property_contact_name'),
                    'property_owner_phone' => DB::raw('property_contact_phone'),
                ]);
        }

        Schema::table('properties', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                Schema::hasColumn('properties', 'property_contact_name') ? 'property_contact_name' : null,
                Schema::hasColumn('properties', 'property_contact_phone') ? 'property_contact_phone' : null,
                Schema::hasColumn('properties', 'property_contact_type') ? 'property_contact_type' : null,
                Schema::hasColumn('properties', 'property_source') ? 'property_source' : null,
            ]);

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    private function migrateOwnerContactData(): void
    {
        if (! Schema::hasColumn('properties', 'property_owner_name')) {
            return;
        }

        DB::table('properties')
            ->where(function ($query) {
                $query->whereNotNull('property_owner_name')
                    ->orWhereNotNull('property_owner_phone');
            })
            ->update([
                'property_contact_name' => DB::raw('property_owner_name'),
                'property_contact_phone' => DB::raw('property_owner_phone'),
                'property_contact_type' => 'owner',
            ]);
    }
};
