<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('admin.login'));
        }

        if (! $request->user()->isAdmin() || $request->user()->status !== 'active') {
            abort(403, 'You are not authorized to access the admin panel.');
        }

        return $next($request);
    }
}
