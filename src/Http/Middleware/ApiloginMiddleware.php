<?php

namespace Atomjoy\Apilogin\Http\Middleware;

use Atomjoy\Apilogin\Exceptions\JsonException;
use Closure;

/**
 *  Only json response allowed
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Closure  $next
 * @return mixed
 *
 */
class ApiloginMiddleware
{
	public function handle($request, Closure $next)
	{
		$this->locales($request);

		$this->acceptJson($request);

		if ($request->is('web/api/*') && !$request->wantsJson()) {
			throw new JsonException(__('apilogin.middleware.not.acceptable'), 406);
		}

		return $next($request);
	}

	function acceptJson(&$request)
	{
		if (config('apilogin.enable_accept_json', false)) {
			$request->headers->set('Accept', 'application/json');
		}
	}

	public function locales(&$request)
	{
		if (config('apilogin.enable_locales', true)) {
			$lang =  session('locale', config('app.locale'));

			app()->setLocale($lang);

			if ($request->has('locale')) {
				app()->setLocale($request->query('locale'));
			}
		}
	}
}
