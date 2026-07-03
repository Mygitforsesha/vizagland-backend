<?php

namespace App\Http\Controllers;

use App\Modules\Property\Services\PropertyMediaStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageController extends Controller
{
    public function __construct(
        private readonly PropertyMediaStorage $propertyMediaStorage,
    ) {}

    public function show(string $path): BinaryFileResponse
    {
        if (str_contains($path, '..')) {
            abort(404);
        }

        $absolutePath = $this->propertyMediaStorage->resolveAbsolutePath($path);

        if ($absolutePath === null) {
            abort(404);
        }

        return response()->file($absolutePath);
    }
}
