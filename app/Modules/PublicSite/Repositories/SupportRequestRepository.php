<?php

namespace App\Modules\PublicSite\Repositories;

use App\Modules\PublicSite\Models\SupportRequest;

class SupportRequestRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SupportRequest
    {
        return SupportRequest::query()->create($attributes);
    }
}
