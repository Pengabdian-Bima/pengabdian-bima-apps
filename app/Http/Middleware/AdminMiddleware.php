<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role === 'kasir') {
            if ($request->is('admin') || $request->is('admin/stock*') || $request->is('admin/cashier*') || $request->is('admin/profile*')) {
                return $next($request);
            }
            if ($request->is('admin/orders*') && $request->isMethod('get')) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized.');
    }
}
