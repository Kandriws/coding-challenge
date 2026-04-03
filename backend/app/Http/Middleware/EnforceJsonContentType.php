<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceJsonContentType
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Content-Type must be application/json.',
            ], 415);
        }

        return $next($request);
    }
}
