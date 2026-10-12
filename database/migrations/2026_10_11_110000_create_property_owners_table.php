<?php

use App\Modules\Property\Support\DefaultPropertyOwners;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_owners')) {
            Schema::create('property_owners', function (Blueprint $table): void {
                $table->id('property_owner_id');
                $table->foreignId('property_id')->constrained('properties', 'property_id')->cascadeOnDelete();
                $table->string('property_owner_name')->nullable();
                $table->string('property_owner_phone', 20)->nullable();
                $table->string('property_owner_email')->nullable();
                $table->unsignedSmallInteger('property_owner_sort_order')->default(0);
                $table->timestamps();

                $table->index('property_id', 'property_owners_property_idx');
            });
        }

        if (! Schema::hasTable('properties')) {
            return;
        }

        $now = now();
        $defaults = DefaultPropertyOwners::list();

        DB::table('properties')
            ->select('property_id')
            ->orderBy('property_id')
            ->chunkById(200, function ($properties) use ($now, $defaults): void {
                $rows = [];

                foreach ($properties as $property) {
                    foreach ($defaults as $index => $owner) {
                        $rows[] = [
                            'property_id' => $property->property_id,
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
        Schema::dropIfExists('property_owners');
    }
};
