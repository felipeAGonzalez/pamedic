<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    public function handle($request, Closure $next, ...$positions)
    {
        if ($request->user() && ! in_array($request->user()->position, $positions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
            }

            return redirect('/welcome')->with('error', 'No tienes permiso para acceder a esta ruta.');
        }

        return $next($request);
    }
}
