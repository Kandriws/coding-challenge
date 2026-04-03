<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function ok(mixed $data = null, string $message = 'Success', int $status = Response::HTTP_OK): JsonResponse
    {
        return api_ok($data, $message, $status);
    }

    protected function fail(string $message = 'Error', int $status = Response::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        return api_fail($message, $status, $errors);
    }
}
