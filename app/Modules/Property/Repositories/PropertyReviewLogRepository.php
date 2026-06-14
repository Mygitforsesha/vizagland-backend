<?php

namespace App\Modules\Property\Repositories;

use App\Modules\Property\Models\PropertyReviewLog;

class PropertyReviewLogRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PropertyReviewLog
    {
        return PropertyReviewLog::query()->create($attributes);
    }
}
