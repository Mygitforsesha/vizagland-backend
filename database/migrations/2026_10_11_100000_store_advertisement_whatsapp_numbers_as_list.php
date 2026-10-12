<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            if (! Schema::hasColumn('advertisements', 'advertisement_email')) {
                $table->string('advertisement_email')->nullable();
            }

            if (! Schema::hasColumn('advertisements', 'advertisement_whatsapp_numbers')) {
                $table->json('advertisement_whatsapp_numbers')->nullable();
            }
        });

        if (! Schema::hasColumn('advertisements', 'advertisement_whatsapp_number')) {
            return;
        }

        $rows = DB::table('advertisements')
            ->whereNotNull('advertisement_whatsapp_number')
            ->where('advertisement_whatsapp_number', '!=', '')
            ->get(['advertisement_id', 'advertisement_whatsapp_number']);

        foreach ($rows as $row) {
            DB::table('advertisements')
                ->where('advertisement_id', $row->advertisement_id)
                ->update([
                    'advertisement_whatsapp_numbers' => json_encode([$row->advertisement_whatsapp_number]),
                ]);
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn('advertisement_whatsapp_number');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        if (! Schema::hasColumn('advertisements', 'advertisement_whatsapp_number')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->string('advertisement_whatsapp_number', 20)->nullable();
            });
        }

        if (! Schema::hasColumn('advertisements', 'advertisement_whatsapp_numbers')) {
            return;
        }

        $rows = DB::table('advertisements')
            ->whereNotNull('advertisement_whatsapp_numbers')
            ->get(['advertisement_id', 'advertisement_whatsapp_numbers']);

        foreach ($rows as $row) {
            $numbers = json_decode((string) $row->advertisement_whatsapp_numbers, true);
            $first = is_array($numbers) ? ($numbers[0] ?? null) : null;

            if (is_string($first) && $first !== '') {
                DB::table('advertisements')
                    ->where('advertisement_id', $row->advertisement_id)
                    ->update(['advertisement_whatsapp_number' => $first]);
            }
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn('advertisement_whatsapp_numbers');
        });
    }
};
