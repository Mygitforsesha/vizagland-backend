<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            if (! Schema::hasColumn('advertisements', 'advertisement_whatsapp_numbers')) {
                $table->json('advertisement_whatsapp_numbers')->nullable();
            }

            if (! Schema::hasColumn('advertisements', 'advertisement_email')) {
                $table->string('advertisement_email')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            if (Schema::hasColumn('advertisements', 'advertisement_email')) {
                $table->dropColumn('advertisement_email');
            }

            if (Schema::hasColumn('advertisements', 'advertisement_whatsapp_numbers')) {
                $table->dropColumn('advertisement_whatsapp_numbers');
            }
        });
    }
};
