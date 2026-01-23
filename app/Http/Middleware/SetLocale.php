<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Resolve the appropriate locale for this request.
     */
    private function resolveLocale(Request $request): string
    {
        $supportedLocales = ['es', 'en'];

        // If authenticated, use user's preference
        if ($request->user()) {
            $userLocale = $request->user()->locale;
            if ($userLocale && in_array($userLocale, $supportedLocales)) {
                return $userLocale;
            }
        }

        // Try to detect from Accept-Language header
        if ($acceptLanguage = $request->header('Accept-Language')) {
            $preferredLocale = $this->parseAcceptLanguage($acceptLanguage);
            if ($preferredLocale && in_array($preferredLocale, $supportedLocales)) {
                return $preferredLocale;
            }
        }

        // Fallback to configured locale
        return config('app.locale') ?? app()->getLocale();
    }

    /**
     * Parse Accept-Language header and return preferred locale.
     */
    private function parseAcceptLanguage(string $acceptLanguage): ?string
    {
        // Parse Accept-Language like "en-US,en;q=0.9,es;q=0.8"
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            // Extract just the language code (before semicolon and dash)
            $lang = explode(';', trim($language))[0];
            $lang = explode('-', $lang)[0];
            $lang = strtolower(trim($lang));

            if (! empty($lang)) {
                return $lang;
            }
        }

        return null;
    }
}
