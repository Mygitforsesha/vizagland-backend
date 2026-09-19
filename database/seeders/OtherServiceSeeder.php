<?php

namespace Database\Seeders;

use App\Modules\OtherService\Models\OtherService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OtherServiceSeeder extends Seeder
{
    /**
     * Seed the initial Other Services master records (idempotent).
     *
     * Creates only missing services by slug. Does not overwrite admin-managed
     * fields (description, icon, sort order, active status) on re-run.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Documentation', 'icon' => 'documentation', 'sort_order' => 1],
            ['name' => 'Link Document', 'icon' => 'link', 'sort_order' => 2],
            ['name' => 'LPM', 'icon' => 'landmark', 'sort_order' => 3],
            ['name' => 'Encumbrance Certificate (EC)', 'icon' => 'certificate', 'sort_order' => 4],
            ['name' => 'GPA Document', 'icon' => 'gpa', 'sort_order' => 5],
            ['name' => 'Village Map', 'icon' => 'map', 'sort_order' => 6],
            ['name' => 'Market Value', 'icon' => 'chart', 'sort_order' => 7],
            ['name' => 'Veelnama Document', 'icon' => 'handshake', 'sort_order' => 8],
            ['name' => 'Adangal', 'icon' => 'list', 'sort_order' => 9],
            ['name' => 'Sale Deed', 'icon' => 'sale-deed', 'sort_order' => 10],
            ['name' => 'FMB', 'icon' => 'grid', 'sort_order' => 11],
            ['name' => '1B', 'icon' => 'hash', 'sort_order' => 12],
        ];

        foreach ($services as $service) {
            $slug = Str::slug($service['name']);

            $exists = OtherService::query()
                ->where(function ($query) use ($slug, $service): void {
                    $query->where('other_service_slug', $slug)
                        ->orWhere('other_service_name', $service['name']);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            OtherService::query()->create([
                'other_service_name' => $service['name'],
                'other_service_slug' => $slug,
                'other_service_description' => null,
                'other_service_icon' => $service['icon'],
                'other_service_sort_order' => $service['sort_order'],
                'other_service_is_active' => true,
            ]);
        }
    }
}
