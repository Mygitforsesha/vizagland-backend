<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_search_histories')) {
            Schema::create('property_search_histories', function (Blueprint $table): void {
                $table->id('property_search_history_id');
                $table->foreignId('property_search_history_user_id')
                    ->nullable()
                    ->constrained('users', 'user_id')
                    ->nullOnDelete();
                $table->string('property_search_history_keyword')->nullable();
                $table->json('property_search_history_filters')->nullable();
                $table->unsignedInteger('property_search_history_results_count')->default(0);
                $table->string('property_search_history_ip_address', 45)->nullable();
                $table->timestamp('property_search_history_created_at')->useCurrent();

                $table->index('property_search_history_created_at');
                $table->index('property_search_history_user_id');
            });

            return;
        }

        Schema::table('property_search_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('property_search_histories', 'property_search_history_user_id')) {
                $table->foreignId('property_search_history_user_id')
                    ->nullable()
                    ->constrained('users', 'user_id')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('property_search_histories', 'property_search_history_keyword')) {
                $table->string('property_search_history_keyword')->nullable();
            }

            if (! Schema::hasColumn('property_search_histories', 'property_search_history_filters')) {
                $table->json('property_search_history_filters')->nullable();
            }

            if (! Schema::hasColumn('property_search_histories', 'property_search_history_results_count')) {
                $table->unsignedInteger('property_search_history_results_count')->default(0);
            }

            if (! Schema::hasColumn('property_search_histories', 'property_search_history_ip_address')) {
                $table->string('property_search_history_ip_address', 45)->nullable();
            }

            if (! Schema::hasColumn('property_search_histories', 'property_search_history_created_at')) {
                $table->timestamp('property_search_history_created_at')->nullable();
            }
        });

        // Backfill from Laravel default timestamp column when present.
        if (
            Schema::hasColumn('property_search_histories', 'created_at')
            && Schema::hasColumn('property_search_histories', 'property_search_history_created_at')
        ) {
            DB::table('property_search_histories')
                ->whereNull('property_search_history_created_at')
                ->update([
                    'property_search_history_created_at' => DB::raw('created_at'),
                ]);
        }

        DB::table('property_search_histories')
            ->whereNull('property_search_history_created_at')
            ->update([
                'property_search_history_created_at' => now(),
            ]);

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE property_search_histories MODIFY property_search_history_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        }

        $this->ensureIndex('property_search_histories', 'property_search_history_created_at');
        $this->ensureIndex('property_search_histories', 'property_search_history_user_id');
    }

    public function down(): void
    {
        // Non-destructive fix migration — do not drop columns that may hold data.
    }

        
    private function ensureIndex(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $indexName = match ($column) {
            'property_search_history_created_at' => 'psh_created_at_idx',
            'property_search_history_user_id' => 'psh_user_id_idx',
            default => "{$table}_{$column}_idx",
        };

        $sm = Schema::getConnection()->getSchemaBuilder();
        $indexes = collect($sm->getIndexes($table))->pluck('name')->all();

        if (in_array($indexName, $indexes, true)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName): void {
            $blueprint->index($column, $indexName);
        });
    }
};
