<?php

namespace App\Modules\Report\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Report\Requests\CreateExportRequest;
use App\Modules\Report\Requests\ListExportsRequest;
use App\Modules\Report\Resources\ExportJobListResource;
use App\Modules\Report\Resources\ExportJobResource;
use App\Modules\Report\Services\ExportService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {}

    public function index(ListExportsRequest $request): JsonResponse
    {
        $exports = $this->exportService->listForUser(
            userId: $request->user()->user_id,
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new ExportJobListResource($exports),
            message: 'Export history retrieved successfully.',
        );
    }

    public function store(CreateExportRequest $request): JsonResponse
    {
        try {
            $exportJob = $this->exportService->create(
                user: $request->user(),
                type: $request->exportType(),
                format: $request->exportFormat(),
                filters: $request->filters(),
            );

            return $this->successResponse(
                data: new ExportJobResource($exportJob),
                message: 'Export generated successfully.',
                statusCode: HttpStatus::CREATED,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Failed to generate export. Please try again.',
                statusCode: HttpStatus::INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function download(int $export_job_id): StreamedResponse|JsonResponse
    {
        try {
            $exportJob = $this->exportService->downloadPath(
                exportJobId: $export_job_id,
                userId: request()->user()->user_id,
            );

            return Storage::disk('local')->download(
                $exportJob->export_job_file_path,
                $exportJob->export_job_file_name,
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Export job not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: HttpStatus::UNPROCESSABLE_ENTITY,
            );
        }
    }
}
