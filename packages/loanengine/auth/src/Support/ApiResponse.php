<?php

namespace LoanEngine\Auth\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Request completed successfully.', int $status = 200, array $meta = []): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve();
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => (object) ($meta ?: []),
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Created successfully.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function message(string $message, int $status = 200, mixed $data = null): JsonResponse
    {
        return self::success($data, $message, $status);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => (object) [],
        ], $status);
    }

    public static function validationError(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'This action is not allowed.'): JsonResponse
    {
        return self::error($message, 403);
    }

    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function conflict(string $message = 'The request conflicts with the current state.'): JsonResponse
    {
        return self::error($message, 409);
    }

    public static function tooManyRequests(string $message = 'Too many requests. Please try again later.'): JsonResponse
    {
        return self::error($message, 429);
    }

    public static function serverError(string $message = 'An unexpected error occurred.', ?string $requestId = null): JsonResponse
    {
        return self::error($message, 500)->header('X-Request-ID', $requestId ?: (string) str()->uuid());
    }

    public static function paginated(LengthAwarePaginator|AbstractPaginator $paginator, string $message = 'Records retrieved successfully.'): JsonResponse
    {
        return self::success($paginator->items(), $message, 200, [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator instanceof LengthAwarePaginator ? $paginator->lastPage() : null,
            'per_page' => $paginator->perPage(),
            'total' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : null,
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ]);
    }
}
