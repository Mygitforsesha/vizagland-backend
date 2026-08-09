<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Models\PropertySearchHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class PropertySearchHistoryRepository
{
    private static ?string $resolvedCreatedAtColumn = null;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PropertySearchHistory
    {
        $createdAtColumn = $this->createdAtColumn();

        if ($createdAtColumn !== null && ! array_key_exists($createdAtColumn, $attributes)) {
            $attributes[$createdAtColumn] = now();
        }

        return PropertySearchHistory::query()->create($attributes);
    }

    public function paginate(int $perPage, int $page): LengthAwarePaginator
    {
        $query = PropertySearchHistory::query()
            ->with(['user:user_id,user_full_name,user_role']);

        $createdAtColumn = $this->createdAtColumn();

        if ($createdAtColumn !== null) {
            $query->orderByDesc($createdAtColumn);
        }

        // Always available; keeps API working even before the created_at column migration runs.
        $query->orderByDesc('property_search_history_id');

        return $query->paginate(perPage: $perPage, page: $page);
    }

    private function createdAtColumn(): ?string
    {
        if (self::$resolvedCreatedAtColumn !== null) {
            return self::$resolvedCreatedAtColumn === '' ? null : self::$resolvedCreatedAtColumn;
        }

        if (! Schema::hasTable('property_search_histories')) {
            self::$resolvedCreatedAtColumn = '';

            return null;
        }

        if (Schema::hasColumn('property_search_histories', 'property_search_history_created_at')) {
            self::$resolvedCreatedAtColumn = 'property_search_history_created_at';

            return self::$resolvedCreatedAtColumn;
        }

        if (Schema::hasColumn('property_search_histories', 'created_at')) {
            self::$resolvedCreatedAtColumn = 'created_at';

            return self::$resolvedCreatedAtColumn;
        }

        self::$resolvedCreatedAtColumn = '';

        return null;
    }
}
