<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\PublicSite\Models\SupportRequest;
use App\Modules\PublicSite\Repositories\SupportRequestRepository;

class SupportRequestService
{
    public function __construct(
        private readonly SupportRequestRepository $supportRequestRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SupportRequest
    {
        return $this->supportRequestRepository->create([
            ...$data,
            'support_request_status' => 'new',
        ]);
    }
}
