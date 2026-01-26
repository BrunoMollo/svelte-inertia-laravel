<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Ocr\OcrService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OcrService::class, function ($app) {
            $config = $app['config']['ocr'];

            return new OcrService(
                pythonPath: $config['python_path'],
                scriptPath: $config['script_path'],
                defaultDpi: $config['default_dpi'],
                defaultLang: $config['default_lang'],
                timeout: $config['timeout'],
                supportedExtensions: $config['supported_extensions'],
                supportedLanguages: $config['supported_languages'],
                maxDpi: $config['max_dpi'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(User::class, UserPolicy::class);
    }
}
