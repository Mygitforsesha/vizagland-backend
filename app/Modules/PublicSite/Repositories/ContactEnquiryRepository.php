<?php

namespace App\Modules\PublicSite\Repositories;

use App\Modules\PublicSite\Models\ContactEnquiry;

class ContactEnquiryRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ContactEnquiry
    {
        return ContactEnquiry::query()->create($attributes);
    }
}
