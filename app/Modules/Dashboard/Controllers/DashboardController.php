<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Requests\AdminDashboardRequest;
use App\Modules\Dashboard\Resources\AdminDashboardResource;
use App\Modules\Dashboard\Resources\DashboardResource;
use App\Modules\Dashboard\Services\AdminDashboardService;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly AdminDashboardService $adminDashboardService,
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

    public function admin(AdminDashboardRequest $request): JsonResponse
    {
        return $this->successResponse(
            data: new AdminDashboardResource(
                $this->adminDashboardService->dashboard(),
            ),
            message: 'Dashboard data retrieved successfully',
        );
    }
}
