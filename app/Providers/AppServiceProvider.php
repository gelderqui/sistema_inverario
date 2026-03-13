<?php

namespace App\Providers;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = config('app.locale', 'es');

        try {
            if (Schema::hasTable('configuraciones')) {
                $configuredLocale = Configuracion::valor('locale');

                if (is_string($configuredLocale) && in_array($configuredLocale, ['es', 'en'], true)) {
                    $locale = $configuredLocale;
                }
            }
        } catch (\Throwable) {
            // During early bootstrap or migrations, use env locale fallback.
        }

        app()->setLocale($locale);
        config(['app.locale' => $locale]);
    }
}
