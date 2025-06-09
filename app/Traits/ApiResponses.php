<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses 
{
    // General function to return customized success responses
    protected function success($message, $data = [], $statusCode = 200): JsonResponse 
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => $statusCode
        ], $statusCode);
    }

    // General function to return customized business errors
    protected function error($errors = [], $statusCode = 400): JsonResponse 
    {
        if (is_string($errors)) {
            return response()->json([
                'message' => $errors,
                'status' => $statusCode
            ], $statusCode);
        }

        return response()->json([
            'errors' => $errors,
            'status' => $statusCode
        ], $statusCode);
    }
} 