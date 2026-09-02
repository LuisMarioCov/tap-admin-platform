<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasSection
{
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (! $user->hasSection($section)) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        return $next($request);
    }
}
