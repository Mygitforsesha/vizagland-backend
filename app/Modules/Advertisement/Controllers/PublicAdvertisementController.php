<?php

namespace App\Modules\Advertisement\Controllers;

use App\Constants\HttpStatus;
use App\Http\Controllers\Controller;
use App\Modules\Advertisement\Requests\ListPublicAdvertisementsRequest;
use App\Modules\Advertisement\Resources\AdvertisementPublicResource;
use App\Modules\Advertisement\Services\AdvertisementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class PublicAdvertisementController extends Controller
{
    public function __construct(
        private readonly AdvertisementService $advertisementService,
    ) {}

    public function index(ListPublicAdvertisementsRequest $request): JsonResponse
    {
        $sections = $this->advertisementService->listPublicSections();

        return $this->successResponse(
            data: [
                'village_wise_ads' => AdvertisementPublicResource::collection($sections['village_wise_ads']),
                'general_ads' => AdvertisementPublicResource::collection($sections['general_ads']),
                'latest_ads' => AdvertisementPublicResource::collection($sections['latest_ads']),
            ],
            message: 'Advertisements retrieved successfully.',
        );
    }

    public function show(int $advertisement_id): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService->showPublic($advertisement_id);

            return $this->successResponse(
                data: new AdvertisementPublicResource($advertisement),
                message: 'Advertisement retrieved successfully.',
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                message: 'Advertisement not found.',
                statusCode: HttpStatus::NOT_FOUND,
            );
        }
    }
}
