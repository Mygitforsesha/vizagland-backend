<?php

namespace App\Modules\PropertyFieldConfiguration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyFieldConfiguration\Resources\PublicPropertyFormConfigResource;
use App\Modules\PropertyFieldConfiguration\Services\PropertyFieldConfigurationService;
use Illuminate\Http\JsonResponse;

class PublicPropertyFormConfigController extends Controller
{
    public function __construct(
        private readonly PropertyFieldConfigurationService $propertyFieldConfigurationService,
    ) {}

    public function show(): JsonResponse
    {
        return $this->successResponse(
            data: new PublicPropertyFormConfigResource(
                $this->propertyFieldConfigurationService->publicFormConfig(),
            ),
            message: 'Property form configuration retrieved successfully.',
        );
    }
}
