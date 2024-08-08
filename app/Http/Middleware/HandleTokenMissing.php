<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class HandleTokenMissing
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            return $next($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'Token not provided or invalid',
                'status' => 401
            ], 401);
        }
    }
}
