<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    protected function successResponse($data = null, $message = 'Operation completed successfully', $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        return response()->json($response, $statusCode);
    }

    protected function errorResponse($message = 'An error occurred', $errors = null, $statusCode = 400, $code = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($code) {
            $response['meta']['code'] = $code;
        }

        return response()->json($response, $statusCode);
    }

    protected function paginatedResponse($data, $meta, $links = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'meta' => $meta,
        ];

        if ($links) {
            $response['links'] = $links;
        }

        return response()->json($response);
    }
}

