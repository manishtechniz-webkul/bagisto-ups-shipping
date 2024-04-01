<?php

namespace Webkul\UpsShipping\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class UpsShippingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'ups');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'ups');

        $this->loadPublishableAssets();

        $this->app->register(EventServiceProvider::class);

        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'ups');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/carriers.php', 'carriers'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php', 'core'
        );
    }

    /**
     * This method will load all publishables.
     */
    public function loadPublishableAssets(): void
    {
        $this->publishes([
            __DIR__.'/../Resources/views/shop/checkout/onepage/shipping.blade.php' => resource_path('themes/default/views/checkout/onepage/shipping.blade.php'),
        ]);

        $this->publishes([
            __DIR__.'/../Resources/views/admin/sales/shipment/view.blade.php' => resource_path('admin-themes/default/views/sales/shipments/view.blade.php'),
        ]);
    }
}