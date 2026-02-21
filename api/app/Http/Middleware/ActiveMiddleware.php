<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->active) {
            return $next($request);
        }

        if($user) $user->tokens()->delete();

        return response()->json(['error' => 'Não autorizado'], 401);
    }
}
