<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfTokenExpired
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->tokenExpired()) {
            Auth::logout();
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado por favor inicia sesión nuevamente.');
        }

        return $next($request);
    }
}
