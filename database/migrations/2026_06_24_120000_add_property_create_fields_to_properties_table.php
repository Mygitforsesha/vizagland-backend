<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_flat_door_no')->nullable()->after('property_floor_number');
            $table->string('property_youtube_video_link')->nullable()->after('property_other_service_name');
            $table->string('property_location_link')->nullable()->after('property_youtube_video_link');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'property_flat_door_no',
                'property_youtube_video_link',
                'property_location_link',
            ]);
        });
    }
};
