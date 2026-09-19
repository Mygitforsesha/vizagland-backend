<?php

namespace App\Modules\OtherService\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OtherService\Resources\OtherServicePublicResource;
use App\Modules\OtherService\Services\OtherServiceService;
use Illuminate\Http\JsonResponse;

class PublicOtherServiceController extends Controller
{
    public function __construct(
        private readonly OtherServiceService $otherServiceService,
    ) {}

    public function index(): JsonResponse
    {
        $otherServices = $this->otherServiceService->listActivePublic();

        return $this->successResponse(
            data: OtherServicePublicResource::collection($otherServices),
            message: 'Other services retrieved successfully.',
        );
    }
}
