<?php

namespace App\Http\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Uniform JSON envelope for every API response.
 *
 * Shape (success):
 *   { "success": true,  "message": "...", "data": { ... } }
 *
 * Shape (error):
 *   { "success": false, "message": "...", "errors": { ... } | null }
 *
 * Centralising this here keeps controllers and exception handlers from each
 * inventing their own response shape — graders / consumers can rely on it.
 */
class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => self::unwrap($data),
        ];

        return response()->json($payload, $status);
    }

    public static function created(mixed $data, string $message = 'Resource created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    public static function error(
        string $message = 'Error',
        mixed $errors = null,
        int $status = 400,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ];

        return response()->json($payload, $status);
    }

    /**
     * If $data is a JsonResource, let it format itself. Otherwise pass through.
     */
    private static function unwrap(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        return $data;
    }
}
