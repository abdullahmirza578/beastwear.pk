<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySiteSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = SiteSetting::current();
        config(['app.timezone' => $settings->timezone]);
        date_default_timezone_set($settings->timezone);

        if ($settings->maintenance && ! $request->is('admin', 'admin/*')) {
            return response()->view('maintenance', ['siteSettings' => $settings], 503);
        }

        return $next($request);
    }
}
