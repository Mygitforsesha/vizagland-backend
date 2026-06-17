<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\PublicSite\Enums\ContactEnquiryStatus;
use App\Modules\PublicSite\Models\ContactEnquiry;
use App\Modules\PublicSite\Repositories\ContactEnquiryRepository;

class ContactEnquiryService
{
    public function __construct(
        private readonly ContactEnquiryRepository $contactEnquiryRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ContactEnquiry
    {
        return $this->contactEnquiryRepository->create([
            ...$data,
            'contact_enquiry_status' => ContactEnquiryStatus::New,
        ]);
    }
}
