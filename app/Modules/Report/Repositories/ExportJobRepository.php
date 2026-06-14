<?php

namespace App\Modules\Report\Repositories;

use App\Modules\Report\Models\ExportJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExportJobRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ExportJob
    {
        return ExportJob::query()->create($attributes);
    }

    public function findById(int $exportJobId): ?ExportJob
    {
        return ExportJob::query()
            ->with('user:user_id,user_full_name')
            ->where('export_job_id', $exportJobId)
            ->first();
    }

    public function findForUser(int $exportJobId, int $userId): ?ExportJob
    {
        return ExportJob::query()
            ->where('export_job_id', $exportJobId)
            ->where('export_job_user_id', $userId)
            ->first();
    }

    public function paginateForUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return ExportJob::query()
            ->with('user:user_id,user_full_name')
            ->where('export_job_user_id', $userId)
            ->orderByDesc('export_job_created_at')
            ->orderByDesc('export_job_id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ExportJob $exportJob, array $attributes): ExportJob
    {
        $exportJob->update($attributes);

        return $exportJob->fresh(['user']);
    }
}
