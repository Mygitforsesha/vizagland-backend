<?php

namespace App\Modules\Advertisement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Advertisement\Requests\ListPublicAdvertisementsRequest;
use App\Modules\Advertisement\Resources\AdvertisementPublicResource;
use App\Modules\Advertisement\Services\AdvertisementService;
use Illuminate\Http\JsonResponse;

class PublicAdvertisementController extends Controller
{
    public function __construct(
        private readonly AdvertisementService $advertisementService,
    ) {}

    public function index(ListPublicAdvertisementsRequest $request): JsonResponse
    {
        $advertisements = $this->advertisementService->listPublic(
            filters: $request->filters(),
        );

        return $this->successResponse(
            data: AdvertisementPublicResource::collection($advertisements),
            message: 'Advertisements retrieved successfully.',
        );
    }
}
