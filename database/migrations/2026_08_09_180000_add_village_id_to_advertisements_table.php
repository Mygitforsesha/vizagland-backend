<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->unsignedBigInteger('advertisement_village_id')->nullable()->after('advertisement_is_active');

            $table->foreign('advertisement_village_id', 'advertisements_village_fk')
                ->references('master_location_id')
                ->on('master_locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropForeign('advertisements_village_fk');
            $table->dropColumn('advertisement_village_id');
        });
    }
};
