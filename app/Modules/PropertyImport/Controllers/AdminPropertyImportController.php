<?php

namespace App\Modules\PropertyImport\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\PropertyImport\Requests\CreatePropertyImportRequest;
use App\Modules\PropertyImport\Requests\ListPropertyImportsRequest;
use App\Modules\PropertyImport\Resources\PropertyImportJobListResource;
use App\Modules\PropertyImport\Resources\PropertyImportJobResource;
use App\Modules\PropertyImport\Services\PropertyImportService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AdminPropertyImportController extends Controller
{
    public function __construct(
        private readonly PropertyImportService $propertyImportService,
    ) {}

    public function store(CreatePropertyImportRequest $request): JsonResponse
    {
        try {
            $importJob = $this->propertyImportService->upload(
                file: $request->file('property_import_file'),
                user: $request->user(),
            );

            $this->propertyImportService->dispatch($importJob);

            return $this->successResponse(
                data: new PropertyImportJobResource($importJob->load('createdBy')),
                message: 'Property import queued successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: HttpStatus::UNPROCESSABLE_ENTITY,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to queue property import. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function index(ListPropertyImportsRequest $request): JsonResponse
    {
        $importJobs = $this->propertyImportService->list(
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new PropertyImportJobListResource($importJobs),
            message: 'Property import jobs retrieved successfully.',
        );
    }

    public function show(int $property_import_job_id): JsonResponse
    {
        try {
            $importJob = $this->propertyImportService->show($property_import_job_id);

            return $this->successResponse(
                data: new PropertyImportJobResource($importJob),
                message: 'Property import job retrieved successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property import job not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function template(): BinaryFileResponse|JsonResponse
    {
        try {
            $template = $this->propertyImportService->generateTemplateFile();

            return response()->download(
                $template['absolute_path'],
                $template['file_name'],
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to generate property import template.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }
}
