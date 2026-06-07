<?php

namespace App\Traits;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = HttpStatus::OK,
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::Success->value,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function errorResponse(
        string $message = 'An error occurred',
        int $statusCode = HttpStatus::BAD_REQUEST,
        mixed $errors = null,
    ): JsonResponse {
        $response = [
            'status' => ApiResponseStatus::Error->value,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    protected function validationResponse(
        array $errors,
        string $message = 'Validation failed',
    ): JsonResponse {
        return $this->errorResponse(
            message: $message,
            statusCode: HttpStatus::UNPROCESSABLE_ENTITY,
            errors: $errors,
        );
    }
}
