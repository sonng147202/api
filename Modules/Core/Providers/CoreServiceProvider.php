<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Core\Console\UpdateLocationData;
use Modules\Core\Events\BackEndInit;
use Nwidart\Modules\Facades\Module;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();

        // Register command
        $this->commands([
            UpdateLocationData::class
        ]);

        $this->getMenuConfig();

        // Get settings
        View::composer('*', 'Modules\Core\Http\ViewComposers\SettingComposer');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('core.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'core'
        );
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/notification.php', 'notification'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/core');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/core';
        }, \Config::get('view.paths')), [$sourcePath]), 'core');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/core');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'core');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'core');
        }
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/Database/factories');
        }
    }

    /**
     * Get menu config from all module
     */
    public function getMenuConfig()
    {
        $modules = Module::all();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                // Get menu config
                $menuConfig = $module->getPath() . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'menu.php';

                if (file_exists($menuConfig)) {
                    $this->mergeConfigFrom($menuConfig, 'backend_menu');
                }
            }
        }

        // Order menu by 'order'
        $menus = config('backend_menu');

        if ($menus) {
            usort($menus, function($a, $b) {
                return $a['order'] - $b['order'];
            });

            // Share menus
            View::share('backend_menus', $menus);
        }
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
