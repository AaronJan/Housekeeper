<?php

namespace Housekeeper\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class HousekeeperServiceProvider
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Providers
 */
class HousekeeperServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/housekeeper.php' => config_path('housekeeper.php')
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/housekeeper.php', 'housekeeper'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands('Housekeeper\Console\Generators\MakeRepositoryCommand');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}