<?php

namespace App\Modules\MasterLocation\Repositories;

use App\Modules\MasterLocation\Models\MasterLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MasterLocationRepository
{
    /**
     * @var list<string>
     */
    public const SEARCHABLE_COLUMNS = [
        'master_location_village',
        'master_location_nearby_location',
        'master_location_additional_nearby_location',
        'master_location_district',
        'master_location_mandal',
        'master_location_panchayati',
        'master_location_gvmc_zone',
        'master_location_gvmc_ward',
        'master_location_vmrda',
        'master_location_registration_office',
        'master_location_authority',
    ];

    public function count(): int
    {
        return MasterLocation::query()->count();
    }

    public function search(string $term, int $limit, ?int $page = null): Collection|LengthAwarePaginator
    {
        $query = MasterLocation::query()
            ->where(function (Builder $builder) use ($term): void {
                foreach (self::SEARCHABLE_COLUMNS as $column) {
                    $builder->orWhereRaw("LOWER({$column}) LIKE ?", ['%'.$term.'%']);
                }
            })
            ->orderByRaw(
                'CASE WHEN LOWER(master_location_village) LIKE ? THEN 0 ELSE 1 END',
                ['%'.$term.'%'],
            )
            ->orderBy('master_location_village')
            ->orderBy('master_location_id');

        if ($page !== null) {
            return $query->paginate(perPage: $limit, page: $page);
        }

        return $query->limit($limit)->get();
    }

    public function truncate(): void
    {
        DB::table('master_locations')->truncate();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function insertMany(array $rows): void
    {
        MasterLocation::query()->insert($rows);
    }
}
