<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Webkul\B2BSuite\Console\Commands\InstallB2BSuite;
use Webkul\B2BSuite\Console\Commands\SeedDemoData;
use Webkul\B2BSuite\Contracts\CompanyFlat;
use Webkul\B2BSuite\Http\Middleware\CustomerBouncerMiddleware;
use Webkul\B2BSuite\Http\Middleware\EnsurePayByCreditWithinLimit;
use Webkul\B2BSuite\Menu as B2BMenu;
use Webkul\B2BSuite\Providers\B2BSuiteManager;
use Webkul\B2BSuite\Providers\B2BSuiteServiceProvider as BaseB2BSuiteServiceProvider;
use Webkul\B2BSuite\Providers\EventServiceProvider;
use Webkul\B2BSuite\Providers\ModuleServiceProvider;
use Webkul\B2BSuite\Repositories\CompanyFlatRepository;
use Webkul\Core\Menu as CoreMenu;

class SafeB2BSuiteServiceProvider extends BaseB2BSuiteServiceProvider
{
    /**
     * Bootstrap services safely guarding against pre-migration channel queries.
     */
    public function boot(): void
    {
        $baseDir = dirname((new ReflectionClass(BaseB2BSuiteServiceProvider::class))->getFileName());

        include_once $baseDir . '/../Http/helpers.php';

        try {
            $isDatabaseReady = Schema::hasTable('channels') && Schema::hasTable('core_config');
        } catch (\Throwable $e) {
            $isDatabaseReady = false;
        }

        if ($isDatabaseReady && core()->getConfigData('b2b.general.settings.active')) {
            Route::middleware('web')->group($baseDir . '/../Routes/web.php');

            require_once $baseDir . '/../Routes/breadcrumbs.php';

            config([
                'bagisto.tracked_modules' => array_values(array_unique(array_merge(
                    config('bagisto.tracked_modules', []),
                    ['bagisto/b2b-suite']
                ))),
            ]);
        }

        Route::aliasMiddleware('customer_bouncer', CustomerBouncerMiddleware::class);

        Route::pushMiddlewareToGroup('shop', EnsurePayByCreditWithinLimit::class);

        $this->loadMigrationsFrom($baseDir . '/../Database/Migrations');

        $this->loadTranslationsFrom($baseDir . '/../Resources/lang', 'b2b');

        $this->loadViewsFrom($baseDir . '/../Resources/views', 'b2b');

        Blade::anonymousComponentPath($baseDir . '/../Resources/views/components', 'b2b');

        $this->registerCommands();

        $this->registerProviders();

        $this->publishAssets();

        if (
            $isDatabaseReady
            && (bool) core()->getConfigData('b2b.general.settings.active')
        ) {
            $this->mergeConfigFrom(
                dirname($baseDir) . '/Config/admin/menu.php',
                'menu.admin'
            );

            $this->mergeConfigFrom(
                dirname($baseDir) . '/Config/shop/menu.php',
                'menu.customer'
            );
        }

        new B2BSuiteManager($this->app);
    }
}
