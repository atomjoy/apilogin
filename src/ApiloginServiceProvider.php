<?php

namespace Atomjoy\Apilogin;

use Atomjoy\Apilogin\Http\Middleware\ApiloginMiddleware;
use Atomjoy\Apilogin\Http\Middleware\ApiloginAuthMiddleware;
use Atomjoy\Apilogin\Providers\AuthServiceProvider;
use Atomjoy\Apilogin\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

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
		$this->app['router']->aliasMiddleware('apilogin_is_admin', ApiloginAuthMiddleware::class);

		// Spatie permissions
		if (config('apilogin.load_permissions', true)) {
			$this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
			$this->app['router']->aliasMiddleware('permission', PermissionMiddleware::class);
			$this->app['router']->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
		}

		$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'apilogin');

		if (config('apilogin.load_migrations', true)) {
			$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		}

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

			$this->publishes([
				// __DIR__.'/../database/migrations/create_table.php.stub' => $this->getMigrationFileName('create_table.php'),
			], 'apilogin-migrations');
		}
	}
}
