<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class BaseController extends Controller
{
    /**
     * Success response format
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Error response format
     */
    protected function errorResponse(string $message = 'Something went wrong', int $code = 500, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Wrapper for try-catch in controllers
     */
    protected function safeCall(callable $callback, string $errorMessage = 'An error occurred', int $code = 500): JsonResponse
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            // optionally log error
            Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse($errorMessage, $code, $e->getMessage());
        }
    }
}
