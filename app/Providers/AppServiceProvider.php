<?php

namespace App\Providers;

use App\Models\Usaha;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject $appTheme ke semua view berdasarkan kolom usaha.theme.
        // Tema merah-putih adalah fitur opsional yang diset manual di DB
        // (bukan UI switcher). Cache 5 menit; flush manual setelah update.
        View::composer('*', function ($view) {
            if ($view->offsetExists('appTheme')) {
                return;
            }

            $theme = 'default';

            if (Session::get('lokasi')) {
                $cacheKey = 'app_theme_' . Session::get('lokasi');
                $theme = Cache::remember($cacheKey, 300, function () {
                    $usaha = Usaha::find(Session::get('lokasi'));
                    return ($usaha && in_array($usaha->theme, ['default', 'merah-putih'], true))
                        ? $usaha->theme
                        : 'default';
                });
            }

            $view->with('appTheme', $theme);
        });
    }
}
