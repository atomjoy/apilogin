<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Events\LoggedUser;
use Atomjoy\Apilogin\Events\LoggedUserError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoggedController extends Controller
{
	function index(Request $request)
	{
		$this->setSampleCookie($request);

		if (Auth::check()) {
			LoggedUser::dispatch(Auth::user());

			return response()->json([
				'message' => __('apilogin.logged.authenticated'),
				'user' => Auth::user()
			], 200);
		} else {
			LoggedUserError::dispatch();

			return response()->json([
				'message' => __('apilogin.logged.unauthenticated'),
				'user' => null
			], 422);
		}
	}

	function setSampleCookie($request)
	{
		if (cookie('dummy_token') != null) {
			$token = 'token5678';
			// Set cookie: $name, $val, $minutes, $path, $domain, $secure, $httpOnly = true, $raw = false, $sameSite = 'strict'
			Cookie::queue(
				'dummy_token',
				$token,
				env('APP_REMEBER_ME_MINUTES', 3456789),
				'/',
				'.' . request()->getHost(),
				request()->secure(), // or true for https only
				true,
				false,
				'lax' // Or 'strict' for max security
			);
		}
	}
}
