<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActivationCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param null $area
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $area = null): mixed
    {
        return $next($request);
    }
}
