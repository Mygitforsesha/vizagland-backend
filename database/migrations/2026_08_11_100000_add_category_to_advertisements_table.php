<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('advertisement_category')->default('general')->after('advertisement_type');
            $table->index('advertisement_category');
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropIndex(['advertisement_category']);
            $table->dropColumn('advertisement_category');
        });
    }
};
