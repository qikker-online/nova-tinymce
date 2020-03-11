<?php

namespace QikkerOnline\NovaTinyMCE;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use  QikkerOnline\NovaTinyMCE\Console\SupportFileManagerCommand;
use QikkerOnline\NovaTinyMCE\Http\Middleware\Authorize;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            realpath(__DIR__ . '/../dist/tinymce') => public_path('vendor/tinymce'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../config/nova-tinymce.php' => config_path('nova-tinymce.php'),
        ], 'config');

        Nova::serving(function (ServingNova $event) {
            Nova::script('Nova-TinyMCE-tinymce', __DIR__.'/../dist/js/tinymce.js');
            Nova::script('Nova-TinyMCE', __DIR__.'/../dist/js/field.js');
            Nova::style('Nova-TinyMCE', __DIR__.'/../dist/css/field.css');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SupportFileManagerCommand::class
            ]);
        }

        $this->app->booted(function() {
            $this->routes();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nova-tinymce.php', 'nova-tinymce');
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/nova-tinymce')
            ->namespace('QikkerOnline\\NovaTinyMCE\\Http\\Controllers')
            ->group(__DIR__.'/../routes/api.php');
    }
}
