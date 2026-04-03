<?php

use App\Http\Middleware\EnforceJsonContentType;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->api(prepend: [
            EnforceJsonContentType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return api_fail('Unauthenticated.', 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return api_fail('Validation failed.', 422, $e->errors());
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return api_fail('Resource not found.', 404);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            return api_fail($e->getMessage() ?: 'HTTP error.', $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            return api_fail('An unexpected error occurred.', 500);
        });
    })->create();
