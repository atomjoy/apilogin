<?php

namespace Atomjoy\Apilogin\Providers;

use Atomjoy\Apilogin\Events\ActivateUser;
use Atomjoy\Apilogin\Events\EmailChange;
use Atomjoy\Apilogin\Events\PasswordReset;
use Atomjoy\Apilogin\Events\RegisterUser;
use Atomjoy\Apilogin\Listeners\ActivateUserNotification;
use Atomjoy\Apilogin\Listeners\PasswordResetNotification;
use Atomjoy\Apilogin\Listeners\RegisterUserNotification;
use Atomjoy\Apilogin\Listeners\EmailChangeNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	protected $listen = [
		ActivateUser::class => [
			ActivateUserNotification::class,
		],
		PasswordReset::class => [
			PasswordResetNotification::class,
		],
		RegisterUser::class => [
			RegisterUserNotification::class,
		],
		EmailChange::class => [
			EmailChangeNotification::class,
		],
	];

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();
	}
}
