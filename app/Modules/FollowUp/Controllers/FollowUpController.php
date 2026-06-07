<?php

namespace App\Modules\FollowUp\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\FollowUp\Requests\CreateFollowUpRequest;
use App\Modules\FollowUp\Requests\ListFollowUpsRequest;
use App\Modules\FollowUp\Requests\UpdateFollowUpRequest;
use App\Modules\FollowUp\Resources\FollowUpListResource;
use App\Modules\FollowUp\Resources\FollowUpResource;
use App\Modules\FollowUp\Services\FollowUpService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class FollowUpController extends Controller
{
    public function __construct(
        private readonly FollowUpService $followUpService,
    ) {}

    public function store(CreateFollowUpRequest $request): JsonResponse
    {
        try {
            $followUp = $this->followUpService->create(
                $request->followUpAttributes(),
                $request->user(),
            );

            return $this->successResponse(
                data: new FollowUpResource($followUp->load(['property', 'lead', 'createdBy', 'assignedTo'])),
                message: 'Follow-up created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create follow-up. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(UpdateFollowUpRequest $request, int $follow_up_id): JsonResponse
    {
        try {
            $followUp = $this->followUpService->update($follow_up_id, $request->updateAttributes());

            return $this->successResponse(
                data: new FollowUpResource($followUp->load(['property', 'lead', 'createdBy', 'assignedTo'])),
                message: 'Follow-up updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Follow-up not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update follow-up. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function index(ListFollowUpsRequest $request): JsonResponse
    {
        $followUps = $this->followUpService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new FollowUpListResource($followUps),
        );
    }

    public function byProperty(int $property_id): JsonResponse
    {
        $followUps = $this->followUpService->listByProperty($property_id);

        return $this->successResponse(
            data: FollowUpResource::collection($followUps),
        );
    }

    public function byLead(int $lead_id): JsonResponse
    {
        $followUps = $this->followUpService->listByLead($lead_id);

        return $this->successResponse(
            data: FollowUpResource::collection($followUps),
        );
    }
}
