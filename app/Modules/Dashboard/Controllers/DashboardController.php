<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Resources\DashboardResource;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function employee(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new DashboardResource(
                $this->dashboardService->employeeDashboard($request->user()),
            ),
        );
    }

    public function agent(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new DashboardResource(
                $this->dashboardService->agentDashboard($request->user()),
            ),
        );
    }

    public function admin(): JsonResponse
    {
        return $this->successResponse(
            data: new DashboardResource(
                $this->dashboardService->adminDashboard(),
            ),
        );
    }
}
