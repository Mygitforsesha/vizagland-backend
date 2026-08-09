<?php

namespace App\Modules\PropertyFieldConfiguration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyFieldConfiguration\Resources\PublicMasterDropdownsResource;
use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use Illuminate\Http\JsonResponse;

class PublicMasterDropdownController extends Controller
{
    public function __construct(
        private readonly PropertyFieldConfigurationService $propertyFieldConfigurationService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->successResponse(
            data: new PublicMasterDropdownsResource(
                $this->propertyFieldConfigurationService->publicMasterDropdowns(),
            ),
            message: 'Master dropdowns retrieved successfully.',
        );
    }
}
