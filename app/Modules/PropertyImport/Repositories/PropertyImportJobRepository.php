<?php

namespace App\Modules\PropertyImport\Repositories;

use App\Modules\PropertyImport\Models\PropertyImportError;
use App\Modules\PropertyImport\Models\PropertyImportJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertyImportJobRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PropertyImportJob
    {
        return PropertyImportJob::query()->create($attributes);
    }

    public function findById(int $propertyImportJobId): ?PropertyImportJob
    {
        return PropertyImportJob::query()->find($propertyImportJobId);
    }

    public function findByIdWithRelations(int $propertyImportJobId): ?PropertyImportJob
    {
        return PropertyImportJob::query()
            ->with(['createdBy', 'errors' => static fn ($query) => $query->orderBy('property_import_row_number')])
            ->find($propertyImportJobId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(PropertyImportJob $importJob, array $attributes): PropertyImportJob
    {
        $importJob->update($attributes);

        return $importJob->fresh();
    }

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return PropertyImportJob::query()
            ->with('createdBy')
            ->orderByDesc('property_import_job_id')
            ->paginate($perPage);
    }
}
