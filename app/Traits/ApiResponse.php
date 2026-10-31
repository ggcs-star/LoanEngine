<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ApiResponse
{
    /**
     * Standard message for every API error.
     */
    protected function apiErrorMessage(): string
    {
        return 'Something went wrong';
    }

    /**
     * Standard success response.
     */
    public function successResponse(
        string $message = 'Success',
        mixed $data = [],
        int $statusCode = Response::HTTP_OK,
        ?array $pagination = null,
        ?array $meta = null
    ): JsonResponse {
        if ($data === null) {
            $data = [];
        }

        if ($data instanceof AbstractPaginator) {
            $pagination = [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];

            $data = $data->items();
        }

        $response = [
            'status' => true,
            'success' => true,
            'status_code' => $statusCode,
            'errors' => [],
            'message' => $message,
            'data' => $data,
        ];

        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Standard error response.
     *
     * Every API error uses the same public message.
     */
    public function errorResponse(
        string $message = 'Something went wrong',
        int $statusCode = Response::HTTP_BAD_REQUEST,
        array $errors = [],
        mixed $data = []
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'success' => false,
            'status_code' => $statusCode,
            'errors' => [],
            'message' => $this->apiErrorMessage(),
            'data' => [],
        ], $statusCode);
    }

    /**
     * Validation error.
     */
    public function validationResponse(array $errors = []): JsonResponse
    {
        return $this->errorResponse(
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Unauthorized response.
     */
    public function unauthorizedResponse(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return $this->errorResponse(
            statusCode: Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Forbidden response.
     */
    public function forbiddenResponse(
        string $message = 'Access Forbidden'
    ): JsonResponse {
        return $this->errorResponse(
            statusCode: Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Not found response.
     */
    public function notFoundResponse(
        string $message = 'Record Not Found'
    ): JsonResponse {
        return $this->errorResponse(
            statusCode: Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Created response.
     */
    public function createdResponse(
        string $message = 'Created Successfully',
        mixed $data = []
    ): JsonResponse {
        return $this->successResponse(
            message: $message,
            data: $data,
            statusCode: Response::HTTP_CREATED
        );
    }

    /**
     * Updated response.
     */
    public function updatedResponse(
        string $message = 'Updated Successfully',
        mixed $data = []
    ): JsonResponse {
        return $this->successResponse(
            message: $message,
            data: $data
        );
    }

    /**
     * Deleted response.
     */
    public function deletedResponse(
        string $message = 'Deleted Successfully'
    ): JsonResponse {
        return $this->successResponse(
            message: $message,
            data: []
        );
    }

    /**
     * No content response.
     */
    public function noContentResponse(): Response
    {
        return response()->noContent();
    }

    /**
     * Internal server error.
     */
    public function serverErrorResponse(
        string $message = 'Internal Server Error',
        array $errors = [],
        mixed $data = []
    ): JsonResponse {
        return $this->errorResponse(
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Rate-limit response.
     */
    public function tooManyRequestsResponse(
        string $message = 'Too many attempts. Please try again later.',
        int $retryAfter = 60,
        bool $requiresOtp = false,
        mixed $risk = null,
        array $errors = []
    ): JsonResponse {
        return response()
            ->json([
                'status' => false,
                'success' => false,
                'status_code' => Response::HTTP_TOO_MANY_REQUESTS,
                'errors' => [],
                'message' => $this->apiErrorMessage(),
                'data' => [],
            ], Response::HTTP_TOO_MANY_REQUESTS)
            ->header('Retry-After', (string) max(0, $retryAfter));
    }

    /**
     * Log an unexpected API exception without exposing
     * its internal details to the client.
     */
    protected function logApiException(Throwable $exception): void
    {
        Log::error('LoanEngine API exception', [
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => request()->path(),
            'method' => request()->method(),
            'user_id' => request()->user()?->getAuthIdentifier(),
        ]);
    }
}
