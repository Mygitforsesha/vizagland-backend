<?php

use App\Modules\ActivityLog\Services\ActivityLogBackfillService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ActivityLogBackfillService::class)->backfill();
    }

    public function down(): void
    {
        // Historical backfill rows are not reversed.
    }
};
