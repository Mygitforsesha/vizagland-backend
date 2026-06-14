<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        $this->dropPropertyReferenceIdUniqueIndex();
        $this->renameMetadataColumns();
        $this->addRecordTypeAndMetadataColumns();
    }

    public function down(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table): void {
            if (Schema::hasColumn('properties', 'property_parent_property_id')) {
                $table->dropForeign(['property_parent_property_id']);
                $table->dropColumn('property_parent_property_id');
            }

            $columns = [
                'property_record_type',
                'property_other_service_name',
                'property_is_featured',
                'property_is_deleted',
                'property_review_remarks',
                'property_rejected_reason',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        $this->revertMetadataColumnRenames();

        try {
            Schema::table('properties', function (Blueprint $table): void {
                $table->unique('property_reference_id');
            });
        } catch (\Throwable) {
            // Index may not be restorable if duplicates exist.
        }
    }

    private function dropPropertyReferenceIdUniqueIndex(): void
    {
        if (! Schema::hasColumn('properties', 'property_reference_id')) {
            return;
        }

        try {
            Schema::table('properties', function (Blueprint $table): void {
                $table->dropUnique(['property_reference_id']);
            });
        } catch (\Throwable) {
            // Unique index may already be absent.
        }
    }

    private function renameMetadataColumns(): void
    {
        if (Schema::hasColumn('properties', 'property_views') && ! Schema::hasColumn('properties', 'property_view_count')) {
            DB::statement('ALTER TABLE properties CHANGE property_views property_view_count INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('properties', 'property_leads') && ! Schema::hasColumn('properties', 'property_lead_count')) {
            DB::statement('ALTER TABLE properties CHANGE property_leads property_lead_count INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('properties', 'property_assigned_to') && ! Schema::hasColumn('properties', 'property_assigned_user_id')) {
            Schema::table('properties', function (Blueprint $table): void {
                $table->dropForeign(['property_assigned_to']);
                $table->dropIndex(['property_assigned_to']);
            });

            DB::statement('ALTER TABLE properties CHANGE property_assigned_to property_assigned_user_id BIGINT UNSIGNED NULL');

            Schema::table('properties', function (Blueprint $table): void {
                $table->foreign('property_assigned_user_id')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
                $table->index('property_assigned_user_id');
            });
        }
    }

    private function addRecordTypeAndMetadataColumns(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'property_record_type')) {
                $table->string('property_record_type')->default('vizagland_copy')->after('property_reference_id');
                $table->index('property_record_type');
            }

            if (! Schema::hasColumn('properties', 'property_parent_property_id')) {
                $table->unsignedBigInteger('property_parent_property_id')->nullable()->after('property_record_type');
                $table->foreign('property_parent_property_id')
                    ->references('property_id')
                    ->on('properties')
                    ->nullOnDelete();
                $table->index('property_parent_property_id');
            }

            if (! Schema::hasColumn('properties', 'property_other_service_name')) {
                $table->string('property_other_service_name')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_is_featured')) {
                $table->boolean('property_is_featured')->default(false);
            }

            if (! Schema::hasColumn('properties', 'property_view_count') && ! Schema::hasColumn('properties', 'property_views')) {
                $table->unsignedInteger('property_view_count')->default(0);
            }

            if (! Schema::hasColumn('properties', 'property_lead_count') && ! Schema::hasColumn('properties', 'property_leads')) {
                $table->unsignedInteger('property_lead_count')->default(0);
            }

            if (! Schema::hasColumn('properties', 'property_is_deleted')) {
                $table->boolean('property_is_deleted')->default(false);
            }

            if (! Schema::hasColumn('properties', 'property_assigned_user_id') && ! Schema::hasColumn('properties', 'property_assigned_to')) {
                $table->foreignId('property_assigned_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->index('property_assigned_user_id');
            }

            if (! Schema::hasColumn('properties', 'property_review_remarks')) {
                $table->text('property_review_remarks')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_rejected_reason')) {
                $table->text('property_rejected_reason')->nullable();
            }
        });
    }

    private function revertMetadataColumnRenames(): void
    {
        if (Schema::hasColumn('properties', 'property_view_count')) {
            DB::statement('ALTER TABLE properties CHANGE property_view_count property_views INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('properties', 'property_lead_count')) {
            DB::statement('ALTER TABLE properties CHANGE property_lead_count property_leads INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('properties', 'property_assigned_user_id')) {
            Schema::table('properties', function (Blueprint $table): void {
                $table->dropForeign(['property_assigned_user_id']);
                $table->dropIndex(['property_assigned_user_id']);
            });

            DB::statement('ALTER TABLE properties CHANGE property_assigned_user_id property_assigned_to BIGINT UNSIGNED NULL');

            Schema::table('properties', function (Blueprint $table): void {
                $table->foreign('property_assigned_to')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
                $table->index('property_assigned_to');
            });
        }
    }
};
