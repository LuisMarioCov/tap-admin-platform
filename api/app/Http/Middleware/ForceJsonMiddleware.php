<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonMiddleware
{
    /** API writes must be JSON or multipart (user photo). Blocks odd content-types. */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*') && ! $request->is('api/users/*/photo')) {
            $contentType = (string) $request->header('Content-Type', '');

            if (
                $request->isMethod('POST')
                || $request->isMethod('PUT')
                || $request->isMethod('PATCH')
            ) {
                $allowsMultipart = str_contains($contentType, 'multipart/form-data');
                $allowsJson = $contentType === '' || str_contains($contentType, 'application/json');

                if (! $allowsMultipart && ! $allowsJson) {
                    return response()->json([
                        'message' => 'Content-Type application/json requerido.',
                    ], 406);
                }
            }
        }

        return $next($request);
    }
}
