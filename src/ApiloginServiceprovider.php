<?php

namespace Atomjoy\Apilogin;

use Atomjoy\Apilogin\Http\Middleware\ApiloginMiddleware;
use Atomjoy\Apilogin\Providers\AuthServiceProvider;
use Atomjoy\Apilogin\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class ApiloginServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'apilogin');
		$this->app->register(EventServiceProvider::class);
		// $this->app->register(AuthServiceProvider::class);
	}

	public function boot(Kernel $kernel)
	{
		$this->app['router']->aliasMiddleware('apilogin', ApiloginMiddleware::class);

		$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'apilogin');
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

		if (config('apilogin.load_translations', true)) {
			$this->loadTranslationsFrom(__DIR__ . '/../lang', 'apilogin');
			$this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
		}

		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config/config.php' => config_path('apilogin.php'),
			], 'apilogin-config');

			$this->publishes([
				__DIR__ . '/../resources/views' => resource_path('views/vendor/apilogin')
			], 'apilogin-views');

			$this->publishes([
				__DIR__ . '/../lang' => base_path('lang/vendor/apilogin')
			], 'apilogin-lang');
		}
	}
}
