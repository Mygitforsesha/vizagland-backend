<?php

namespace App\Modules\PublicSite\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Requests\ListPublicPropertiesRequest;
use App\Modules\PublicSite\Resources\PublicPropertyDetailsResource;
use App\Modules\PublicSite\Resources\PublicPropertyListItemResource;
use App\Modules\PublicSite\Resources\PublicPropertyListResource;
use App\Modules\PublicSite\Services\PublicPropertyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class PublicPropertyController extends Controller
{
    public function __construct(
        private readonly PublicPropertyService $publicPropertyService,
    ) {}

    public function index(ListPublicPropertiesRequest $request): JsonResponse
    {
        $properties = $this->publicPropertyService->list(
            filters: $request->filters(),
            perPage: $request->perPage(),
        );

        return $this->successResponse(
            data: new PublicPropertyListResource($properties),
        );
    }

    public function browseLand(): JsonResponse
    {
        return $this->successResponse(
            data: $this->publicPropertyService->browseLand(),
            message: 'Browse area data retrieved successfully.',
        );
    }

    public function show(int $property_id): JsonResponse
    {
        try {
            $property = $this->publicPropertyService->show($property_id);

            return $this->successResponse(
                data: new PublicPropertyDetailsResource($property),
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Property not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }

    public function featured(): JsonResponse
    {
        $properties = $this->publicPropertyService->featured();

        return $this->successResponse(
            data: PublicPropertyListItemResource::collection($properties),
        );
    }
}
