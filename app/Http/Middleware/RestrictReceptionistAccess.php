<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictReceptionistAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->position !== 'RECEPCIONIST') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $allowedRoutes = [
            'welcome',
            'attendance.index',
            'attendance.search',
            'attendance.register',
            'attendance.attendanceSchedule',
            'attendance.searchSchedule',
            'password.view',
            'password.update',
        ];

        if (str_starts_with((string) $routeName, 'schedule.') || in_array($routeName, $allowedRoutes, true)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a este módulo.');
    }
}
