<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromHeader {
    private const SUPPORTED = ['en', 'my'];

    public function handle(Request $request, Closure $next) {
        $locale = $request->header('X-App-Locale', 'en');
        App::setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : 'en');
        return $next($request);
    }
}
