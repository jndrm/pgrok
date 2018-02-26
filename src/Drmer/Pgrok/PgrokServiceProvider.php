<?php

namespace Drmer\Pgrok;

use Illuminate\Support\ServiceProvider;

class PgrokServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->configPath() => config_path('pgrok.php'),
        ]);

        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            Commands\PgrokCommand::class,
        ]);

        $this->mergeConfigFrom($this->configPath(), 'pgrok');
    }

    protected function configPath()
    {
        return __DIR__ . '/../../../config/pgrok.php';
    }
}
