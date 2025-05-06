<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LanguageSwitchService;
use Closure;
use Illuminate\Http\Request;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $languageSwitchService = app(LanguageSwitchService::class);

        app()->setLocale($languageSwitchService->getLocale());

        return $next($request);
    }
}
