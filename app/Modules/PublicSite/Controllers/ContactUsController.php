<?php

namespace App\Modules\PublicSite\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Requests\CreateContactEnquiryRequest;
use App\Modules\PublicSite\Resources\ContactEnquiryResource;
use App\Modules\PublicSite\Resources\ContactUsPageResource;
use App\Modules\PublicSite\Services\ContactEnquiryService;
use App\Modules\PublicSite\Services\ContactUsPageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ContactUsController extends Controller
{
    public function __construct(
        private readonly ContactUsPageService $contactUsPageService,
        private readonly ContactEnquiryService $contactEnquiryService,
    ) {}

    public function show(): JsonResponse
    {
        return $this->successResponse(
            data: new ContactUsPageResource(
                $this->contactUsPageService->getPageContent(),
            ),
        );
    }

    public function store(CreateContactEnquiryRequest $request): JsonResponse
    {
        try {
            $contactEnquiry = $this->contactEnquiryService->create(
                $request->contactEnquiryAttributes(),
            );

            return $this->successResponse(
                data: new ContactEnquiryResource($contactEnquiry),
                message: 'Contact enquiry submitted successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to submit contact enquiry. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
