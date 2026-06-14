<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'property_rejected_at')) {
                $table->timestamp('property_rejected_at')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_rejected_by_user_id')) {
                $table->unsignedBigInteger('property_rejected_by_user_id')->nullable();
                $table->foreign('property_rejected_by_user_id', 'properties_rejected_by_user_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('properties', 'property_archived_reason')) {
                $table->text('property_archived_reason')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_archived_at')) {
                $table->timestamp('property_archived_at')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_archived_by_user_id')) {
                $table->unsignedBigInteger('property_archived_by_user_id')->nullable();
                $table->foreign('property_archived_by_user_id', 'properties_archived_by_user_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('properties', 'property_restored_at')) {
                $table->timestamp('property_restored_at')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_restored_by_user_id')) {
                $table->unsignedBigInteger('property_restored_by_user_id')->nullable();
                $table->foreign('property_restored_by_user_id', 'properties_restored_by_user_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });

        Schema::create('property_review_logs', function (Blueprint $table) {
            $table->id('property_review_log_id');
            $table->unsignedBigInteger('property_id');
            $table->string('property_review_action');
            $table->text('property_review_notes')->nullable();
            $table->unsignedBigInteger('property_review_performed_by_user_id');
            $table->timestamp('property_review_created_at')->useCurrent();

            $table->foreign('property_id', 'prop_review_logs_property_fk')
                ->references('property_id')
                ->on('properties')
                ->cascadeOnDelete();
            $table->foreign('property_review_performed_by_user_id', 'prop_review_logs_performed_by_fk')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();

            $table->index('property_id');
            $table->index('property_review_action');
            $table->index('property_review_performed_by_user_id');
            $table->index('property_review_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_review_logs');

        Schema::table('properties', function (Blueprint $table): void {
            $columns = [
                'property_rejected_at',
                'property_rejected_by_user_id',
                'property_archived_reason',
                'property_archived_at',
                'property_archived_by_user_id',
                'property_restored_at',
                'property_restored_by_user_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    if (str_ends_with($column, '_user_id')) {
                        $table->dropForeign([$column]);
                    }

                    $table->dropColumn($column);
                }
            }
        });
    }
};
