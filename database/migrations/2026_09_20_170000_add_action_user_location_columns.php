<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_search_histories')
            && ! Schema::hasColumn('property_search_histories', 'property_search_history_user_location')
        ) {
            Schema::table('property_search_histories', function (Blueprint $table): void {
                $table->json('property_search_history_user_location')
                    ->nullable()
                    ->after('property_search_history_mobile_number');
            });
        }

        if (Schema::hasTable('contact_enquiries')
            && ! Schema::hasColumn('contact_enquiries', 'contact_enquiry_user_location')
        ) {
            Schema::table('contact_enquiries', function (Blueprint $table): void {
                $table->json('contact_enquiry_user_location')
                    ->nullable()
                    ->after('contact_enquiry_consent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('property_search_histories')
            && Schema::hasColumn('property_search_histories', 'property_search_history_user_location')
        ) {
            Schema::table('property_search_histories', function (Blueprint $table): void {
                $table->dropColumn('property_search_history_user_location');
            });
        }

        if (Schema::hasTable('contact_enquiries')
            && Schema::hasColumn('contact_enquiries', 'contact_enquiry_user_location')
        ) {
            Schema::table('contact_enquiries', function (Blueprint $table): void {
                $table->dropColumn('contact_enquiry_user_location');
            });
        }
    }
};
