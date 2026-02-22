<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicensingService;

class LicenseCheckMiddleware
{
    protected LicensingService $licensing;

    public function __construct(LicensingService $licensing)
    {
        $this->licensing = $licensing;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check for activation routes and local/debug environments (optional)
        if ($request->is('activate*') || $request->is('_debugbar*')) {
            return $next($request);
        }

        if (!$this->licensing->isActivated()) {
            return redirect()->route('system.activate');
        }

        return $next($request);
    }
}
