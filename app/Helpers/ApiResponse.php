<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error(string $message, $errors = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    public static function paginated($resource, string $message = null): JsonResponse
    {
        $paginated = $resource->toArray();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginated['data'],
            'meta'    => [
                'current_page' => $paginated['current_page'],
                'per_page'     => $paginated['per_page'],
                'total'        => $paginated['total'],
                'last_page'    => $paginated['last_page'],
                'from'         => $paginated['from'],
                'to'           => $paginated['to'],
            ]
        ], 200);
    }
}
