<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\Core\Models\Setting;

class SetLocale
{
    /**
     * Read the app_language setting and apply it.
     * Default: 'ar' (Arabic).
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = 'ar'; // safe default

        try {
            $saved = Setting::getValue('app_language', 'general');
            if ($saved && in_array($saved, ['ar', 'en'])) {
                $locale = $saved;
            }
        } catch (\Throwable $e) {
            // Setting table might not exist yet (fresh install)
        }

        App::setLocale($locale);

        return $next($request);
    }
}
