<?php

namespace Atomjoy\Apilogin\Http\Middleware;

use Atomjoy\Apilogin\Exceptions\JsonException;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 *  Only json response allowed
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Closure  $next
 * @return mixed
 */
class ApiloginAuthMiddleware
{
	public function handle($request, Closure $next)
	{
		if (!Auth::check() || Auth::user()->is_admin != 1) {
			throw new JsonException(__('apilogin.middleware.invalid.is_admin'), 403);
		}

		return $next($request);
	}
}
