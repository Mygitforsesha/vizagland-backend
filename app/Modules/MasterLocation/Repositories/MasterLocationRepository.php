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

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function existsExactRecord(array $attributes): bool
    {
        return $this->applyExactAttributeFilters(
            MasterLocation::query(),
            $attributes,
        )->exists();
    }

    public function search(string $term, ?int $limit = null, ?int $page = null): Collection|LengthAwarePaginator
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
            return $query->paginate(perPage: $limit ?? 20, page: $page);
        }

        if ($limit !== null) {
            return $query->limit($limit)->get();
        }

        return $query->get();
    }

    public function list(?int $limit = null, ?int $page = null): Collection|LengthAwarePaginator
    {
        $query = MasterLocation::query()
            ->orderBy('master_location_village')
            ->orderBy('master_location_id');

        if ($page !== null) {
            return $query->paginate(perPage: $limit ?? 20, page: $page);
        }

        if ($limit !== null) {
            return $query->limit($limit)->get();
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAdmin(
        array $filters,
        int $perPage,
        string $sortBy,
        string $sortDirection,
    ): LengthAwarePaginator {
        $query = MasterLocation::query();

        $this->applyAdminFilters($query, $filters);
        $this->applyAdminSorting($query, $sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    public function truncate(): void
    {
        DB::table('master_locations')->truncate();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): MasterLocation
    {
        return MasterLocation::query()->create($attributes);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function insertMany(array $rows): void
    {
        MasterLocation::query()->insert($rows);
    }

    /**
     * @param  Builder<MasterLocation>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyAdminFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search']) && is_string($filters['search'])) {
            $term = strtolower($filters['search']);

            $query->where(function (Builder $builder) use ($term): void {
                foreach (self::SEARCHABLE_COLUMNS as $column) {
                    $builder->orWhereRaw("LOWER({$column}) LIKE ?", ['%'.$term.'%']);
                }
            });
        }

        if (! empty($filters['master_location_district']) && is_string($filters['master_location_district'])) {
            $query->whereRaw(
                'LOWER(master_location_district) = ?',
                [strtolower($filters['master_location_district'])],
            );
        }

        if (! empty($filters['master_location_mandal']) && is_string($filters['master_location_mandal'])) {
            $query->whereRaw(
                'LOWER(master_location_mandal) = ?',
                [strtolower($filters['master_location_mandal'])],
            );
        }
    }

    /**
     * @param  Builder<MasterLocation>  $query
     */
    private function applyAdminSorting(Builder $query, string $sortBy, string $sortDirection): void
    {
        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'master_location_id') {
            $query->orderBy('master_location_id');
        }
    }

    /**
     * @param  Builder<MasterLocation>  $query
     * @param  array<string, mixed>  $attributes
     * @return Builder<MasterLocation>
     */
    private function applyExactAttributeFilters(Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);

                continue;
            }

            $query->where($column, $value);
        }

        return $query;
    }
}
