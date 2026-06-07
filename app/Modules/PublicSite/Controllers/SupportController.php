<?php

namespace App\Modules\PublicSite\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Requests\CreateSupportRequest;
use App\Modules\PublicSite\Resources\SupportRequestResource;
use App\Modules\PublicSite\Services\SupportRequestService;
use Illuminate\Http\JsonResponse;
use Throwable;

class SupportController extends Controller
{
    public function __construct(
        private readonly SupportRequestService $supportRequestService,
    ) {}

    public function store(CreateSupportRequest $request): JsonResponse
    {
        try {
            $supportRequest = $this->supportRequestService->create(
                $request->supportAttributes(),
            );

            return $this->successResponse(
                data: new SupportRequestResource($supportRequest),
                message: 'Support request submitted successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to submit support request. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
