<?php

namespace App\Modules\Report\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Report\Requests\ReportsDashboardRequest;
use App\Modules\Report\Resources\ReportsDashboardResource;
use App\Modules\Report\Services\ReportService;
use Illuminate\Http\JsonResponse;

class AdminReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function dashboard(ReportsDashboardRequest $request): JsonResponse
    {
        return $this->successResponse(
            data: new ReportsDashboardResource(
                $this->reportService->dashboard($request->period()),
            ),
            message: 'Reports dashboard retrieved successfully.',
        );
    }
}
