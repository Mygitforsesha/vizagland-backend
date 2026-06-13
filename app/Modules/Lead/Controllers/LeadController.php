<?php

namespace App\Modules\Lead\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Lead\Enums\LeadSource;
use App\Modules\Lead\Requests\AssignLeadRequest;
use App\Modules\Lead\Requests\CreateLeadRequest;
use App\Modules\Lead\Requests\ListLeadsRequest;
use App\Modules\Lead\Requests\UpdateLeadRequest;
use App\Modules\Lead\Resources\LeadListResource;
use App\Modules\Lead\Resources\LeadResource;
use App\Modules\Lead\Services\LeadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
    ) {}

    public function storePublic(CreateLeadRequest $request): JsonResponse
    {
        return $this->createLead(
            fn () => $this->leadService->createPublic($request->leadAttributes()),
        );
    }

    public function storeAgent(CreateLeadRequest $request): JsonResponse
    {
        return $this->createLead(
            fn () => $this->leadService->createAuthenticated(
                $request->leadAttributes(),
                $request->user(),
                LeadSource::Agent,
            ),
        );
    }

    public function storeEmployee(CreateLeadRequest $request): JsonResponse
    {
        return $this->createLead(
            fn () => $this->leadService->createAuthenticated(
                $request->leadAttributes(),
                $request->user(),
                LeadSource::Employee,
            ),
        );
    }

    public function index(ListLeadsRequest $request): JsonResponse
    {
        $leads = $this->leadService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
            user: $request->user(),
        );

        return $this->successResponse(
            data: new LeadListResource($leads),
        );
    }

    public function show(Request $request, int $lead_id): JsonResponse
    {
        try {
            $lead = $this->leadService->show($lead_id, $request->user());

            return $this->successResponse(
                data: new LeadResource($lead),
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Lead not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function update(UpdateLeadRequest $request, int $lead_id): JsonResponse
    {
        try {
            $lead = $this->leadService->update(
                $lead_id,
                $request->updateAttributes(),
                $request->user(),
            );

            return $this->successResponse(
                data: new LeadResource($lead->load(['property', 'createdBy', 'assignedTo'])),
                message: 'Lead updated successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Lead not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to update lead. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function assign(AssignLeadRequest $request, int $lead_id): JsonResponse
    {
        try {
            $lead = $this->leadService->assign(
                leadId: $lead_id,
                assigneeId: $request->assigneeId(),
                assignedBy: $request->user(),
                remarks: $request->remarks(),
            );

            return $this->successResponse(
                data: new LeadResource($lead->load(['property', 'createdBy', 'assignedTo', 'assignments'])),
                message: 'Lead assigned successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Lead not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to assign lead. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    private function createLead(callable $action): JsonResponse
    {
        try {
            $lead = $action();

            return $this->successResponse(
                data: new LeadResource($lead),
                message: 'Lead created successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to create lead. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
