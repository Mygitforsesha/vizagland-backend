<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->renameUsersColumns();
        $this->renamePropertyReviewColumns();
        $this->renamePropertyDuplicateMatchColumns();

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->revertPropertyDuplicateMatchColumns();
        $this->revertPropertyReviewColumns();
        $this->revertUsersColumns();

        Schema::enableForeignKeyConstraints();
    }

    private function renameUsersColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['is_active']);
            });
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            DB::statement('ALTER TABLE users CHANGE email_verified_at user_email_verified_at TIMESTAMP NULL');
        }

        if (Schema::hasColumn('users', 'is_active')) {
            DB::statement('ALTER TABLE users CHANGE is_active user_is_active TINYINT(1) NOT NULL DEFAULT 1');
        }

        if (Schema::hasColumn('users', 'last_login_at')) {
            DB::statement('ALTER TABLE users CHANGE last_login_at user_last_login_at TIMESTAMP NULL');
        }

        if (Schema::hasColumn('users', 'remember_token')) {
            DB::statement('ALTER TABLE users CHANGE remember_token user_remember_token VARCHAR(100) NULL');
        }

        if (Schema::hasColumn('users', 'id')) {
            DB::statement('ALTER TABLE users CHANGE id user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        if (Schema::hasColumn('users', 'user_is_active') && ! $this->indexExists('users', 'users_user_is_active_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index('user_is_active');
            });
        }
    }

    private function revertUsersColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'user_is_active')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['user_is_active']);
            });
        }

        if (Schema::hasColumn('users', 'user_id')) {
            DB::statement('ALTER TABLE users CHANGE user_id id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        if (Schema::hasColumn('users', 'user_remember_token')) {
            DB::statement('ALTER TABLE users CHANGE user_remember_token remember_token VARCHAR(100) NULL');
        }

        if (Schema::hasColumn('users', 'user_last_login_at')) {
            DB::statement('ALTER TABLE users CHANGE user_last_login_at last_login_at TIMESTAMP NULL');
        }

        if (Schema::hasColumn('users', 'user_is_active')) {
            DB::statement('ALTER TABLE users CHANGE user_is_active is_active TINYINT(1) NOT NULL DEFAULT 1');
        }

        if (Schema::hasColumn('users', 'user_email_verified_at')) {
            DB::statement('ALTER TABLE users CHANGE user_email_verified_at email_verified_at TIMESTAMP NULL');
        }

        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index('is_active');
            });
        }
    }

    private function renamePropertyReviewColumns(): void
    {
        if (! Schema::hasTable('property_reviews')) {
            return;
        }

        if (Schema::hasColumn('property_reviews', 'reviewed_at')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->dropIndex(['reviewed_at']);
            });
        }

        if (Schema::hasColumn('property_reviews', 'reviewed_by')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->dropForeign(['reviewed_by']);
                $table->dropIndex(['reviewed_by']);
            });
        }

        if (Schema::hasColumn('property_reviews', 'review_status')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->dropIndex(['review_status']);
            });
        }

        if (Schema::hasColumn('property_reviews', 'reviewed_by')) {
            DB::statement('ALTER TABLE property_reviews CHANGE reviewed_by property_review_reviewed_by BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasColumn('property_reviews', 'review_status')) {
            DB::statement('ALTER TABLE property_reviews CHANGE review_status property_review_status VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('property_reviews', 'review_comments')) {
            DB::statement('ALTER TABLE property_reviews CHANGE review_comments property_review_comments TEXT NULL');
        }

        if (Schema::hasColumn('property_reviews', 'reviewed_at')) {
            DB::statement('ALTER TABLE property_reviews CHANGE reviewed_at property_review_reviewed_at TIMESTAMP NULL');
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_by') && ! $this->foreignKeyExists('property_reviews', 'property_review_reviewed_by')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->foreign('property_review_reviewed_by')
                    ->references('user_id')
                    ->on('users')
                    ->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_by') && ! $this->indexExists('property_reviews', 'property_reviews_property_review_reviewed_by_index')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->index('property_review_reviewed_by', 'pr_reviewed_by_idx');
            });
        }

        if (Schema::hasColumn('property_reviews', 'property_review_status') && ! $this->indexExists('property_reviews', 'pr_review_status_idx')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->index('property_review_status', 'pr_review_status_idx');
            });
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_at') && ! $this->indexExists('property_reviews', 'pr_reviewed_at_idx')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->index('property_review_reviewed_at', 'pr_reviewed_at_idx');
            });
        }
    }

    private function revertPropertyReviewColumns(): void
    {
        if (! Schema::hasTable('property_reviews')) {
            return;
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_at')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->dropIndex(['property_review_reviewed_at']);
            });
        }

        if (Schema::hasColumn('property_reviews', 'property_review_status')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->dropIndex(['property_review_status']);
            });
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_by')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->dropForeign(['property_review_reviewed_by']);
                $table->dropIndex(['property_review_reviewed_by']);
            });
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_by')) {
            DB::statement('ALTER TABLE property_reviews CHANGE property_review_reviewed_by reviewed_by BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasColumn('property_reviews', 'property_review_status')) {
            DB::statement('ALTER TABLE property_reviews CHANGE property_review_status review_status VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('property_reviews', 'property_review_comments')) {
            DB::statement('ALTER TABLE property_reviews CHANGE property_review_comments review_comments TEXT NULL');
        }

        if (Schema::hasColumn('property_reviews', 'property_review_reviewed_at')) {
            DB::statement('ALTER TABLE property_reviews CHANGE property_review_reviewed_at reviewed_at TIMESTAMP NULL');
        }

        if (Schema::hasColumn('property_reviews', 'reviewed_by')) {
            Schema::table('property_reviews', function (Blueprint $table): void {
                $table->foreign('reviewed_by')->references('id')->on('users')->restrictOnDelete();
                $table->index('reviewed_by');
                $table->index('review_status');
                $table->index('reviewed_at');
            });
        }
    }

    private function renamePropertyDuplicateMatchColumns(): void
    {
        if (! Schema::hasTable('property_duplicate_matches')) {
            return;
        }

        if (Schema::hasColumn('property_duplicate_matches', 'match_status')) {
            Schema::table('property_duplicate_matches', function (Blueprint $table): void {
                $table->dropIndex(['match_status']);
            });
        }

        if (Schema::hasColumn('property_duplicate_matches', 'match_percentage')) {
            Schema::table('property_duplicate_matches', function (Blueprint $table): void {
                $table->dropIndex(['match_percentage']);
            });
        }

        if (Schema::hasColumn('property_duplicate_matches', 'match_percentage')) {
            DB::statement('ALTER TABLE property_duplicate_matches CHANGE match_percentage property_duplicate_match_percentage DECIMAL(5,2) NOT NULL');
        }

        if (Schema::hasColumn('property_duplicate_matches', 'match_status')) {
            DB::statement('ALTER TABLE property_duplicate_matches CHANGE match_status property_duplicate_match_status VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('property_duplicate_matches', 'property_duplicate_match_status') && ! $this->indexExists('property_duplicate_matches', 'pdm_match_status_idx')) {
            Schema::table('property_duplicate_matches', function (Blueprint $table): void {
                $table->index('property_duplicate_match_status', 'pdm_match_status_idx');
                $table->index('property_duplicate_match_percentage', 'pdm_match_pct_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }

    private function foreignKeyExists(string $table, string $column): bool
    {
        $foreignKeys = Schema::getForeignKeys($table);

        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey['columns'] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    private function revertPropertyDuplicateMatchColumns(): void
    {
        if (! Schema::hasTable('property_duplicate_matches')) {
            return;
        }

        if (Schema::hasColumn('property_duplicate_matches', 'property_duplicate_match_status')) {
            Schema::table('property_duplicate_matches', function (Blueprint $table): void {
                $table->dropIndex('pdm_match_status_idx');
                $table->dropIndex('pdm_match_pct_idx');
            });
        }

        if (Schema::hasColumn('property_duplicate_matches', 'property_duplicate_match_percentage')) {
            DB::statement('ALTER TABLE property_duplicate_matches CHANGE property_duplicate_match_percentage match_percentage DECIMAL(5,2) NOT NULL');
        }

        if (Schema::hasColumn('property_duplicate_matches', 'property_duplicate_match_status')) {
            DB::statement('ALTER TABLE property_duplicate_matches CHANGE property_duplicate_match_status match_status VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('property_duplicate_matches', 'match_status')) {
            Schema::table('property_duplicate_matches', function (Blueprint $table): void {
                $table->index('match_status');
                $table->index('match_percentage');
            });
        }
    }
};
