<?php

use App\Modules\Property\Support\DefaultPropertyOwners;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('properties') || ! Schema::hasTable('property_owners')) {
            return;
        }

        $now = now();
        $defaults = DefaultPropertyOwners::list();

        DB::table('properties')
            ->select('property_id')
            ->orderBy('property_id')
            ->chunkById(200, function ($properties) use ($now, $defaults): void {
                $propertyIds = $properties->pluck('property_id')->all();

                DB::table('property_owners')->whereIn('property_id', $propertyIds)->delete();

                $rows = [];

                foreach ($propertyIds as $propertyId) {
                    foreach ($defaults as $index => $owner) {
                        $rows[] = [
                            'property_id' => $propertyId,
                            'property_owner_name' => $owner['property_owner_name'],
                            'property_owner_phone' => $owner['property_owner_phone'],
                            'property_owner_email' => $owner['property_owner_email'],
                            'property_owner_sort_order' => $index,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('property_owners')->insert($rows);
                }
            }, 'property_id');
    }

    public function down(): void
    {
        // Owner rows are property data. Leaving them in place is safer than deleting every owner.
    }
};
